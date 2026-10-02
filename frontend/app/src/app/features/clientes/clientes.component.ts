import { Component, OnInit, ChangeDetectionStrategy } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { RouterModule } from '@angular/router';

import { Router, ActivatedRoute } from '@angular/router';
import { ClientService } from '../../core/services/client.service';
import { LucideDynamicIcon } from '@lucide/angular';
import { AlertService } from '../../core/services/alert.service';

export interface Cliente {
  id: number;
  nombre: string;
  apellido: string;
  dni: string;
  telefono: string;
  email: string;
  direccion: string;
  ciudad: string;
  codigoPostal: string;
  mascotas: number;
  notas?: string;
}

@Component({
  selector: 'app-clientes',
  standalone: true,
  imports: [RouterModule, FormsModule, LucideDynamicIcon],
  changeDetection: ChangeDetectionStrategy.Eager,
  templateUrl: './clientes.component.html'
})
export class ClientesComponent implements OnInit {

  searchTerm: string = '';

  clientes: Cliente[] = [];

  isDialogOpen = false;
  editingCliente: Cliente | null = null;

  clienteForm: {
    nombre: string;
    apellido: string;
    dni: string;
    telefono: string;
    email: string;
    direccion: string;
    ciudad: string;
    codigoPostal: string;
    notas: string;
  } = {
    nombre: '',
    apellido: '',
    dni: '',
    telefono: '',
    email: '',
    direccion: '',
    ciudad: '',
    codigoPostal: '',
    notas: ''
  };

  constructor(
    private router: Router,
    private route: ActivatedRoute,
    private clientService: ClientService,
    private alertService: AlertService
  ) {}

  ngOnInit(): void {
    this.loadClientes();
  }

  get filteredClientes(): Cliente[] {
    const term = this.searchTerm.toLowerCase().trim();
    if (!term) return this.clientes;

    return this.clientes.filter((c) =>
      c.nombre.toLowerCase().includes(term) ||
      c.dni.toLowerCase().includes(term) ||
      c.telefono.includes(term)
    );
  }

  loadClientes(): void {
    this.clientService.getClients().subscribe({
      next: (clientes) => {
        this.clientes = clientes.map((cliente) => ({
          id: cliente.id,
          nombre: cliente.firstName,
          apellido: cliente.lastName,
          dni: cliente.dni,
          telefono: cliente.phone || '',
          email: cliente.email,
          direccion: cliente.address || '',
          ciudad: cliente.city || '',
          codigoPostal: cliente.zipCode || '',
          mascotas: cliente.petCount || 0,
          notas: cliente.additionalNotes || ''
        }));
      },
      error: (err) => console.error('Error al cargar clientes', err)
    });
  }

  openDialog(cliente?: Cliente): void {
    if (cliente) {
      this.editingCliente = cliente;
      this.clienteForm = {
        nombre: cliente.nombre,
        apellido: cliente.apellido,
        dni: cliente.dni,
        telefono: cliente.telefono,
        email: cliente.email,
        direccion: cliente.direccion,
        ciudad: cliente.ciudad,
        codigoPostal: cliente.codigoPostal,
        notas: cliente.notas || ''
      };
    } else {
      this.editingCliente = null;
      this.clienteForm = {
        nombre: '',
        apellido: '',
        dni: '',
        telefono: '',
        email: '',
        direccion: '',
        ciudad: '',
        codigoPostal: '',
        notas: ''
      };
    }
    this.isDialogOpen = true;
  }

  closeDialog(): void {
    this.isDialogOpen = false;
    this.editingCliente = null;
  }

  saveCliente(): void {
    if (this.isSaveDisabled) {
      return;
    }

    const payload = {
      email: this.clienteForm.email.trim(),
      firstName: this.clienteForm.nombre.trim(),
      lastName: this.clienteForm.apellido.trim(),
      dni: this.clienteForm.dni.toUpperCase().trim(),
      phone: this.normalizePhone(this.clienteForm.telefono),
      address: this.clienteForm.direccion.trim(),
      city: this.clienteForm.ciudad.trim(),
      zipCode: this.clienteForm.codigoPostal.trim(),
      additionalNotes: this.clienteForm.notas?.trim()
    };

    const request$ = this.editingCliente
      ? this.clientService.updateClient(this.editingCliente.id, payload)
      : this.clientService.createClient({ ...payload, roles: ['ROLE_CLIENT'] });

    request$.subscribe({
      next: () => {
        this.loadClientes();
        this.closeDialog();
      },
      error: (err) => this.handleValidationError(err)
    });
  }

  viewCliente(cliente: Cliente): void {
    void this.router.navigate(['ficha', cliente.id], { relativeTo: this.route });
  }

  get isSaveDisabled(): boolean {
    return (
      !this.clienteForm.nombre.trim() ||
      !this.clienteForm.apellido.trim() ||
      !this.clienteForm.dni.trim() ||
      !this.clienteForm.telefono.trim() ||
      !this.clienteForm.email.trim() ||
      !this.clienteForm.direccion.trim() ||
      !this.clienteForm.ciudad.trim() ||
      !this.clienteForm.codigoPostal.trim()
    );
  }

  private normalizePhone(value: string): string {
    return (value || '').replace(/\s+/g, '');
  }

  private handleValidationError(err: any): void {

    // =========================
    // 409 → CONFLICTO (UNICIDAD)
    // =========================
    if (err.status === 409) {
      const details = String(err?.error?.error?.details ?? '').toLowerCase();

      // EMAIL duplicado → suele contener @
      if (details.includes('@')) {
        this.alertService.error(
          'Email duplicado',
          'Ya existe un cliente con ese email.'
        );
        return;
      }

      // DNI duplicado → letras/números, sin @
      if (details.includes('dni')) {
        this.alertService.error(
          'DNI duplicado',
          'Ya existe un cliente con ese DNI.'
        );
        return;
      }

      // Fallback seguro
      this.alertService.error(
        'Datos duplicados',
        'Ya existe un cliente con esos datos.'
      );
      return;
    }

    // =========================
    // 400 → VALIDACIÓN DE CAMPOS
    // =========================
    if (err.status === 400) {
      const details = err?.error?.error?.details;

      if (!details || typeof details !== 'object') {
        this.alertService.error(
          'Datos no válidos',
          'Revisa los datos introducidos.'
        );
        return;
      }

      const firstField = Object.keys(details)[0];
      const message = details[firstField];

      this.alertService.error(
        'Error de validación',
        message
      );
      return;
    }

    // =========================
    // ERROR NO CONTROLADO
    // =========================
    this.alertService.error(
      'Error',
      'Ha ocurrido un error inesperado.'
    );
  }
}
