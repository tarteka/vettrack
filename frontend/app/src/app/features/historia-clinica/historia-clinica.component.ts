
import { Component, EventEmitter, Output, inject, ChangeDetectionStrategy } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { LucideAngularModule, LucideIconProvider, LUCIDE_ICONS, ArrowLeft, Save, Info, Plus, X } from 'lucide-angular';
import { ActivatedRoute, Router } from '@angular/router';
import { AlertService } from '../../core/services/alert.service';
import { MedicalRecordCreatePayload, MedicalRecordService } from '../../core/services/medical-record.service';
import { PetApi, PetService } from '../../core/services/pet.service';
import { TreatmentService } from '../../core/services/treatment.service';
import { treatment } from '../../core/models/treatment.model';

@Component({
  selector: 'app-historia-clinica',
  standalone: true,
  imports: [
    FormsModule,
    LucideAngularModule
],
  providers: [
    {
      provide: LUCIDE_ICONS,
      multi: true,
      useValue: new LucideIconProvider({ ArrowLeft, Save, Info, Plus, X })
    }
  ],
  changeDetection: ChangeDetectionStrategy.Eager,
  templateUrl: './historia-clinica.component.html'
})
export class HistoriaClinicaComponent {
  @Output() navigate = new EventEmitter<string>();

  private route = inject(ActivatedRoute);
  private router = inject(Router);
  private petService = inject(PetService);
  private recordService = inject(MedicalRecordService);
  private alertService = inject(AlertService);
  private treatmentService = inject(TreatmentService);

  // icons
  readonly ArrowLeft = ArrowLeft;
  readonly Save = Save;
  readonly Info = Info;
  readonly Plus = Plus;
  readonly X = X;

  mascotas: Array<{ id: number; nombre: string }> = [];
  selectedPetId: number | null = null;
  petsLoading = false;


  readonly tiposConsulta = [
    'Consulta general',
    'Cirugía',
    'Emergencia',
    'Revisión',
    'Vacunación',
    'Otro'
  ];

  formData = {
    fecha: this.todayISO(),
    mascota: '',
    tipo: 'Consulta general',
    diagnostico: '',
    sintomas: '',
    procedimientos: '',
    tratamiento: '',
    observaciones: '',
  };

  isTratamientoDialogOpen = false;

  tratamientoForm = {
    nombre: '',
    medicamento: '',
    dosis: '',
    frecuencia: '',
    fechaInicio: '',
    fechaFin: '',
    indicaciones: '',
  };

  tratamientosPendientes: any[] = [];


  constructor() {
    const petIdParam = this.route.snapshot.paramMap.get('id');
    const petId = petIdParam ? Number(petIdParam) : null;
    if (petId && Number.isFinite(petId)) {
      this.selectedPetId = petId;
      this.formData.mascota = String(petId);
    }
    this.loadPets();
  }

  onNavigate(page: string) {
    this.navigate.emit(page);
  }

  handleSubmit() {
    const petId = Number(this.formData.mascota);
    if (!petId || !Number.isFinite(petId)) {
      this.alertService.error('Error', 'Selecciona una mascota válida.');
      return;
    }

    const description = this.buildDescripcion();
    if (!description) {
      this.alertService.error('Error', 'Completa síntomas o procedimientos para la descripción.');
      return;
    }

    const payload: MedicalRecordCreatePayload = {
      type: this.formData.tipo,
      description,
      diagnosis: this.formData.diagnostico,
      procedures: this.formData.procedimientos || null,
      notes: this.buildNotas()
    };

    this.recordService.createMedicalRecord(petId, payload).subscribe({
      next: (res) => {
        const recordId = res.data.id;
        this.guardarTratamientosPendientes(recordId, petId);
      },
      error: (err) => {
        console.error('Error al guardar historia clínica', err);
        this.alertService.error('Error', 'No se pudo guardar la historia clínica.');
      }
    });
  }

  openTratamientoDialog() {
    this.isTratamientoDialogOpen = true;
  }

  closeTratamientoDialog() {
    this.isTratamientoDialogOpen = false;
  }

  //Guardar tratamientos
  handleSaveTratamiento() {
    const nuevoTratamiento = {
      nombre: this.tratamientoForm.nombre,
      medicamento: this.tratamientoForm.medicamento,
      dosis: this.tratamientoForm.dosis,
      frecuencia: this.tratamientoForm.frecuencia,
      fechaInicio: this.tratamientoForm.fechaInicio,
      fechaFin: this.tratamientoForm.fechaFin || null,
      indicaciones: this.tratamientoForm.indicaciones,
    };

    this.tratamientosPendientes.push(nuevoTratamiento);

    void this.alertService.success(
      'Tratamiento añadido',
      'Se guardará junto con la consulta'
    );

    this.closeTratamientoDialog();
    this.pintarTratamiento();

    // Limpieza segura
    this.tratamientoForm = {
      nombre: '',
      medicamento: '',
      dosis: '',
      frecuencia: '',
      fechaInicio: '',
      fechaFin: '',
      indicaciones: '',
    };
  }

  private guardarTratamientosPendientes(
    recordId: number,
    petId: number
  ): void {
    if (this.tratamientosPendientes.length === 0) {
      this.finalizarGuardado();
      return;
    }

    let pendientes = this.tratamientosPendientes.length;
    let errorMostrado = false;

    this.tratamientosPendientes.forEach((t) => {
      const payload = {
        name: t.nombre,
        medicine: t.medicamento,
        dose: t.dosis,
        frequency: t.frecuencia,
        instructions: t.indicaciones,
        startDate: t.fechaInicio,
        endDate: t.fechaFin,
        medicalRecordId: recordId
      };

      this.treatmentService.addTreatment(String(petId), payload).subscribe({
        next: () => {
          pendientes--;
          if (pendientes === 0) {
            this.finalizarGuardado();
          }
        },
        error: () => {
          if (!errorMostrado) {
            errorMostrado = true;
            this.alertService.error(
              'Error',
              'Algún tratamiento no se pudo guardar'
            );
          }
        }
      });
    });
  }

  private finalizarGuardado(): void {
    this.tratamientosPendientes = [];
    void this.alertService.success(
      'Historia clínica guardada',
      'Consulta y tratamientos creados correctamente'
    );
    this.goBack();
  }


  //Pintar Tratamientos en consulta
  pintarTratamiento() {
    const lines = [
      `Nombre: ${this.tratamientoForm.nombre}`,
      `Medicamento: ${this.tratamientoForm.medicamento}`,
      `Dosis: ${this.tratamientoForm.dosis}`,
      `Frecuencia: ${this.tratamientoForm.frecuencia}`,
      `Inicio: ${this.tratamientoForm.fechaInicio}`,
      `Fin: ${this.tratamientoForm.fechaFin}` || null,
      `Indicaciones: ${this.tratamientoForm.indicaciones}`
    ];
    const bloque = lines.join('\n');
    this.formData.tratamiento = this.formData.tratamiento
      ? `${this.formData.tratamiento}\n\n${bloque}`
      : bloque;
    this.tratamientoForm = {
      nombre: '',
      medicamento: '',
      dosis: '',
      frecuencia: '',
      fechaInicio: '',
      fechaFin: '',
      indicaciones: '',
    };
  }

  get isTratamientoInvalid(): boolean {
    const t = this.tratamientoForm;
    return (
      !t.nombre ||
      !t.medicamento ||
      !t.dosis ||
      !t.frecuencia ||
      !t.fechaInicio ||
      //!t.fechaFin ||
      !t.indicaciones
    );
  }

  private loadPets(): void {
    this.petsLoading = true;
    this.petService.getPets(true).subscribe({
      next: (pets: PetApi[]) => {
        this.mascotas = pets.map((pet) => ({
          id: pet.id,
          nombre: this.formatPetOption(pet)
        }));
        this.petsLoading = false;
      },
      error: (err) => {
        console.error('Error al cargar mascotas', err);
        this.petsLoading = false;
      }
    });
  }

  private formatPetOption(pet: PetApi): string {
    const owner = pet.client?.fullName
      ? pet.client.fullName
      : `${pet.client?.firstName || ''} ${pet.client?.lastName || ''}`.trim();
    return `${pet.name}${owner ? ` - ${owner}` : ''}`;
  }

  private buildDescripcion(): string {
    const parts: string[] = [];
    if (this.formData.sintomas) parts.push(`Síntomas: ${this.formData.sintomas}`);
    return parts.join('\n') || this.formData.procedimientos || '';
  }

  private buildNotas(): string | null {
    const notesParts: string[] = [];
    //if (this.formData.tratamiento) notesParts.push(`Tratamiento:\n${this.formData.tratamiento}`);
    if (this.formData.observaciones) notesParts.push(`Observaciones: ${this.formData.observaciones}`);
    const value = notesParts.join('\n');
    return value.length > 0 ? value : null;
  }

  goBack() {
    if (this.selectedPetId) {
      this.router.navigate(['/pacientes/ficha', this.selectedPetId]);
      return;
    }
    if (this.navigate.observers.length > 0) {
      this.navigate.emit('pacientes');
      return;
    }
    this.router.navigate(['/pacientes']);
  }

  private todayISO(): string {
    return new Date().toISOString().split('T')[0];
  }
}
