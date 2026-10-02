import { Component, EventEmitter, OnInit, Output, ChangeDetectionStrategy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { LucideAngularModule } from 'lucide-angular';
import { ClientListItem, ClientService } from '../../core/services/client.service';
import { PetTypeService } from '../../core/services/pet-type.service';
import {PetApi, PetCreatePayload, PetService, PetUpdatePayload} from '../../core/services/pet.service';
import { AlertService } from '../../core/services/alert.service';

interface Propietario {
  id: number;
  nombre: string;
  email?: string;
  telefono?: string;
}

// Texto para UI
interface Mascota {
  id: number;
  nombre: string;
  isActive: boolean;
  especie: string;
  especieId: number | null;
  raza?: string;
  propietario: string;
  propietarioId: number | null;
  fechaNacimiento?: string;
  edad?: string;
  chip?: string;
  sexo?: string;
  color?: string;
  peso?: string;
  esterilizado: boolean;
  ultimaCita?: string;
}

interface MascotaForm {
  nombre: string;
  propietarioId: number | null;
  petTypeId: number | null;
  raza: string;
  fechaNacimiento: string;
  chip: string;
  sexo: string;
  color: string | null;
  peso: string;
  esterilizado: 'si' | 'no' | '';
  alergias: string;
  seguro: string;
  numPoliza: string;
  notas: string;
}

interface Especie {
  id: number;
  name: string;
  description: string;
}

type activeFilter  = 'all' | 'active' | 'inactive';


@Component({
  selector: 'app-pacientes',
  standalone: true,
  imports: [CommonModule, FormsModule, LucideAngularModule],
  changeDetection: ChangeDetectionStrategy.Eager,
  templateUrl: './pacientes.component.html'
})
export class PacientesComponent implements OnInit {
  @Output() navigate = new EventEmitter<{ page: string; data?: unknown }>();

  constructor(
    private router: Router,
    private clientService: ClientService,
    private petService: PetService,
    private petTypeService: PetTypeService,
    private alertService: AlertService
  ) {}

  searchTerm = '';
  isMascotaDialogOpen = false;
  isEditMode = false;
  editingMascotaId: number | null = null;

  propietarios: Propietario[] = [];
  mascotas: Mascota[] = [];
  especies: Especie[] = [];

  activeFilter: activeFilter = 'active';

  mascotaForm: MascotaForm = this.emptyForm();

  ngOnInit(): void {
    this.loadPropietarios();
    this.loadMascotas();
    this.loadEspecies();
  }

  go(page: string, data?: any) {
    // Navegación controlada con fallback al emitter
    if (page === 'detalle-mascota' && data?.id) {
      void this.router.navigate(['/pacientes/ficha', data.id]);
    } else {
      this.navigate.emit({ page, data });
    }
  }

  get filteredMascotas(): Mascota[] {
    const term = this.searchTerm.toLowerCase().trim();
    if (!term) return this.mascotas;

    return this.mascotas.filter((m) => {
      const nombre = (m.nombre || '').toLowerCase();
      const propietario = (m.propietario || '').toLowerCase();
      const chip = (m.chip || '').toLowerCase();
      return (
        nombre.includes(term) ||
        propietario.includes(term) ||
        chip.includes(term)
      );
    });
  }

  // Cambia el estado de activo/inactivo de una mascota
  toggleEstado(mascota: Mascota): void {

    // Elegimos endpoint según el estado actual
    const request$ = mascota.isActive
      ? this.petService.deactivatePet(mascota.id)
      : this.petService.activatePet(mascota.id);

    request$.subscribe({
      next: () => {
        void this.alertService.success(
          mascota.isActive ? 'Mascota desactivada' : 'Mascota activada',
          mascota.isActive
            ? `${mascota.nombre} ha sido desactivada correctamente.`
            : `${mascota.nombre} ha sido activada correctamente.`
        );

        this.loadMascotas();
      },
      error: (error) => {
        this.handleMascotaSaveError(error);
      }
    });
  }

  // Carga mascotas al cambiar el filtro de actividad/inactividad/todas
  onActiveFilterChange(): void {
    this.loadMascotas();
  }

  openMascotaDialog(): void {
    this.isEditMode = false;
    this.editingMascotaId = null;
    this.resetForm();
    this.isMascotaDialogOpen = true;
  }

  openEditMascotaDialog(mascota: Mascota): void {
    this.isEditMode = true;
    this.editingMascotaId = mascota.id;
    this.isMascotaDialogOpen = true;
    this.fillFormFromMascota(mascota); // Carga inicial con datos de la fila

    // Cargar datos completos desde API y sobreescribir con fuente de verdad
    this.petService.getPetById(mascota.id).subscribe({
      next: (pet) => {
        if (pet) this.fillFormFromPet(pet, mascota);
      },
      error: (err) => console.error('Error al cargar mascota', err)
    });
  }

  closeMascotaDialog(resetForm = true): void {
    this.isMascotaDialogOpen = false;
    this.isEditMode = false;
    this.editingMascotaId = null;
    if (resetForm) this.resetForm();
  }

  handleSaveMascota(): void {
    // Validación defensiva
    const propietarioId = this.mascotaForm.propietarioId ?? null;
    if (!propietarioId) return;

    const petTypeId = this.mascotaForm.petTypeId ?? null;
    if (!petTypeId) return;

    const pesoMascota = this.mascotaForm.peso;

    // Validación peso
    if (this.isInvalidWeightInput(pesoMascota)) {
      this.alertService.error(
        'Peso no válido',
        'Introduce un valor numérico válido (ej. 12,5)'
      );
      return;
    }

    // CREATE (completo y limpio)
    const createPayload: PetCreatePayload = {
      clientId: propietarioId,
      name: this.mascotaForm.nombre.trim(),
      petTypeId: petTypeId,
      breed: this.mascotaForm.raza.trim() || null,
      birthDate: this.mascotaForm.fechaNacimiento || null,
      gender: this.toApiGender(this.mascotaForm.sexo),
      color: this.mascotaForm.color?.trim() || null,
      microchip: this.mascotaForm.chip.trim() || null,
      weight: this.normalizeWeight(pesoMascota),
      allergies: this.mascotaForm.alergias.trim() || null,
      sterilized: this.mascotaForm.esterilizado === 'si',
      insuranceProvider: this.mascotaForm.seguro.trim() || null,
      insurancePolicyNumber: this.mascotaForm.numPoliza.trim() || null,
      notes: this.mascotaForm.notas.trim() || null
    };

    // UPDATE (parcial y limpio)
    const updatePayload: PetUpdatePayload = {
      name: this.mascotaForm.nombre.trim(),
      clientId: this.mascotaForm.propietarioId,
      petTypeId: this.mascotaForm.petTypeId,
      breed: this.mascotaForm.raza || null,
      birthDate: this.mascotaForm.fechaNacimiento || null,
      gender: this.toApiGender(this.mascotaForm.sexo),
      color: this.mascotaForm.color || null,
      microchip: this.mascotaForm.chip || null,
      weight: this.normalizeWeight(this.mascotaForm.peso),
      sterilized: this.mascotaForm.esterilizado === 'si',
      allergies: this.mascotaForm.alergias || null,
      insuranceProvider: this.mascotaForm.seguro || null,
      insurancePolicyNumber: this.mascotaForm.numPoliza || null,
      notes: this.mascotaForm.notas || null
    };

    const request$ =
      this.isEditMode && this.editingMascotaId
        ? this.petService.updatePet(this.editingMascotaId, updatePayload)
        : this.petService.createPet(createPayload);

    request$.subscribe({
      next: () => {
        void this.alertService.success(
          this.isEditMode ? 'Mascota actualizada' : 'Mascota creada',
          this.isEditMode
            ? 'Los datos de la mascota se han actualizado correctamente.'
            : 'La mascota se ha creado correctamente.'
        );

        this.loadMascotas();
        this.closeMascotaDialog(true);
      },
      error: () =>
        this.alertService.error(
        'Error',
        'No se pudo guardar la mascota. Revisa los datos e inténtalo de nuevo.'
      )
    });
  }

  get isSaveDisabled(): boolean {
    // Reglas de requerido claras; trim para evitar espacios vacíos
    const f = this.mascotaForm;
    return (
      !f.nombre.trim() ||
      !f.propietarioId ||
      !f.petTypeId ||
      !f.raza.trim() ||
      !f.sexo ||
      !f.fechaNacimiento ||
      !f.peso.trim() ||
      !f.esterilizado
    );
  }

  resetForm(): void {
    this.mascotaForm = this.emptyForm();
  }

  emptyForm(): MascotaForm {
    // Estado inicial coherente con la UI
    return {
      nombre: '',
      propietarioId: null,
      petTypeId: null,
      raza: '',
      fechaNacimiento: '',
      chip: '',
      sexo: '',
      color: '',
      peso: '',
      esterilizado: '',
      alergias: '',
      seguro: '',
      numPoliza: '',
      notas: ''
    };
  }

  getPetIcon(especie: string): 'dog' | 'cat' | 'paw-print' {
    const value = (especie || '').toLowerCase();

    if (value.includes('perro')) return 'dog';
    if (value.includes('gato')) return 'cat';

    // Todas las demás especies
    return 'paw-print';
  }

  stopRowClick(event: MouseEvent): void {
    event.stopPropagation();
  }

  trackByMascotaId(_index: number, item: Mascota): number {
    // Mejora de rendimiento en *ngFor
    return item.id;
  }

  private loadPropietarios(): void {
    this.clientService.getClients().subscribe({
      next: (clientes) => {
        this.propietarios = clientes.map((cliente) => this.mapClienteToPropietario(cliente));
      },
      error: (err) => console.error('Error al cargar propietarios', err)
    });
  }

  private loadEspecies(): void {
    this.petTypeService.getAll().subscribe({
      next: (especies) => {
        this.especies = especies;
      },
      error: (err) => console.error('Error al cargar especies', err)
    });
  }

  private loadMascotas(): void {

    let isActiveParam: boolean | undefined;

    switch (this.activeFilter) {
      case 'active':
        isActiveParam = true;
        break;
      case 'inactive':
        isActiveParam = false;
        break;
      default:
        isActiveParam = undefined;
    }

    this.petService.getPets(isActiveParam).subscribe({
      next: (pets) => {
        this.mascotas = pets.map((pet) => this.mapPetToMascota(pet));
      },
      error: (err) => console.error('Error al cargar mascotas', err)
    });
  }

  private mapClienteToPropietario(cliente: ClientListItem): Propietario {
    // Formateo de nombre y valores opcionales
    return {
      id: cliente.id,
      nombre: `${cliente.firstName ?? ''} ${cliente.lastName ?? ''}`.trim(),
      email: cliente.email,
      telefono: cliente.phone || ''
    };
  }

  private mapPetToMascota(pet: PetApi): Mascota {
    // Mapeo robusto desde API a modelo de UI
    const client = pet.client || {};
    const propietarioNombre = client.fullName
      ? client.fullName
      : `${client.firstName || ''} ${client.lastName || ''}`.trim();

    return {
      id: pet.id,
      nombre: pet.name,
      isActive: pet.isActive,
      especie: typeof pet.petType === 'object' ? pet.petType.name : 'Otros',
      especieId: typeof pet.petType === 'object' ? pet.petType.id : null,
      raza: pet.breed || '',
      propietario: propietarioNombre || 'Sin propietario',
      propietarioId: client.id ?? null,
      fechaNacimiento: pet.birthDate || '',
      edad: this.computeAgeYears(pet.birthDate), // Cálculo de edad a partir de la fecha
      chip: pet.microchip || '',
      sexo: this.toUiGender(pet.gender),
      color: pet.color || 'Sin datos',
      peso: pet.weight !== null && pet.weight !== undefined ? String(pet.weight) : '',
      esterilizado: Boolean(pet.sterilized),
      ultimaCita: pet.lastAppointmentDate || 'Sin citas'
    };
  }

  private fillFormFromMascota(mascota: Mascota): void {
    // Pre-cargar el formulario con los datos que ya tenemos en la tabla
    this.mascotaForm = {
      nombre: mascota.nombre || '',
      propietarioId: mascota.propietarioId ?? null,
      petTypeId: mascota.especieId ?? null,
      raza: mascota.raza || '',
      fechaNacimiento: mascota.fechaNacimiento || '',
      chip: mascota.chip || '',
      sexo: mascota.sexo || '',
      color: mascota.color || '',
      peso: mascota.peso || '',
      esterilizado: mascota.esterilizado ? 'si' : 'no',
      alergias: '',
      seguro: '',
      numPoliza: '',
      notas: ''
    };
  }

  private fillFormFromPet(pet: PetApi, mascota?: Mascota): void {
    // Sobreescribe con datos del backend, manteniendo fallback a lo que ya hay
    this.mascotaForm = {
      nombre: pet.name || mascota?.nombre || '',
      propietarioId: pet.client?.id ?? mascota?.propietarioId ?? null,
      petTypeId: typeof pet.petType === 'object' ? pet.petType.id : null,
      raza: pet.breed || mascota?.raza || '',
      fechaNacimiento: pet.birthDate || mascota?.fechaNacimiento || '',
      chip: pet.microchip || mascota?.chip || '',
      sexo: this.toUiGender(pet.gender) || mascota?.sexo || '',
      color: pet.color ||  mascota?.color || '',
      peso: pet.weight !== null && pet.weight !== undefined ? String(pet.weight) : mascota?.peso || '',
      esterilizado:
        pet.sterilized === null || pet.sterilized === undefined
          ? mascota?.esterilizado ? 'si' : 'no'
          : pet.sterilized ? 'si' : 'no',
      alergias: pet.allergies || '',
      seguro: pet.insuranceProvider || '',
      numPoliza: pet.insurancePolicyNumber || '',
      notas: pet.notes || ''
    };
  }

  private computeAgeYears(dateStr?: string | null): string {
    // Calcula edad en años desde una fecha ISO (YYYY-MM-DD); si no hay fecha, devuelve vacío
    if (!dateStr) return '';
    const birth = new Date(dateStr);
    if (isNaN(birth.getTime())) return '';
    const now = new Date();
    let years = now.getFullYear() - birth.getFullYear();
    const hasNotHadBirthdayYet =
      now.getMonth() < birth.getMonth() ||
      (now.getMonth() === birth.getMonth() && now.getDate() < birth.getDate());
    if (hasNotHadBirthdayYet) years -= 1;
    return years >= 0 ? `${years} años` : '';
  }

  private isInvalidWeightInput(value: string): boolean {
    if (!value) return false;
    const cleaned = value.replace(',', '.').replace(/[^\d.]/g, '');
    return cleaned === '' || cleaned === '.';
  }


  private normalizeWeight(value: string): number | null {
    // Acepta "25", "25 kg", "25,3kg" => 25.3
    //if (!value) return null;
    //const cleaned = value.replace(',', '.').replace(/[^\d.]/g, '');
    //const parsed = Number(cleaned);
    //return Number.isFinite(parsed) ? parsed : null;

    if (!value) return null;

    const cleaned = value
      .replace(',', '.')
      .replace(/[^\d.]/g, '');

    if (!cleaned || cleaned === '.') {
      return null;
    }

    const parsed = Number(cleaned);

    return Number.isFinite(parsed) && parsed > 0 ? parsed : null;

  }

  private toApiGender(value: string): string {
    // Mapea UI -> API en minúsculas, con valor por defecto seguro
    const normalized = (value || '').toLowerCase();
    if (normalized === 'macho') return 'macho';
    if (normalized === 'hembra') return 'hembra';
    return 'macho';
  }

  private toUiGender(value?: string | null): string {
    // Mapea API -> UI con capitalización
    if (!value) return '';
    const normalized = value.toLowerCase();
    if (normalized === 'macho') return 'Macho';
    if (normalized === 'hembra') return 'Hembra';
    return value;
  }

  private handleMascotaSaveError(err: any): void {

    // =========================
    // 400 → VALIDACIÓN DTO
    // =========================
    if (err.status === 400) {
      const fieldErrors = err?.error?.error?.details;

      if (!fieldErrors) {
        this.alertService.error(
          'Datos no válidos',
          'Revisa los campos del formulario.'
        );
        return;
      }

      if (fieldErrors.clientId) {
        this.alertService.error('Propietario no válido', fieldErrors.clientId);
        return;
      }

      if (fieldErrors.name) {
        this.alertService.error('Nombre no válido', fieldErrors.name);
        return;
      }

      if (fieldErrors.petTypeId) {
        this.alertService.error('Especie no válida', fieldErrors.petTypeId);
        return;
      }

      if (fieldErrors.gender) {
        this.alertService.error('Género no válido', fieldErrors.gender);
        return;
      }

      if (fieldErrors.birthDate) {
        this.alertService.error('Fecha no válida', fieldErrors.birthDate);
        return;
      }

      if (fieldErrors.microchip) {
        this.alertService.error('Microchip no válido', fieldErrors.microchip);
        return;
      }

      if (fieldErrors.weight) {
        this.alertService.error('Peso no válido', fieldErrors.weight);
        return;
      }

      if (fieldErrors.sterilized) {
        this.alertService.error('Esterilización no válida', fieldErrors.sterilized);
        return;
      }

      if (fieldErrors.insuranceProvider) {
        this.alertService.error('Aseguradora no válida', fieldErrors.insuranceProvider);
        return;
      }

      if (fieldErrors.insurancePolicyNumber) {
        this.alertService.error('Número de póliza no válido', fieldErrors.insurancePolicyNumber);
        return;
      }

      // Fallback
      this.alertService.error(
        'Error de validación',
        'Revisa los datos introducidos.'
      );
      return;
    }

    // =========================
    // ERROR NO CONTROLADO
    // =========================
    this.alertService.error(
      'Error',
      'No se pudo guardar la mascota. Inténtalo de nuevo.'
    );
  }

}
