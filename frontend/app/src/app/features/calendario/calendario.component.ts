import { CommonModule } from '@angular/common';
import {Component, EventEmitter, Input, Output, OnInit, inject, signal, ViewChild, HostListener, ChangeDetectionStrategy} from '@angular/core';
import { FormsModule, NgForm } from '@angular/forms';
import { Router } from '@angular/router';

import { FullCalendarComponent,FullCalendarModule } from '@fullcalendar/angular';
import interactionPlugin from '@fullcalendar/interaction';
import timeGridPlugin from '@fullcalendar/timegrid';
import dayGridPlugin from '@fullcalendar/daygrid';
import { CalendarOptions, DateSelectArg, EventClickArg, EventInput } from '@fullcalendar/core';
import esLocale from '@fullcalendar/core/locales/es';

import { LucideDynamicIcon, LucideArrowLeft as ArrowLeft, LucidePlus as Plus } from '@lucide/angular';
import { AppointmentService } from '../../core/services/appointment.service';
import { UserService } from '../../core/services/user.service';
import { User } from '../../core/models/user.model';
import {ClientService} from '../../core/services/client.service';
import { PetService } from '../../core/services/pet.service';
import { AlertService } from '../../core/services/alert.service';

type TipoCita = 'Revisión' | 'Vacuna' | 'Consulta' | 'Cirugía' | 'Desparasitación';
type ViewPort = 'mobile' | 'tablet' | 'desktop';

@Component({
  selector: 'app-calendario',
  standalone: true,
  imports: [CommonModule, FormsModule, FullCalendarModule, LucideDynamicIcon],
  changeDetection: ChangeDetectionStrategy.Eager,
  templateUrl: './calendario.component.html',
})

export class CalendarioComponent implements OnInit {
  // Si quieres botón volver
  @Input() showBackButton = true;
  @Input() backTarget = 'dashboard-vet';
  @Output() navigate = new EventEmitter<string>();
  @ViewChild('calendar') calendarComponent!: FullCalendarComponent;

  // Lucide icons
  readonly ArrowLeft = ArrowLeft;
  readonly Plus = Plus;

  private appointmentService = inject(AppointmentService);
  private userService = inject(UserService);
  private clientService = inject(ClientService);
  private petService = inject(PetService);
  private alertService = inject(AlertService);
  private router = inject(Router);

  // Signal para los eventos que leerá FullCalendar
  events = signal<EventInput[]>([]);
  tiposDeCita = signal<any[]>([]);
  propietarios = signal<any[]>([]); // Lista para el buscador
  propietariosFiltrados = signal<any[]>([]); //Propietarios filtrados
  mascotasDelPropietario = signal<any[]>([]); // Se llena al elegir pr
  veterinarios = signal<User[]>([]); // Lista de vets
  clientes = signal<any[]>([]); // Lista de clientes
  private todasLasMascotas = signal<any[]>([]); // Todas las mascotas cargadas
  availableDates: string[] = [];
  availableSlots: any[] = [];

  dialogOpen = false;
  isEditing = false;
  searchTermPropietario: string = '';
  showDropdown = false;

  // Form (ngModel)
  form = {
    id: '',
    propietarioId: null as number | null,
    mascotaId: null as number | null,
    vetId: null as number | null,
    appointmentTypeId: null as number | null,
    appointmentSlotId: null as number | null,
    fecha: '',
    hora: '',
    duracionMin: 30,
    notas: '',
  };


  calendarOptions: CalendarOptions = {
    plugins: [timeGridPlugin, dayGridPlugin, interactionPlugin],
    initialView: 'timeGridWeek',
    locale: esLocale,
    firstDay: 1,
    nowIndicator: true,
    selectable: true,
    selectMirror: true,
    allDaySlot: false,
    //timeZone: 'UTC', //España UTC+1 en verano UTC+2
    height: 1000,
    expandRows: true,
    slotEventOverlap: false,
    eventMinHeight: 30,
    handleWindowResize: true,
    dayMaxEventRows: true,
    //eventMinHeight: 40,
    selectConstraint: 'businessHours',
    eventConstraint: 'businessHours',

    eventTimeFormat: {
      hour: '2-digit',
      minute: '2-digit',
      hour12: false,
    },

    // Ajusta rango de horas de la clínica abierta
    slotMinTime: '09:00:00',
    slotMaxTime: '21:00:00',
    slotDuration: '00:30:00',
      businessHours: [
    // Lunes a viernes mañana
        {
          daysOfWeek: [1, 2, 3, 4, 5, 6],
          startTime: '09:00',
          endTime: '14:00',
        },
        // Lunes a viernes tarde
        {
          daysOfWeek: [1, 2, 3, 4, 5],
          startTime: '16:00',
          endTime: '21:00',
        }
      ],

    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'timeGridWeek,timeGridDay,dayGridMonth',
    },

    eventDidMount: (info) => {
      // Tooltip nativo con el título completo del evento
      info.el.setAttribute('title', info.event.title);
    },

    // Vinculamos los eventos a la función de carga
    events: (info, successCallback, failureCallback) => {
      this.appointmentService.getAdminCalendar(info.startStr, info.endStr).subscribe({
      next: (data) => {
        const mappedEvents = data.map(cita => {
          const startLocal = cita.start.replace('Z', '').split('+')[0];
          const endLocal = cita.end.replace('Z', '').split('+')[0];
          const colores = this.getTipoColors(cita.extendedProps.appointmentType);

          return {
            id: cita.id.toString(),
            title: cita.title,
            start: startLocal,
            end: endLocal,
            backgroundColor: colores.bg,
            borderColor: colores.border,
            textColor: colores.text,
            extendedProps: {
              // Guardamos lo que el back SI nos da
              appointmentSlotId: cita.extendedProps.appointmentSlotId,
              tipo: cita.extendedProps.appointmentType,
              petName: cita.extendedProps.petName,
              clientName: cita.extendedProps.clientName,
              status: cita.extendedProps.status,
              veterinarianName: cita.extendedProps.veterinarianName,
              notas: cita.extendedProps.appointmentReason,
            }
          };
        });
        successCallback(mappedEvents);
      },

        error: (err) => failureCallback(err)
      });
    },

    select: (arg) => this.onSelectSlot(arg),
    eventClick: (arg) => this.onEventClick(arg),
  };

  // --- Handlers ---
  onBack(): void {
    void this.router.navigate([this.backTarget]);
  }

  ngOnInit() {

    this.loadTypes();
    this.loadVets();
    this.loadClients();
    this.petService.getPets().subscribe(data => this.todasLasMascotas.set(data));
    this.cargarFechasDisponibles();

    // Ajustar vista inicial según dispositivo
    setTimeout(() => {
      this.updateCalendarView();
    });
  }

  @HostListener('window:resize')
  onResize(): void {
    this.updateCalendarView();
  }

  loadTypes() {
    this.appointmentService.getAppointmentTypes().subscribe(data => {
      this.tiposDeCita.set(data);
    });
  }

  loadVets() {
    this.userService.getVeterinarians().subscribe(vets => {
      this.veterinarios.set(vets);
    });
  }

  openNewDialog(): void {
    this.isEditing = false;
    this.resetForm();
    this.dialogOpen = true;
    //Nuevo
    this.showDropdown = false;
    this.propietariosFiltrados.set(this.propietarios());
    this.searchTermPropietario = '';

  }

  closeDialog(): void {
    this.dialogOpen = false;
    this.showDropdown = false;
    this.propietariosFiltrados.set(this.propietarios());
  }


  onSelectSlot(arg: DateSelectArg): void {
    this.isEditing = false;
    this.resetForm();
    console.log("Propietario ID:", this.form.propietarioId);
    this.searchTermPropietario = '';
    // arg.startStr suele venir como "2026-01-08T09:00:00+01:00"
    // Nos quedamos solo con la parte local
    const fechaCompleta = arg.startStr.split('T');
    const fecha = fechaCompleta[0]; // "2026-01-08"
    const hora = fechaCompleta[1].substring(0, 5); // "09:00"

    this.form.fecha = fecha;
    this.form.hora = hora;

    // 2. Llamada al servicio con la fecha
    this.appointmentService.getAvailableSlots(fecha).subscribe({
      next: (slots) => {
        // Buscamos el slot donde el startTime coincida exactamente con la hora seleccionada
        const slotEncontrado = slots.find(s => s.startTime === hora);

        if (slotEncontrado) {
          this.form.appointmentSlotId = slotEncontrado.id;
          console.log(`Slot asignado: ${slotEncontrado.id} para las ${hora}`);
        } else {
          console.error(`No hay slot disponible para la hora: ${hora}`);
          this.form.appointmentSlotId = null;
        }
      },
      error: (err) => console.error("Error al obtener slots", err)
    });

    this.dialogOpen = true;
  }


  onEventClick(arg: EventClickArg): void {
    this.isEditing = true;
    const props = arg.event.extendedProps;


    // 1. Buscar IDs por nombre (Parche porque el back no manda IDs)
    //Posible mejora: buscar por ID en lugar de por nombre completo
    const propEncontrado = this.propietarios().find(p =>
      (p.firstName + ' ' + p.lastName) === props['clientName']
    );

    this.searchTermPropietario = propEncontrado
      ? `${propEncontrado.firstName} ${propEncontrado.lastName}`
      : (props['clientName'] || '');

    console.log("Propietario encontrado:", propEncontrado);


    console.log("Buscando a:", props['veterinarianName']);
    console.log("Lista de veterinarios disponibles:", this.veterinarios().map(v => `${v.firstName} ${v.lastName}`));

    const vetEncontrado = this.veterinarios().find(v =>
      `${v.firstName} ${v.lastName}`.toLowerCase().includes(props['veterinarianName']?.toLowerCase())
    );
    console.log("Vet encontrado:", vetEncontrado);

    const tipoEncontrado = this.tiposDeCita().find(t =>
      t.name === props['tipo']
    );

    // 2. Cargar el formulario
    this.form = {
      id: arg.event.id,
      propietarioId: propEncontrado ? propEncontrado.id : null,
      mascotaId: null, // Lo buscaremos tras cargar las mascotas del dueño
      vetId: vetEncontrado ? vetEncontrado.id : null,
      appointmentTypeId: tipoEncontrado ? tipoEncontrado.id : null,
      appointmentSlotId: props['appointmentSlotId'], // Ya viene en el JSON
      fecha: arg.event.startStr.split('T')[0],
      hora: arg.event.startStr.split('T')[1].substring(0, 5),
      duracionMin: 30,
      notas: props['notas'] || ''
    };

    // 3. Cargar mascotas del propietario y seleccionar la correcta por nombre
    if (this.form.propietarioId) {
      this.onPropietarioChange(this.form.propietarioId.toString());

      // Esperamos a que se llene la señal de mascotas para buscar el ID de la mascota
      setTimeout(() => {
        const mascota = this.mascotasDelPropietario().find(m => m.name === props['petName']);
        if (mascota) this.form.mascotaId = mascota.id;
      }, 400);
    }

    this.dialogOpen = true;
  }


  // 1. Cargar propietarios al iniciar o al escribir
  loadClients(): void {
    this.clientService.getClients().subscribe((clients: any[]) => {
      this.clientes.set(clients);
      this.propietarios.set(clients); // inicialmente todos
    });
  }


  onSearchInput(): void {
    const value = this.searchTermPropietario?.trim().toLowerCase() || '';
    this.showDropdown = true;

    this.propietariosFiltrados.set(
      this.propietarios().filter(p =>
        `${p.firstName} ${p.lastName}`.toLowerCase().includes(value)
      )
    );

    const encontrado = this.propietarios().find(
      p => `${p.firstName} ${p.lastName}`.toLowerCase() === value
    );

    if (encontrado) {
      this.form.propietarioId = encontrado.id;
      this.onPropietarioChange(encontrado.id.toString());
      this.showDropdown = false;
    } else {
      this.form.propietarioId = null;
      this.mascotasDelPropietario.set([]);
    }
  }

  selectPropietario(p: any): void {
    this.searchTermPropietario = `${p.firstName} ${p.lastName}`;
    this.form.propietarioId = p.id;
    this.onPropietarioChange(p.id.toString());
    this.showDropdown = false;
  }

  // 2. Al seleccionar un propietario, cargar sus mascotas
onPropietarioChange(propietarioId: string): void {
  const idBuscado = Number(propietarioId);
  const filtradas = this.todasLasMascotas().filter(p => p.client?.id === idBuscado);
  this.mascotasDelPropietario.set(filtradas);
}

  // Cargar fechas disponibles desde el backend
  cargarFechasDisponibles(): void {
    this.appointmentService.getAvailableDates().subscribe({
      next: (dates) => this.availableDates = dates,
      error: (err) => console.error("Error cargando fechas", err)
    });
  }

  // Se ejecuta cuando el usuario cambia el Select de Fecha
  onFechaChange(): void {
    this.form.hora = ''; // Resetear hora al cambiar fecha
    this.form.appointmentSlotId = null;
    this.availableSlots = [];

    if (this.form.fecha) {
      this.appointmentService.getAvailableSlots(this.form.fecha).subscribe({
        next: (slots) => {
          this.availableSlots = slots;
        },
        error: (err) => console.error("Error al buscar slots", err)
      });
    }
  }

  // Se ejecuta cuando el usuario elige una Hora específica
  onHoraChange(): void {
    // Ahora el valor del select será directamente el ID del slot
    console.log("Slot asignado:", this.form.appointmentSlotId);
  }


  saveCita(formRef: NgForm): void {
    if (formRef.invalid) return;

      // Si no hay slot ID, podrías mostrar un error o impedir el guardado
    if (!this.form.appointmentSlotId) {
      this.alertService.error("Error", "No se ha podido asignar un ID de horario válido para esta cita.");
      return;
    }

    // 1. Preparamos el payload exacto para el Backend
    // Nota: Debes asegurarte de que form.mascota, form.veterinario y form.tipo
    // contengan los IDs numéricos o mapearlos antes de enviar.
    const payload = {
      petId: Number (this.form.mascotaId),
      vetId: Number (this.form.vetId),
      appointmentTypeId: Number (this.form.appointmentTypeId),
      appointmentSlotId: Number (this.form.appointmentSlotId),
      reason: this.form.notas || 'Cita programada desde calendario'
    };

    console.log('Enviando payload:', payload);

    const request = this.isEditing
      ? this.appointmentService.updateAppointmentById(this.form.id, payload)
      : this.appointmentService.createAppointment(payload);

    request.subscribe({
      next: () => {
        this.refreshCalendar();
        this.closeDialog();
        //this.resetForm();
      },
      error: (err) => console.error("Error al procesar cita", err)
    });
  }

  async deleteCita(): Promise<void> {
    if (!this.form.id) return;

    const confirmed = await this.alertService.confirm('Confirmación', '¿Seguro que quieres cancelar esta cita?');

    if (confirmed) {
      this.appointmentService.cancelAdminAppointment(this.form.id).subscribe({
        next: () => {
          this.refreshCalendar();
          this.closeDialog();
        }
      });
    }
  }

  // --- Helpers ---

  private refreshCalendar(): void {
    if (this.calendarComponent && this.calendarComponent.getApi()) {
      // Esto limpia el calendario y vuelve a llamar a la función 'events' de calendarOptions
      this.calendarComponent.getApi().refetchEvents();
    } else {
      // Si por algún motivo la API no está lista, forzamos un cambio de referencia en las opciones
      this.calendarOptions = { ...this.calendarOptions };
    }
  }


  private getTipoColors(tipo: TipoCita) {
    switch (tipo) {
      case 'Revisión':
        return { bg: '#DBEAFE', border: '#93C5FD', text: '#1D4ED8' }; // blue
      case 'Vacuna':
        return { bg: '#DCFCE7', border: '#86EFAC', text: '#15803D' }; // green
      case 'Consulta':
        return { bg: '#EDE9FE', border: '#C4B5FD', text: '#6D28D9' }; // purple
      case 'Cirugía':
        return { bg: '#FEE2E2', border: '#FCA5A5', text: '#B91C1C' }; // red
      case 'Desparasitación':
        return { bg: '#FEF9C3', border: '#FDE68A', text: '#A16207' }; // yellow
      default:
        return { bg: '#F3F4F6', border: '#D1D5DB', text: '#374151' };
    }
  }

  private resetForm(): void {
    this.form = {
      id: '',
      propietarioId: null,
      mascotaId: null,
      vetId: null,
      appointmentTypeId: null,
      fecha: '',
      hora: '',
      duracionMin: 30,
      notas: '',
      appointmentSlotId: null,
    };
  }

  private getViewPort(width: number) : ViewPort {
    if (width >= 1024) return 'desktop'; //lg
    if (width >= 768) return 'tablet'; //md
    return 'mobile';
  }

  private getCalendarView(viewPort: ViewPort): string {
    switch (viewPort) {
      case 'mobile':
        return 'timeGridDay';
      case 'tablet':
        return 'timeGridWeek';
      case 'desktop':
        return 'dayGridMonth';
    }
  }

  private currentViewport: ViewPort | null = null;

  private updateCalendarView(): void {
    if (!this.calendarComponent) return;

    const viewport = this.getViewPort(window.innerWidth);
    if (viewport === this.currentViewport) return;

    this.currentViewport = viewport;

    const api = this.calendarComponent.getApi();

    api.changeView(this.getCalendarView(viewport));
    api.setOption('titleFormat', this.getTitleFormat(viewport));
  }

  private getTitleFormat(viewport: ViewPort): Intl.DateTimeFormatOptions {
    switch (viewport) {
      case 'mobile':
        return {
          weekday: 'short',
          day: 'numeric',
        };

      case 'tablet':
        return {
          day: 'numeric',
          month: 'long',
        };

      case 'desktop':
        return {
          month: 'long',
          year: 'numeric',
        };
    }
  }



}

