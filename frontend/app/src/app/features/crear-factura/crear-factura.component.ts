import { CommonModule } from '@angular/common';
import { Component, EventEmitter, OnInit, Output } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { LucideAngularModule, LucideIconProvider, LUCIDE_ICONS, Plus, Trash2, ArrowLeft } from 'lucide-angular';
import { ActivatedRoute, Router } from '@angular/router';

import { SearchOwnerComponent, Owner } from '../search-owner/search-owner.component';
import { ClientService } from '../../core/services/client.service';
import { InvoiceApi, InvoiceCreatePayload, InvoiceService, InvoiceUpdatePayload } from '../../core/services/invoice.service';
import { MedicalService } from '../../core/services/medical-service.service';
import { Servicio } from '../../core/models/service.model';
import { AlertService } from '../../core/services/alert.service';

interface ServicioFactura {
  id: number;
  serviceId: number;
  nombre: string;
  precio: number;
  cantidad: number;
}

@Component({
  selector: 'app-crear-factura',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    SearchOwnerComponent,
    LucideAngularModule
  ],
  providers: [
    {
      provide: LUCIDE_ICONS,
      multi: true,
      useValue: new LucideIconProvider({ Plus, Trash2, ArrowLeft })
    }
  ],
  templateUrl: './crear-factura.component.html'
})
export class CrearFacturaComponent implements OnInit {
  @Output() navigate = new EventEmitter<string>();

  // icons
  readonly Plus = Plus;
  readonly Trash2 = Trash2;
  readonly ArrowLeft = ArrowLeft;

  propietarioSeleccionado: Owner | null = null;
  mascota: number | null = null;
  servicios: ServicioFactura[] = [];
  servicioSeleccionado: number | null = null;
  isEditMode = false;
  private invoiceId: number | null = null;
  private invoiceData: InvoiceApi | null = null;
  private ownersLoaded = false;
  private servicesLoaded = false;
  private initialServiciosSnapshot: Array<{ serviceId: number; cantidad: number }> = [];
  private initialNotas: string | null = null;

  // Numero factura + fecha
  numeroFactura = this.generateInvoiceNumberPreview();
  fechaEmision = this.todayISO();
  notas = '';

  // propietarios (backend)
  propietarios: Owner[] = [];

  // servicios disponibles (backend)
  serviciosDisponibles: Array<{ id: number; nombre: string; precio: number }> = [];

  loadingOwners = false;
  loadingServicios = false;
  errorMessage = '';
  private dniStorageKey = 'clientes_dni_map';

  constructor(
    private clientService: ClientService,
    private medicalService: MedicalService,
    private invoiceService: InvoiceService,
    private route: ActivatedRoute,
    private alertService: AlertService,
    private router: Router
  ) {}

  ngOnInit(): void {
    const invoiceId = this.route.snapshot.paramMap.get('invoiceId');
    if (invoiceId) {
      const parsed = Number(invoiceId);
      if (Number.isFinite(parsed) && parsed > 0) {
        this.isEditMode = true;
        this.invoiceId = parsed;
        this.loadInvoice(parsed);
      }
    }
    this.loadOwners();
    this.loadServicios();
  }

  // --- computed ---
  get subtotal(): number {
    return this.servicios.reduce((sum, s) => sum + s.precio * s.cantidad, 0);
  }

  get iva(): number {
    return this.subtotal * 0.21;
  }

  get total(): number {
    return this.subtotal + this.iva;
  }

  // --- actions ---
  onNavigate(page: string) {
    if (this.navigate.observers.length > 0) {
      this.navigate.emit(page);
      return;
    }
    this.router.navigate([`/${page}`]);
  }

  onOwnerSelected(owner: Owner | null) {
    this.propietarioSeleccionado = owner;
    this.mascota = null; // reset mascota al cambiar propietario
    if (owner) {
      this.loadOwnerPets(owner);
    }
  }

  private loadOwners(): void {
    this.loadingOwners = true;
    this.clientService.getClients().subscribe({
      next: (clientes) => {
        this.propietarios = clientes.map((cliente) => ({
          id: cliente.id,
          nombre: `${cliente.firstName || ''} ${cliente.lastName || ''}`.trim(),
          dni: cliente.dni || '',
          mascotas: []
        }));
        this.ownersLoaded = true;
        if (this.invoiceData) {
          this.applyOwnerSelection(this.invoiceData);
        }
        this.loadingOwners = false;
      },
      error: (err) => {
        console.error('Error al cargar propietarios', err);
        this.loadingOwners = false;
      }
    });
  }

  private loadOwnerPets(owner: Owner, selectedPetId?: number | null, selectedPetName?: string | null): void {
    this.clientService.getClientById(owner.id).subscribe({
      next: (client) => {
        const pets = (client.pets || []).map((pet: any) => ({
          id: pet.id,
          nombre: pet.name || pet.petName || '',
          especie: this.getPetTypeName(pet)
        }));
        const updatedOwner = { ...owner, mascotas: pets };
        this.propietarioSeleccionado = updatedOwner;
        this.propietarios = this.propietarios.map((o) => (o.id === owner.id ? updatedOwner : o));
        this.applyPetSelection(updatedOwner, selectedPetId, selectedPetName);
      },
      error: (err) => console.error('Error al cargar mascotas del propietario', err)
    });
  }

  private loadServicios(): void {
    this.loadingServicios = true;
    this.medicalService.getServices().subscribe({
      next: (services: Servicio[]) => {
        this.serviciosDisponibles = services
          .filter((service) => service.isActive !== false)
          .map((service) => ({
          id: service.id,
          nombre: service.name,
          precio: service.unitPrice ?? (service as unknown as { price?: number }).price ?? 0
        }));
        this.servicesLoaded = true;
        if (this.invoiceData) {
          this.servicios = this.mapInvoiceItems(this.invoiceData);
        }
        this.loadingServicios = false;
      },
      error: (err) => {
        console.error('Error al cargar servicios', err);
        this.loadingServicios = false;
      }
    });
  }

  private getPetTypeName(pet: any): string {
    if (!pet) return 'Otros';
    if (typeof pet.petType === 'string') return pet.petType;
    return pet.petType?.name || pet.type || 'Otros';
  }

  agregarServicio() {
    if (this.servicioSeleccionado === null) return;

    const servicio = this.serviciosDisponibles.find(s => s.id === Number(this.servicioSeleccionado));
    if (!servicio) return;

    const nuevoServicio: ServicioFactura = {
      id: Date.now(),
      serviceId: servicio.id,
      nombre: servicio.nombre,
      precio: servicio.precio,
      cantidad: 1
    };

    this.servicios = [...this.servicios, nuevoServicio];
    this.servicioSeleccionado = null;
  }

  eliminarServicio(id: number) {
    this.servicios = this.servicios.filter(s => s.id !== id);
  }

  actualizarCantidad(id: number, cantidad: number) {
    const parsed = Number(cantidad);
    const safe = Number.isFinite(parsed) ? Math.max(1, parsed) : 1;
    this.servicios = this.servicios.map(s => (s.id === id ? { ...s, cantidad: safe } : s));
  }

  guardarFactura() {
    if (this.hasInactiveSelectedService()) {
      this.alertService.error(
        'Servicios inactivos',
        'Hay servicios inactivos en la factura. Sustitúyelos por servicios activos.'
      );
      return;
    }

    if (this.servicios.length === 0) {
      alert('Por favor, agrega al menos un servicio.');
      return;
    }

    if (this.isEditMode && this.invoiceId) {
      const payload: InvoiceUpdatePayload = {
        notes: this.notas || null
      };
      if (this.hasServiciosChanged()) {
        payload.invoiceItems = this.servicios.map((s) => ({
          serviceId: s.serviceId,
          quantity: s.cantidad
        }));
      }

      this.invoiceService.updateInvoice(this.invoiceId, payload).subscribe({
        next: () => {
          this.alertService.success(
            'Factura modificada',
            'La factura se ha modificado exitosamente.'
          );
          this.onNavigate('facturas-vet');
        },
        error: (err) => {
          console.error('Error al actualizar factura', err);
          this.alertService.error('Error', 'No se pudo guardar la factura.');
        }
      });
      return;
    }

    if (!this.propietarioSeleccionado || !this.mascota) {
      alert('Por favor, completa todos los campos obligatorios (propietario, mascota y servicios)');
      return;
    }

    const payload: InvoiceCreatePayload = {
      userId: this.propietarioSeleccionado.id,
      petId: this.mascota,
      invoiceDate: this.fechaEmision,
      notes: this.notas || null,
      invoiceItems: this.servicios.map((s) => ({
        serviceId: s.serviceId,
        quantity: s.cantidad
      }))
    };

    this.invoiceService.createInvoice(payload).subscribe({
      next: () => {
        this.alertService.success(
          'Factura creada',
          'La factura se ha creado exitosamente.'
        );
        this.onNavigate('facturas-vet');
      },
      error: (err) => {
        console.error('Error al crear factura', err);
        this.alertService.error('Error', 'No se pudo guardar la factura.');
      }
    });
  }

  // --- utils ---
  private generateInvoiceNumberPreview(): string {
    const year = new Date().getFullYear();
    return `${year}/____`;
  }

  private todayISO(): string {
    return new Date().toISOString().split('T')[0];
  }

  private loadInvoice(id: number): void {
    this.invoiceService.getInvoiceById(id).subscribe({
      next: (invoice) => {
        this.invoiceData = invoice;
        this.applyInvoiceCommon(invoice);
        this.servicios = this.mapInvoiceItems(invoice);
        if (this.ownersLoaded) {
          this.applyOwnerSelection(invoice);
        }
      },
      error: (err) => {
        console.error('Error al cargar la factura', err);
      }
    });
  }

  private applyInvoiceCommon(invoice: InvoiceApi): void {
    this.numeroFactura = invoice.invoiceNumber || String(invoice.id);
    this.fechaEmision = this.normalizeInvoiceDate(invoice.invoiceDate);
    this.notas = invoice.notes || '';
    this.initialNotas = this.notas;
  }

  private applyOwnerSelection(invoice: InvoiceApi): void {
    const userId = this.toNumber(invoice.userId);
    const petId = this.toNumber(invoice.petId);
    const owner =
      (userId !== null ? this.propietarios.find((o) => o.id === userId) : undefined) ??
      this.findOwnerByName(invoice.clientName);

    if (owner) {
      this.propietarioSeleccionado = owner;
      this.loadOwnerPets(owner, petId, invoice.petName || null);
      return;
    }

    this.propietarioSeleccionado = {
      id: userId ?? 0,
      nombre: invoice.clientName || 'Cliente',
      dni: '',
      mascotas: invoice.petName
        ? [{ id: petId ?? 0, nombre: invoice.petName, especie: '' }]
        : []
    };
    this.mascota = petId ?? (invoice.petName ? 0 : null);
  }

  private applyPetSelection(owner: Owner, petId?: number | null, petName?: string | null): void {
    if (!owner.mascotas || owner.mascotas.length === 0) {
      this.mascota = petId ?? null;
      return;
    }
    if (petId) {
      const foundById = owner.mascotas.find((p) => p.id === petId);
      if (foundById) {
        this.mascota = foundById.id;
        return;
      }
    }
    if (petName) {
      const normalized = petName.trim().toLowerCase();
      const foundByName = owner.mascotas.find((p) => p.nombre.trim().toLowerCase() === normalized);
      if (foundByName) {
        this.mascota = foundByName.id;
        return;
      }
    }
    this.mascota = null;
  }

  private findOwnerByName(name?: string | null): Owner | null {
    if (!name) return null;
    const normalized = name.trim().toLowerCase();
    return this.propietarios.find((o) => o.nombre.trim().toLowerCase() === normalized) ?? null;
  }

  private mapInvoiceItems(invoice: InvoiceApi): ServicioFactura[] {
    const items = this.getInvoiceItems(invoice);
    const mapped = items.map((item, index) => {
      const serviceId = this.toNumber((item as any).serviceId ?? (item as any).service_id) ?? 0;
      const serviceName =
        (item as any).serviceName ??
        (item as any).service_name ??
        (item as any).name ??
        'Servicio';
      const unitPrice = this.toNumber((item as any).unitPrice ?? (item as any).unit_price) ?? 0;
      const quantity = this.toNumber((item as any).quantity) ?? 1;
      const safeQuantity = Math.max(1, quantity);
      const service = this.serviciosDisponibles.find((s) => s.id === serviceId);

      return {
        id: Date.now() + index,
        serviceId,
        nombre: serviceName || service?.nombre || 'Servicio',
        precio: service?.precio ?? unitPrice,
        cantidad: safeQuantity
      };
    });
    if (mapped.length > 0) {
      this.initialServiciosSnapshot = mapped.map((s) => ({
        serviceId: s.serviceId,
        cantidad: s.cantidad
      }));
    }
    return mapped;
  }

  private getInvoiceItems(invoice: InvoiceApi): Array<Record<string, unknown>> {
    const raw =
      (invoice as any).invoiceItems ??
      (invoice as any).invoice_items ??
      (invoice as any).items ??
      [];
    return Array.isArray(raw) ? raw : [];
  }

  private normalizeInvoiceDate(value?: string | null): string {
    if (!value) return this.todayISO();
    if (/^\d{4}-\d{2}-\d{2}$/.test(value)) return value;
    const parts = value.split(/[/-]/);
    if (parts.length === 3 && parts[2].length === 4) {
      const day = parts[0].padStart(2, '0');
      const month = parts[1].padStart(2, '0');
      return `${parts[2]}-${month}-${day}`;
    }
    return this.todayISO();
  }


  private toNumber(value: unknown): number | null {
    if (value === null || value === undefined) return null;
    const parsed = typeof value === 'number' ? value : Number(value);
    return Number.isFinite(parsed) ? parsed : null;
  }

  private hasServiciosChanged(): boolean {
    const current = this.servicios.map((s) => ({
      serviceId: s.serviceId,
      cantidad: s.cantidad
    }));
    if (current.length !== this.initialServiciosSnapshot.length) return true;
    const sortFn = (a: { serviceId: number; cantidad: number }, b: { serviceId: number; cantidad: number }) =>
      a.serviceId - b.serviceId || a.cantidad - b.cantidad;
    const sortedCurrent = [...current].sort(sortFn);
    const sortedInitial = [...this.initialServiciosSnapshot].sort(sortFn);
    return sortedCurrent.some(
      (item, idx) =>
        item.serviceId !== sortedInitial[idx].serviceId ||
        item.cantidad !== sortedInitial[idx].cantidad
    );
  }

  private hasInactiveSelectedService(): boolean {
    const activeMap = new Map<number, boolean>();
    for (const service of this.serviciosDisponibles) {
      activeMap.set(service.id, true);
    }
    return this.servicios.some((item) => !activeMap.has(item.serviceId));
  }
}








