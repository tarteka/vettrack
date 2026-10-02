import { Component, EventEmitter, Input, OnInit, Output, inject, ChangeDetectionStrategy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { LucideDynamicIcon } from '@lucide/angular';
import { AuthService } from '../../../core/services/auth.service';
import { DashboardService } from '../../../core/services/dashboard.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-dashboard-client',
  standalone: true,
  imports: [CommonModule, LucideDynamicIcon],
  changeDetection: ChangeDetectionStrategy.Eager,
  templateUrl: './dashboard-client.component.html'
})
export class DashboardClientComponent implements OnInit {
  @Input() userName: string = '';
  @Output() onNavigate = new EventEmitter<{ page: string; data?: any }>();

  mascotas: Array<{ id: number; nombre: string; especie: string; raza: string; propietario?: string; telefono?: string }> = [];
  proximasCitas: Array<{ id: number; mascota: string; fecha: string; hora: string; tipo: string }> = [];
  facturasPendientes: Array<{ id: number; fecha: string; concepto: string; importe: number; estado: string }> = [];
  private router = inject(Router);

  constructor(
    private dashboardService: DashboardService,
    private authService: AuthService
  ) {}

  ngOnInit(): void {
    const user = this.authService.getUserData();
    if (!this.userName && user) {
      const name = `${user.firstName || ''} ${user.lastName || ''}`.trim();
      this.userName = name || user.username || '';
    }

    this.loadDashboard();
  }

  navigate(page: string, mascota?: any) {
    if (page === 'ficha-mascota' && mascota) {
      this.router.navigate(['/mascotas/ficha', mascota.id]);
    } else {
      this.router.navigate([`/${page}`]);
    }
  }

  private loadDashboard(): void {
    this.dashboardService.getPets().subscribe({
      next: (pets) => {
        this.mascotas = pets.map((pet) => ({
          id: pet.id,
          nombre: pet.name,
          especie: pet.petType,
          raza: pet.breed || ''
        }));
      },
      error: (err) => console.error('Error al cargar mascotas del dashboard', err)
    });

    this.dashboardService.getAppointments().subscribe({
      next: (appointments) => {
        this.proximasCitas = appointments.map((appointment) => ({
          id: appointment.id,
          mascota: appointment.petName,
          fecha: this.formatDate(appointment.date),
          hora: this.formatTime(appointment.time),
          tipo: appointment.appointmentType
        }));
      },
      error: (err) => console.error('Error al cargar citas del dashboard', err)
    });

    this.dashboardService.getInvoices().subscribe({
      next: (invoices) => {
        this.facturasPendientes = invoices.map((invoice) => ({
          id: invoice.id,
          fecha: this.formatDate(invoice.invoiceDate),
          concepto: invoice.invoiceNumber,
          importe: invoice.totalAmount,
          estado: this.mapInvoiceStatus(invoice.status)
        }));
      },
      error: (err) => console.error('Error al cargar facturas del dashboard', err)
    });
  }

  private formatDate(value?: string | null): string {
    if (!value) return '';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return date.toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' });
  }

  private formatTime(value: string): string {
    if (!value) return '';
    return value.slice(0, 5);
  }

  private mapInvoiceStatus(value: string): string {
    const normalized = (value || '').toLowerCase();
    if (normalized === 'pending' || normalized === 'pendiente') return 'Pendiente';
    if (normalized === 'paid' || normalized === 'pagada' || normalized === 'pagado') return 'Pagada';
    return value || '';
  }
}
