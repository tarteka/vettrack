import { CommonModule } from '@angular/common';
import { Component, EventEmitter, Output, OnInit, inject, signal, ChangeDetectionStrategy } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import {
  LucideDynamicIcon,
  provideLucideIcons,
  LucideCalendar as Calendar,
  LucidePlus as Plus,
  LucideClock as Clock,
  LucideArrowLeft as ArrowLeft,
  LucideCheckCircle as CheckCircle,
  LucideInfo as Info,
  LucidePhone as Phone,
  LucideX as X
} from '@lucide/angular';
import { AppointmentService } from '../../core/services/appointment.service';
import { Appointment } from '../../core/models/appointment.model';
import { Mascota } from '../../core/models/pet-user.model';
import { ServiceCategory } from '../../core/models/service.model';
import { PetUserService } from '../../core/services/pet-user.service';
import { AlertService } from '../../core/services/alert.service';


type TabKey = 'proximas' | 'pasadas';

@Component({
  selector: 'app-mis-citas',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    LucideDynamicIcon,
  ],
  providers: [
    provideLucideIcons(Calendar, Plus, Clock, ArrowLeft, CheckCircle, Info, Phone, X)
  ],
  changeDetection: ChangeDetectionStrategy.Eager,
  templateUrl: './mis-citas.component.html',
})
export class MisCitasComponent implements OnInit {
  @Output() navigate = new EventEmitter<string>();
  private appointmentService = inject(AppointmentService);
  private petService = inject(PetUserService);
  private router = inject(Router);
  private alertService = inject(AlertService);

  // icons
  readonly Calendar = Calendar;
  readonly Plus = Plus;
  readonly Clock = Clock;
  readonly ArrowLeft = ArrowLeft;
  readonly CheckCircle = CheckCircle;
  readonly Info = Info;
  readonly Phone = Phone;
  readonly X = X;

  dialogOpen = false;

  proximasCitas = signal<Appointment[]>([]);
  citasPasadas = signal<Appointment[]>([]);
  misMascotas = signal<Mascota[]>([]);
  categoriasCita = signal<ServiceCategory[]>([]);
  tiposDeCita = signal<any[]>([]);
  availableDates = signal<string[]>([]);
  availableSlots = signal<any[]>([]);
  editingAppointment = signal<Appointment | null>(null);

  activeTab = signal<'proximas' | 'pasadas'>('proximas');

  loading = signal(false);

  ngOnInit() {
    this.loadUpcoming();
    this.loadFormData();
    this.loadHistory()
  }

  loadUpcoming() {
    this.loading.set(true);
    this.appointmentService.getUpcomingAppointments().subscribe({
      next: (data) => {
        this.proximasCitas.set(data);
        this.loading.set(false);
      },
      error: (err) => this.loading.set(false)
    });
  }

  // Formulario de solicitud de cita
  solicitudForm = {
    petId: null as number | null,
    appointmentTypeId: null as number | null,
    appointmentSlotId: null as number | null,
    date: '', // Variable temporal para el primer select
    reason: ''
  };

  onNavigate(page: string) {
    this.router.navigate([`/${page}`]);
  }

  setTab(tab: 'proximas' | 'pasadas') {
    this.activeTab.set(tab);
    if (tab === 'pasadas' && this.citasPasadas().length === 0) {
      this.loadHistory();
    }
  }

  loadFormData() {
    // 1. Cargar tipos de cita desde el nuevo endpoint
    this.appointmentService.getAppointmentTypes().subscribe(data => {
      this.tiposDeCita.set(data);
    });

    // 2. Cargar mascotas del usuario
    this.petService.getPets().subscribe(data => {
      this.misMascotas.set(data);
    });

    // Cargar fechas disponibles iniciales
    this.appointmentService.getAvailableDates().subscribe(dates => this.availableDates.set(dates));
  }

  // Se llama cada vez que el usuario cambia la fecha en el HTML
  onDateChange(newDate: string) {
    this.solicitudForm.date = newDate;
    this.solicitudForm.appointmentSlotId = null; // Resetear hora al cambiar día

    this.appointmentService.getAvailableSlots(newDate).subscribe(slots => {
      this.availableSlots.set(slots);
    });
  }

  loadHistory() {
    this.loading.set(true);
    this.appointmentService.getPastAppointments().subscribe({
      next: (data) => {
        this.citasPasadas.set(data);
        this.loading.set(false);
      },
      error: (err) => {
        console.error('Error al cargar historial', err);
        this.loading.set(false);
      }
    });
  }

  openDialog() {
    this.editingAppointment.set(null); // MUY IMPORTANTE
    this.solicitudForm = {
      petId: null,
      appointmentTypeId: null,
      appointmentSlotId: null,
      date: '',
      reason: ''
    };
    this.availableSlots.set([]); // Limpiar slots previos
    this.dialogOpen = true;
  }

  closeDialog() {
    this.dialogOpen = false;
    this.editingAppointment.set(null);
    // Reset del formulario a estado inicial
    this.solicitudForm = {
      petId: null,
      appointmentTypeId: null,
      appointmentSlotId: null,
      date: '',
      reason: ''
    };
  }

  handleSolicitar() {
    // IMPORTANTE: El back no pide "date", solo "appointmentSlotId"
    const payload = {
      petId: Number(this.solicitudForm.petId),
      appointmentTypeId: this.solicitudForm.appointmentTypeId,
      appointmentSlotId: this.solicitudForm.appointmentSlotId,
      reason: this.solicitudForm.reason
    };

    // 2. Comprobamos si estamos Editando (PATCH) o Creando (POST)
    const isEditing = !!this.editingAppointment();
    const request$ = isEditing
      ? this.appointmentService.updateAppointment(this.editingAppointment()!.id, payload)
      : this.appointmentService.requestAppointment(payload);

    // 3. Ejecución de la petición
    request$.subscribe({
      next: () => {
        void this.alertService.success(
          isEditing ? 'Cita Actualizada' : 'Cita Solicitada',
          isEditing ? 'Los cambios se han guardado correctamente.' : 'Nos pondremos en contacto pronto para confirmar.'
        );
        this.closeAndRefresh();
      },
      error: (err) => {
        console.error('Error en la operación:', err);

        const apiError = err.error?.error; 

        const message =
          apiError?.message ??
          'No hemos podido procesar tu solicitud. Inténtalo de nuevo.';

        const details = apiError?.details;

        if (details && typeof details === 'object') {
          // Convertimos los errores campo a campo en un mensaje legible
          const validationErrors = Object.values(details).join('<br>');

          this.alertService.error(
            'Error de validación',
            validationErrors
          );
        } else {
          this.alertService.error(
            'Error en la solicitud',
            message
          );
        }
      }
    });
  }


  async handleCancelarCita(cita: Appointment) {
    // 1. Pedir confirmación profesional con SweetAlert2
    const confirmed = await this.alertService.confirm(
      '¿Cancelar cita?',
      `Vas a cancelar la cita de ${cita.petName} para el día ${cita.date}.`
    );

    if (confirmed) {
      this.appointmentService.cancelAppointment(cita.id).subscribe({
        next: () => {
          // 2. Alerta de éxito tipo Toast
          this.alertService.success('Cancelada', 'La cita ha sido cancelada correctamente.');
          // 3. Refrescar la lista de señales
          this.loadUpcoming();
        },
        error: (err) => {
          this.alertService.error('Error', 'No se ha podido cancelar la cita en este momento.');
        }
      });
    }
  }

  //Dialogo de edición de cita
    openEditDialog(cita: any) {
    this.editingAppointment.set(cita);

    // Precargamos el formulario con los datos actuales
    this.solicitudForm = {
      petId: cita.petId || null, // El ID de mascota no suele venir en el listado, se puede dejar nulo para elegir
      appointmentTypeId: cita.appointmentTypeId || null, // Lo mismo aquí
      appointmentSlotId: cita.appointmentSlotId ||null,
      date: cita.date, // Para que el usuario vea la fecha actual
      reason: 'Motivo previo: ' + cita.appointmentType // O una cadena vacía
    };

    if (cita.date) {
      this.appointmentService.getAvailableSlots(cita.date).subscribe(slots => {
        this.availableSlots.set(slots);
      });
    }

    this.dialogOpen = true;
  }

  private closeAndRefresh() {
    this.closeDialog();
    this.editingAppointment.set(null);
    this.loadUpcoming();
  }

}

