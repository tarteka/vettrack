import { CommonModule } from '@angular/common';
import { Component, OnInit, inject, signal, computed, ChangeDetectionStrategy } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { LucideDynamicIcon, LucideSearch as Search, LucidePlus as Plus, LucideEdit as Edit } from '@lucide/angular';
import { MedicalService } from '../../core/services/medical-service.service';
import { Servicio, ServiceCategory } from '../../core/models/service.model';
import { AlertService } from '../../core/services/alert.service';

interface ServicioPayload {
  name: string;
  description: string;
  unitPrice: number;
  taxRate: number;
  categoryId: number;
}

@Component({
  selector: 'app-servicios',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    LucideDynamicIcon,
  ],
  changeDetection: ChangeDetectionStrategy.Eager,
  templateUrl: './servicios.component.html',
})

export class ServiciosComponent implements OnInit {

  private alertService = inject(AlertService);
  private medicalService = inject(MedicalService);
  categories = signal<ServiceCategory[]>([]);

  selectedCategoryId = signal<number | null>(null);

  // icons
  readonly Search = Search;
  readonly Plus = Plus;
  readonly Edit = Edit;

  servicios = signal<Servicio[]>([]);
  searchTerm = signal('');
  isDialogOpen = signal(false);
  editingServicio = signal<Servicio | null>(null);

  servicioForm = {
    name: '',
    price: '',
    taxRate: null as number | null,
    description: '',
    categoryId: null as number | null,
  };


  filteredServicios = computed(() => {
    const term = this.searchTerm().toLowerCase().trim();
    const categoryId = this.selectedCategoryId();



    return this.servicios().filter(servicio => {
      const matchesText =
        !term ||
        servicio.name.toLowerCase().includes(term) ||
        servicio.category.name.toLowerCase().includes(term);

      const matchesCategory =
        categoryId === null || servicio.category.id === categoryId;

      return matchesText && matchesCategory;
    });
  });

  ngOnInit() {
    this.loadServices();
    this.loadCategories();
  }

  loadServices() {
    this.medicalService.getServices().subscribe({
    next: (data) => {
      // Mapeamos los datos para asegurar que unitPrice tenga el valor de price si viene así del back
      const mappedData = data.map(s => ({
        ...s,
        unitPrice: s.unitPrice || (s as any).price // Si unitPrice es null, usa price
      }));
      this.servicios.set(mappedData);
    },
    error: (err) => console.error('Error cargando servicios:', err)
    });
  }

  loadCategories() {
    this.medicalService.getCategories().subscribe({
      next: (data) => this.categories.set(data),
      error: (err) => console.error('Error al cargar categorías', err)
    });
  }

  openDialog(servicio?: Servicio) {
    if (servicio) {
      this.editingServicio.set(servicio);
      this.servicioForm = {
        name: servicio.name,
        price: servicio.unitPrice ? servicio.unitPrice.toString() : '0',
        taxRate: servicio.taxRate,
        description: servicio.description,
        categoryId: servicio.category.id,
      };
    } else {
      this.editingServicio.set(null);
      this.servicioForm = {
        name: '',
        price: '',
        taxRate: 21,
        description: '',
        categoryId: null };
    }
    this.isDialogOpen.set(true);
  }

closeDialog() {
  this.isDialogOpen.set(false);
  this.editingServicio.set(null);
  this.servicioForm = { name: '', price: '', description: '', categoryId: null, taxRate: 21 };
}

saveServicio() {
  // 1. Validación usando los nuevos nombres de campo
  if (
    !this.servicioForm.name ||
    !this.servicioForm.price ||
    this.servicioForm.categoryId === null ||
    this.servicioForm.taxRate === null
  ) {
    this.alertService.error('Error', 'Por favor, completa los campos obligatorios');
    return;
  }

  const unitPrice = Number.parseFloat(this.servicioForm.price);
  if (Number.isNaN(unitPrice)) {
    this.alertService.error('Error', 'El precio no es válido');
    return;
  }

  const taxRate = Math.max(0, Math.min(100, this.servicioForm.taxRate));

  // Preparamos el objeto para el backend
  const payload: ServicioPayload = {
    name: this.servicioForm.name,
    description: this.servicioForm.description,
    unitPrice,
    taxRate,
    categoryId: this.servicioForm.categoryId!,
  };

  if (this.editingServicio()) {
    // 2. Lógica para EDITAR (PUT/PATCH)
    const id = this.editingServicio()!.id;
    // Asumiendo que añades updateService a tu MedicalService
    this.medicalService.updateService(id, payload).subscribe({
      next: () => {
        void this.alertService.success('Actualizado', 'El servicio ha sido actualizado correctamente');
        this.loadServices();
        this.closeDialog();
      },
      error: () =>
        this.alertService.error('Error', 'No se ha podido actualizar el servicio')
    });
  } else {
    // 3. Lógica para CREAR (POST)
    this.medicalService.createService(payload).subscribe({
      next: () => {
        // Actualizamos la signal añadiendo el nuevo servicio al final
        void this.alertService.success('Creado', 'El servicio ha sido creado correctamente');
        this.loadServices()
        this.closeDialog();
      },
      error: () =>
        this.alertService.error('Error', 'No se ha podido crear el servicio')
    });
  }
}

  // Cambia el estado de activo/inactivo de un servicio
  toggleEstado(servicio: Servicio): void {

    // Elegimos endpoint según el estado actual
    const request$ = servicio.isActive
      ? this.medicalService.deactivateService(servicio.id)
      : this.medicalService.activateService(servicio.id);

    request$.subscribe({
      next: () => {
        void this.alertService.success(
          servicio.isActive ? 'Servicio desactivado' : 'Servicio activado',
          servicio.isActive
            ? `${servicio.name} ha sido desactivado correctamente.`
            : `${servicio.name} ha sido activado correctamente.`
        );

        // Recargamos la lista completa (fuente de verdad = backend)
        this.loadServices();
      },
      error: () => {
        this.alertService.error(
          'Error',
          'No se ha podido cambiar el estado del servicio'
        );
      }
    });
  }

}
