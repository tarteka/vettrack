import { Component, EventEmitter, Input, OnInit, Output,inject} from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, ActivatedRoute } from '@angular/router';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { switchMap } from 'rxjs';
import { Router } from '@angular/router';
import { LucideAngularModule } from 'lucide-angular';
import { MedicalRecord, MedicalRecordCreatePayload, MedicalRecordService } from '../../core/services/medical-record.service';
import { PetService } from '../../core/services/pet.service';
import { TreatmentService } from '../../core/services/treatment.service';
import { treatment } from '../../core/models/treatment.model';
import { AlertService } from '../../core/services/alert.service';

type TipoConsulta =
  | 'Consulta'
  | 'Consulta general'
  | 'Cirugía'
  | 'Urgencia'
  | 'Emergencia'
  | 'Revisión'
  | 'Vacunación'
  | 'Otro'
  | string;
//type EstadoTratamiento = 'activo' | 'completado' | string;

interface Mascota {
  id: number;
  nombre: string;
  especie: string;
  raza: string;
  sexo: string;
  edad: string;
  fechaNacimiento: string; // yyyy-mm-dd
  peso: number | null;
  chip: string;
  propietarioId: number;
  propietario: string;
  propietarioDNI: string;
  propietarioTelefono: string;
  propietarioEmail: string;
  color: string;
  esterilizado: boolean;
  alergias: string;
  seguro: string;
}

interface HistoriaClinicaItem {
  id: number;
  fecha: string;
  tipo: TipoConsulta;
  veterinario: string;
  motivo: string;
  diagnostico: string;
  procedimientos: string;
  proximaCita?: string;
  notas?: string;
}


interface ConsultaForm {
  tipo: string;
  motivo: string;
  diagnostico: string;
  procedimientos: string;
  notas: string;
}

interface TratamientoForm {
  nombre: string;
  medicamento: string;
  dosis: string;
  frecuencia: string;
  fechaInicio: string;
  fechaFin: string;
  indicaciones: string;
}

interface MascotaForm {
  nombre: string;
  especie: string;
  raza: string;
  sexo: string;
  fechaNacimiento: string;
  peso: string;
  chip: string;
  color: string;
  esterilizado: boolean;
  alergias: string;
  seguro: string;
}

@Component({
  selector: 'app-ficha-paciente',
  standalone: true,
  imports: [CommonModule, RouterModule, FormsModule, ReactiveFormsModule, LucideAngularModule],
  templateUrl: './ficha-paciente.component.html'
})
export class FichaPacienteComponent implements OnInit {
  @Output() navigate = new EventEmitter<{ page: string; data?: any }>();

  @Input() mascotaId: number = 1;

  constructor(
    private router: Router,
    private route: ActivatedRoute,
    private petService: PetService,
    private medicalRecordService: MedicalRecordService,
    private treatmentService: TreatmentService
  ) {}

  // dialogs
  isConsultaDialogOpen = false;
  isTratamientoDialogOpen = false;
  isEditMascotaDialogOpen = false;

  // tabs
  activeTab: 'historia' | 'tratamientos' = 'historia';

  // toast simple
  toastVisible = false;
  toastMessage = '';
  toastType: 'success' | 'error' | 'info' = 'success';
  private toastTimer: any;

  mascota: Mascota = {
    id: 0,
    nombre: '',
    especie: '',
    raza: '',
    sexo: '',
    edad: '',
    fechaNacimiento: '',
    peso: null,
    chip: '',
    propietarioId: 0,
    propietario: '',
    propietarioDNI: '',
    propietarioTelefono: '',
    propietarioEmail: '',
    color: '',
    esterilizado: false,
    alergias: '',
    seguro: ''
  };

  private alertService = inject(AlertService);
  mascotaForm: MascotaForm = this.emptyMascotaForm();

  historiaClinica: HistoriaClinicaItem[] = [];

 // tratamientos: TratamientoItem[] = [];
  tratamientos: treatment[] = [];
  currentTratamientoId: number | null = null; // para editar tratamiento

  consultaForm: ConsultaForm = this.emptyConsultaForm();
  tratamientoForm: TratamientoForm = this.emptyTratamientoForm();

  ngOnInit(): void {
    const idParam = this.route.snapshot.paramMap.get('id');
    const id = idParam ? Number(idParam) : this.mascotaId;
    if (id) {
      this.mascotaId = id;
      this.loadMascota(id);
      this.loadHistoriaClinica(id);
    }
  }

  go(page: string, data?: any) {
    if (page === 'mascotas') {
      // Esto te lleva de vuelta a la lista de pacientes
      void this.router.navigate(['/pacientes']);
    } else if (page === 'ficha-cliente' && data?.clienteId) {
      void this.router.navigate(['/clients/ficha', data.clienteId]);
    } else {
      this.navigate.emit({ page, data });
    }
  }

  goToHistoriaClinica() {
    if (!this.mascotaId) return;
    this.router.navigate(['/pacientes/historia', this.mascotaId]);
  }

  // lists - status filters
  get tratamientosActivos(): treatment[] {
    return this.tratamientos.filter(t => t.status === 'activo');
  }

  get tratamientosCompletados(): treatment[] {
    return this.tratamientos.filter(t => t.status === 'completado');
  }

  get tratamientosSuspendidos(): treatment[] {
    return this.tratamientos.filter(t => t.status === 'suspendido');
  }

  // tabs
  setTab(tab: 'historia' | 'tratamientos') {
    this.activeTab = tab;
  }

  // dialogs
  openConsultaDialog() { this.isConsultaDialogOpen = true; }
  openTratamientoDialog() { this.isTratamientoDialogOpen = true; }

  closeConsultaDialog(reset = true) {
    this.isConsultaDialogOpen = false;
    if (reset) this.consultaForm = this.emptyConsultaForm();
  }

  closeTratamientoDialog(reset = true) {
    this.isTratamientoDialogOpen = false;
    if (reset) this.tratamientoForm = this.emptyTratamientoForm();
  }

  openEditMascotaDialog() {
    this.mascotaForm = {
      nombre: this.mascota.nombre,
      especie: this.mascota.especie,
      raza: this.mascota.raza,
      sexo: this.mascota.sexo,
      fechaNacimiento: this.mascota.fechaNacimiento,
      peso: this.mascota.peso !== null
        ? String(this.mascota.peso).replace('.', ',')
        : '',
      chip: this.mascota.chip,
      color: this.mascota.color,
      esterilizado: this.mascota.esterilizado,
      alergias: this.mascota.alergias,
      seguro: this.mascota.seguro
    };
    this.isEditMascotaDialogOpen = true;
  }

  closeEditMascotaDialog() {
    this.isEditMascotaDialogOpen = false;
  }

  // saves 
  handleSaveConsulta() {
    const payload: MedicalRecordCreatePayload = {
      type: this.mapTipoConsultaToApi(this.consultaForm.tipo),
      description: this.consultaForm.motivo,
      diagnosis: this.consultaForm.diagnostico,
      procedures: this.consultaForm.procedimientos || null,
      notes: this.consultaForm.notas || null
    };

    this.medicalRecordService.createMedicalRecord(this.mascotaId, payload).subscribe({
      next: () => {
        this.loadHistoriaClinica(this.mascotaId);
        this.closeConsultaDialog(true);
        this.showToast('Consulta guardada exitosamente', 'success');
      },
      error: (err) => {
        console.error('Error al guardar consulta', err);
        this.showToast('Error al guardar la consulta', 'error');
      }
    });
  }

  //Abrir modal para editar tratamiento
  openModificarTratamientoModal(treatment: treatment) {
    this.tratamientoForm = {
      nombre: treatment.name,
      medicamento: treatment.medicine,
      dosis: treatment.dose,
      frecuencia: treatment.frequency,
      indicaciones: treatment.instructions,
      fechaInicio: this.normalizeDateToInput(treatment.startDate),
      fechaFin: this.normalizeDateToInput(treatment.endDate),
    };
    this.isTratamientoDialogOpen = true;
    this.currentTratamientoId = treatment.id; // guardar id para actualizar
  }

  //Update tratamiento
  async handleUpdateTratamiento(): Promise<void> {
    if (!this.mascotaId || !this.currentTratamientoId) return;

    const payload = {
      name: this.tratamientoForm.nombre,
      medicine: this.tratamientoForm.medicamento,
      dose: this.tratamientoForm.dosis,
      frequency: this.tratamientoForm.frecuencia,
      instructions: this.tratamientoForm.indicaciones,
      startDate: this.tratamientoForm.fechaInicio,
      endDate: this.tratamientoForm.fechaFin || null,
    };

    this.treatmentService.updateTreatment(
      String(this.mascotaId),
      String(this.currentTratamientoId),
      payload
    ).subscribe({
      next: () => {
        this.alertService.success('success', 'Tratamiento actualizado correctamente');
        this.loadTratamientos(this.mascotaId);
        this.closeTratamientoDialog(false);
      },
      error: (err) => {
        console.error('Error al actualizar tratamiento', err);
        this.alertService.error('error', 'Por favor, inténtalo de nuevo más tarde');
      }
    });
  }


  handleSaveMascota() {
    const payload = {
      name: this.mascotaForm.nombre,
      breed: this.mascotaForm.raza,
      birthDate: this.mascotaForm.fechaNacimiento,
      gender: this.mapSexoToApi(this.mascotaForm.sexo),
      microchip: this.mascotaForm.chip,
      weight: this.normalizeWeight(this.mascotaForm.peso),
      sterilized: this.mascotaForm.esterilizado,
      allergies: this.mascotaForm.alergias,
      insuranceProvider: this.mascotaForm.seguro
    };

    this.petService.updatePet(this.mascotaId, payload).subscribe({
      next: () => {
        this.loadMascota(this.mascotaId);
        this.isEditMascotaDialogOpen = false;
        this.showToast('Informacion de la mascota actualizada exitosamente', 'success');
      },
      error: (err) => {
        console.error('Error al actualizar mascota', err);
        this.showToast('Error al actualizar la mascota', 'error');
      }
    });
  }

  // UI helpers
  getTipoBadgeClass(tipo: string): string {
    switch (tipo) {
      case 'Consulta general':
      case 'Consulta':
        return 'badge-blue';
      case 'Cirugía':
      case 'Cirugia':
        return 'badge-purple';
      case 'Urgencia':
      case 'Emergencia':
        return 'badge-red';
      case 'Revisión':
      case 'Revision':
        return 'badge-gray';
      case 'Vacunación':
      case 'Vacunacion':
        return 'badge-blue';
      default:
        return 'badge-gray';
    }
  }

  getTipoIconName(tipo: string): 'stethoscope' | 'scissors' | 'alert-triangle' | 'circle-question-mark' {
    switch (tipo) {
      case 'Consulta general':
      case 'Consulta':
        return 'stethoscope';
      case 'Cirugía':
      case 'Cirugia':
        return 'scissors';
      case 'Urgencia':
      case 'Emergencia':
        return 'alert-triangle';
      default:
        return 'circle-question-mark';
    }
  }

  getEsterilizadoBadgeClass(): string {
    return this.mascota.esterilizado ? 'badge-green' : 'badge-gray';
  }

  getEsterilizadoText(): string {
    return this.mascota.esterilizado ? 'Si' : 'No';
  }

  formatBirthDate(): string {
    const d = new Date(this.mascota.fechaNacimiento);
    if (Number.isNaN(d.getTime())) return this.mascota.fechaNacimiento || '';
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    return `${day}/${month}/${year}`;
  }

  // disabled
  get isConsultaSaveDisabled(): boolean {
    return !this.consultaForm.tipo || !this.consultaForm.motivo || !this.consultaForm.diagnostico;
  }

  get isTratamientoSaveDisabled(): boolean {
    return (
      !this.tratamientoForm.nombre ||
      !this.tratamientoForm.medicamento ||
      !this.tratamientoForm.dosis ||
      !this.tratamientoForm.frecuencia ||
      !this.tratamientoForm.fechaInicio ||
      //!this.tratamientoForm.fechaFin ||
      !this.tratamientoForm.indicaciones
    );
  }

  get isMascotaSaveDisabled(): boolean {
    return (
      !this.mascotaForm.nombre ||
      !this.mascotaForm.especie ||
      !this.mascotaForm.raza ||
      !this.mascotaForm.sexo ||
      !this.mascotaForm.fechaNacimiento ||
      !this.mascotaForm.peso ||
      !this.mascotaForm.chip ||
      !this.mascotaForm.color ||
      !this.mascotaForm.alergias ||
      !this.mascotaForm.seguro
    );
  }

  // utils
  private calcularEdad(fechaNacimiento: string): string {
    const fecha = new Date(fechaNacimiento);
    if (Number.isNaN(fecha.getTime())) return '';
    const hoy = new Date();
    let edad = hoy.getFullYear() - fecha.getFullYear();
    const mes = hoy.getMonth() - fecha.getMonth();
    if (mes < 0 || (mes === 0 && hoy.getDate() < fecha.getDate())) edad--;
    return `${edad} ${edad === 1 ? 'año' : 'años'}`;
  }

  private formatDateEsShort(date: Date): string {
    return date.toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' });
  }

  private showToast(message: string, type: 'success' | 'error' | 'info' = 'success') {
    this.toastMessage = message;
    this.toastType = type;
    this.toastVisible = true;

    if (this.toastTimer) clearTimeout(this.toastTimer);
    this.toastTimer = setTimeout(() => (this.toastVisible = false), 2600);
  }

  private emptyConsultaForm(): ConsultaForm {
    return { tipo: '', motivo: '', diagnostico: '', procedimientos: '', notas: '' };
  }

  private emptyTratamientoForm(): TratamientoForm {
    return { nombre: '', medicamento: '', dosis: '', frecuencia: '', fechaInicio: '', fechaFin: '', indicaciones: '' };
  }

  private emptyMascotaForm(): MascotaForm {
    return {
      nombre: '',
      especie: '',
      raza: '',
      sexo: '',
      fechaNacimiento: '',
      peso: '',
      chip: '',
      color: '',
      esterilizado: false,
      alergias: '',
      seguro: ''
    };
  }

  private loadHistoriaClinica(petId: number): void {
    this.medicalRecordService.getMedicalRecords(petId).subscribe({
      next: (records) => {
        this.historiaClinica = records.map((record) => this.mapRecordToHistoria(record));
        //this.tratamientos = this.mapRecordsToTratamientos(records);
        this.loadTratamientos(petId);
      },
      error: (err) => console.error('Error al cargar historia clinica', err)
    });
  }

  // Carga los tratamientos desde el servicio TreatmentService
  private loadTratamientos(petId: number): void {
    this.treatmentService.getTreatments(String(petId)).subscribe({
        next: (treatments) => {
          this.tratamientos = treatments; // vienen con IDs reales y todos los campos
        },
    //error: (err) => console.error('Error al cargar tratamientos', err)
      error: (err) => this.alertService.error('Error', 'Error al cargar tratamientos')
    });
  }

//Suspender tratamiento
async suspenderTratamiento(treatment: treatment): Promise<void> {
  if (!this.mascotaId || !treatment.id) return;

  const motivo = await this.alertService.confirmSuspension(treatment);
  if (motivo === null) return; 

  const payload = {
    name: treatment.name,
    medicine: treatment.medicine,
    dose: treatment.dose,
    frequency: treatment.frequency,
    //instructions: `Motivo de cancelación: ${motivo} // ${treatment.instructions}`,
    instructions: treatment.instructions,
    startDate: this.normalizeDateToInput(treatment.startDate),
    endDate: this.getTodayDate(),
    suspendedReason: motivo
  };
  console.log('Payload para suspender tratamiento:', payload);

  this.treatmentService
    .updateTreatment(
      String(this.mascotaId),
      String(treatment.id),
      payload
    )
    .pipe(
      switchMap(() =>
        this.treatmentService.suspendTreatment(
          String(this.mascotaId),
          String(treatment.id)
        )
      )
    )
    .subscribe({
      next: () => {
        this.alertService.success(
          'success',
          'Tratamiento suspendido correctamente'
        );
        this.loadTratamientos(this.mascotaId);
        console.log('razon: ', treatment.suspendedReason);
      },
      error: (err) => {
        console.error('Error al suspender tratamiento', err);
        this.alertService.error(
          'error',
          'Por favor, inténtalo de nuevo más tarde'
        );
      }
    });
  }

  //Cribar motivo de cancelación de las instrucciones
  extractCancelReason(instructions: string): string | null {
    if (!instructions) return null;
    const match = instructions.match(/Motivo de cancelación: (.*?) \/\/ /);
    return match ? match[1] : null;
  }

  private loadMascota(petId: number): void {
    this.petService.getPetById(petId).subscribe({
      next: (pet) => {
        this.mascota = this.mapPetFromApi(pet);
      },
      error: (err) => console.error('Error al cargar mascota', err)
    });
  }

  private mapRecordToHistoria(record: MedicalRecord): HistoriaClinicaItem {
    //const { observaciones } = this.parseRecordNotes(record.notes);
    return {
      id: record.id,
      fecha: record.date,
      tipo: record.type,
      veterinario: record.veterinarian,
      motivo: record.description,
      diagnostico: record.diagnosis || '',
      procedimientos: record.procedures || '',
      //notas: observaciones || undefined
    };
  }

  private mapPetFromApi(pet: any): Mascota {
    const client = pet?.client || pet?.owner || {};
    const petType = typeof pet?.petType === 'string' ? pet.petType : pet?.petType?.name || pet?.petType?.type || '';
    const nombrePropietario = client.fullName
      ? client.fullName
      : `${client.firstName || ''} ${client.lastName || ''}`.trim();

    const birthDate = this.normalizeDateToInput(pet?.birthDate);
    const edad = pet?.age ? `${pet.age} años` : this.calcularEdad(birthDate);
    const seguro = [pet?.insuranceProvider, pet?.insurancePolicyNumber].filter(Boolean).join(' - ');

    return {
      id: pet?.id || 0,
      nombre: pet?.name || '',
      especie: petType || 'Otros',
      raza: pet?.breed || '',
      sexo: this.mapSexoToUi(pet?.gender),
      edad: edad || '',
      fechaNacimiento: birthDate || '',
      peso: typeof pet?.weight === 'number' ? pet.weight : null,
      chip: pet?.microchip || '',
      propietarioId: client?.id || 0,
      propietario: nombrePropietario || 'Sin propietario',
      propietarioDNI: client?.dni || '',
      propietarioTelefono: client?.phone || '',
      propietarioEmail: client?.email || '',
      color: pet?.color || 'Sin datos',
      esterilizado: Boolean(pet?.sterilized),
      alergias: pet?.allergies || 'Sin datos',
      seguro: seguro || 'Sin datos'
    };
  }

  private normalizeDateToInput(value: any): string {
    if (!value) return '';
    if (typeof value === 'string') {
      if (/^\d{4}-\d{2}-\d{2}$/.test(value)) return value;
      if (/^\d{2}-\d{2}-\d{4}$/.test(value)) {
        const [day, month, year] = value.split('-');
        return `${year}-${month}-${day}`;
      }
    }

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return '';
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  }

  private normalizeWeight(value: string): number | null {
    if (!value) return null;
    const cleaned = value.replace(',', '.').replace(/[^\d.]/g, '');
    const parsed = Number(cleaned);
    return Number.isFinite(parsed) ? parsed : null;
  }

  private mapSexoToApi(value: string): string {
    const normalized = (value || '').toLowerCase();
    if (normalized === 'macho') return 'macho';
    if (normalized === 'hembra') return 'hembra';
    return 'macho';
  }

  private mapSexoToUi(value?: string): string {
    if (!value) return '';
    const normalized = value.toLowerCase();
    if (normalized === 'macho') return 'Macho';
    if (normalized === 'hembra') return 'Hembra';
    return value;
  }

  private mapTipoConsultaToApi(value: string): string {
    switch (value) {
      case 'Consulta':
        return 'Consulta general';
      case 'Urgencia':
        return 'Emergencia';
      case 'Cirugía':
      case 'Cirugia':
        return 'Cirugía';
      case 'Revisión':
      case 'Revision':
        return 'Revisión';
      case 'Vacunación':
      case 'Vacunacion':
        return 'Vacunación';
      default:
        return value || 'Consulta general';
    }
  }

  //Obtener la fecha actual en formato yyyy-mm-dd
  private getTodayDate(): string {
    return new Date().toISOString().split('T')[0];
  }
}
