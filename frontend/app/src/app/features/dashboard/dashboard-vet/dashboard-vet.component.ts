import { Component, OnInit, ChangeDetectionStrategy } from '@angular/core';
import { Router } from '@angular/router';
import { CommonModule } from '@angular/common';
import { LucideAngularModule } from 'lucide-angular';
import { DashboardService } from '../../../core/services/dashboard.service';
import { AppointmentService} from '../../../core/services/appointment.service';
import { AlertService} from '../../../core/services/alert.service';
import Swal from 'sweetalert2';

interface Cita {
  id: number;
  hora: string;
  mascota: string;
  propietario: string;
  tipo: string;
}

interface Notificacion {
  id: number;
  title: string;
  message: string;
  prioridad: 'warning' | 'urgente' | 'info';
  tipo: 'cita' | 'tratamiento';
}

@Component({
  selector: 'app-dashboard-vet',
  templateUrl: './dashboard-vet.component.html',
  standalone: true,
  changeDetection: ChangeDetectionStrategy.Eager,
  imports: [CommonModule, LucideAngularModule]
})
export class DashboardVetComponent implements OnInit {
  constructor(
    private router: Router,
    private dashboardService: DashboardService,
    private appointmentService: AppointmentService,
    private alertService: AlertService
  ) {}

  onNavigate(page: string) {
    void this.router.navigate([`/${page}`]);
  }

  todayLabel = new Date().toLocaleDateString('es-ES', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  });

  citasHoy: Cita[] = [];
  notificaciones: Notificacion[] = [];

  ngOnInit(): void {
    this.loadDashboard();
  }

  private loadDashboard(): void {
    this.dashboardService.getAppointments().subscribe({
      next: (appointments) => {
        this.citasHoy = appointments.map((appointment) => ({
          id: appointment.id,
          hora: this.formatTime(appointment.time),
          mascota: appointment.petName,
          propietario: appointment.clientName,
          tipo: appointment.appointmentType
        }));
      },
      error: (err) => console.error('Error al cargar citas del dashboard', err)
    });

    this.dashboardService.getNotifications().subscribe({
      next: (notifications) => {
        this.notificaciones = notifications.map((notification) => ({
          id: notification.id,
          title: notification.title,
          message: notification.message,
          prioridad: this.mapPriority(notification.priority),
          tipo: this.mapType(notification.type)
        }));
      },
      error: (err) => console.error('Error al cargar notificaciones del dashboard', err)
    });
  }

  // Manejar eventos de notificaciones de citas para aceptar o cancelar
  async onCitaNotificationClick(notificationId: number): Promise<void> {

    this.appointmentService.getAppointmentById(notificationId).subscribe({
      next: async (response) => {

        const appointment = response.data;

        const { fecha, hora } = this.formatDateAndTime(appointment.start);

        const result = await Swal.fire({
          title: 'Solicitud de cita',
          html: `
          <div class="text-center text-sm leading-relaxed">
            <h3>${fecha} a las ${hora}</h3>
            <hr class="my-2">
            <ul class="text-left space-y-1 pl-10">
              <li><strong>Mascota:</strong> ${appointment.extendedProps.petName ?? '-'}</li>
              <li><strong>Propietario:</strong> ${appointment.extendedProps.clientName ?? '-'}</li>
              <li><strong>Tipo de cita:</strong> ${appointment.extendedProps.appointmentType ?? '-'}</li>
              <li><strong>Motivo:</strong> ${appointment.extendedProps.appointmentReason ?? '-'}</li>
            </ul>
          </div>
        `,
          icon: 'question',
          showCancelButton: true,
          showDenyButton: true,
          confirmButtonText: 'Confirmar',
          denyButtonText: 'Rechazar',
          cancelButtonText: 'Cerrar',
          confirmButtonColor: '#28a745',
          denyButtonColor: '#dc3545',
          cancelButtonColor: '#6c757d'
        });

        if (result.isConfirmed) {
          this.confirmAppointment(notificationId);
        } else if (result.isDenied) {
          this.cancelAppointment(notificationId);
        }
      },
      error: () => {
        this.alertService.close();
        this.alertService.error(
          'Error',
          'No se pudo cargar la información de la cita'
        );
      }
    });
  }

  // Confirmar una cita
  private confirmAppointment(id: number): void {
    this.alertService.loading('Confirmando cita...');

    this.appointmentService.confirmAdminAppointment(id).subscribe({
      next: () => {
        this.alertService.close();
        void this.alertService.success('Cita confirmada');
        this.loadDashboard(); // Refresca citas y notificaciones
      },
      error: () => {
        this.alertService.close();
        this.alertService.error(
          'Error',
          'No se pudo confirmar la cita'
        );
      }
    });
  }

  // Cancelar una cita
  private cancelAppointment(id: number): void {
    this.alertService.loading('Cancelando cita...');

    this.appointmentService.cancelAdminAppointment(id).subscribe({
      next: () => {
        this.alertService.close();
        void this.alertService.success('Cita cancelada');
        this.loadDashboard();
      },
      error: () => {
        this.alertService.close();
        this.alertService.error(
          'Error',
          'No se pudo cancelar la cita'
        );
      }
    });
  }



  private formatTime(value: string): string {
    if (!value) return '';
    return value.slice(0, 5);
  }

  /**
   * Formatea una fecha ISO a fecha en español y hora HH:mm
   */
  private formatDateAndTime(isoDate: string): { fecha: string; hora: string } {
    const date = new Date(isoDate); // Convierte ISO string a Date

    return {
      fecha: date.toLocaleDateString('es-ES', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric'
      }),
      hora: date.toLocaleTimeString('es-ES', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false
      })
    };
  }

  private mapPriority(priority: string): 'warning' | 'urgente' | 'info' {
    const normalized = (priority || '').toLowerCase();
    if (normalized === 'high' || normalized === 'urgent' || normalized === 'urgente') return 'urgente';
    if (normalized === 'medium' || normalized === 'warning') return 'warning';
    return 'info';
  }

  private mapType(type: string): 'cita' | 'tratamiento' {
    const normalized = (type || '').toLowerCase();
    if (normalized === 'appointment' || normalized === 'cita') return 'cita';
    if (normalized === 'treatment' || normalized === 'tratamiento') return 'tratamiento';
    return 'cita';
  }
}
