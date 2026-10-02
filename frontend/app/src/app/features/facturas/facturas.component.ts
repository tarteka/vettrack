import { CommonModule } from '@angular/common';
import { Component, EventEmitter, Input, OnInit, Output, ChangeDetectionStrategy } from '@angular/core';
import { Router } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { LucideAngularModule } from 'lucide-angular';
import {
  Search,
  Plus,
  Edit,
  Trash2,
  Eye,
  FileText,
  X,
  Download,
  CheckCircle,
  Ban,
  Mail
} from 'lucide-angular';
import { InvoiceApi, InvoiceService } from '../../core/services/invoice.service';
import { AlertService } from '../../core/services/alert.service';
import Swal from 'sweetalert2';

type EstadoFactura = 'pagada' | 'pendiente' | 'atrasada' | 'cancelada';
type FiltroEstado = 'todas' | EstadoFactura;

interface Factura {
  id: number;
  numero: string;
  fecha: string;
  cliente: string;
  mascota: string;
  concepto: string;
  subtotal: number;
  iva: number;
  total: number;
  estado: EstadoFactura;
  metodoPago?: string;
}

@Component({
  selector: 'app-facturas',
  standalone: true,
  imports: [CommonModule, FormsModule, LucideAngularModule],
  changeDetection: ChangeDetectionStrategy.Eager,
  templateUrl: './facturas.component.html',
})
export class FacturasComponent implements OnInit {
  @Input() onNavigate?: (page: string) => void;

  @Output() navigate = new EventEmitter<string>();

  readonly icons = {
    Search,
    Plus,
    Edit,
    Trash2,
    Eye,
    FileText,
    X,
    Download,
    CheckCircle,
    Ban,
    Mail
  };

  searchTerm = '';
  filterEstado: FiltroEstado = 'todas';

  dialogOpen = false;
  facturaSeleccionada: Factura | null = null;

  facturas: Factura[] = [];
  loading = false;
  errorMessage = '';

  constructor(
    private invoiceService: InvoiceService,
    private alertService: AlertService,
    private router: Router
  ) {}

  ngOnInit(): void {
    this.loadFacturas();
  }

  get filteredFacturas(): Factura[] {
    const term = this.searchTerm.trim().toLowerCase();
    return this.facturas.filter((f) => {
      const matchSearch =
        f.numero.toLowerCase().includes(term) ||
        f.cliente.toLowerCase().includes(term) ||
        f.mascota.toLowerCase().includes(term);

      const matchEstado =
        this.filterEstado === 'todas' || f.estado === this.filterEstado;

      return (term.length === 0 ? true : matchSearch) && matchEstado;
    });
  }

  navigateTo(page: string) {
    if (this.onNavigate) {
      this.onNavigate(page);
      return;
    }
    if (this.navigate.observers.length > 0) {
      this.navigate.emit(page);
      return;
    }
    void this.router.navigate([`/${page}`]);
  }

  setFiltro(estado: FiltroEstado) {
    this.filterEstado = estado;
  }


  handleVerDetalles(factura: Factura) {
    this.facturaSeleccionada = factura;
    this.dialogOpen = true;
  }

  closeDialog() {
    this.dialogOpen = false;
  }

  downloadPdf(factura: Factura) {
    this.invoiceService.downloadInvoicePdf(factura.id).subscribe({
      next: (blob) => {
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `factura-${factura.numero || factura.id}.pdf`;
        link.click();
        URL.revokeObjectURL(url);
      },
      error: (err) => console.error('Error al descargar PDF', err)
    });
  }

  sendingEmail = false;

  handleEnviarEmail(factura: Factura) {
    if (!factura) return;
    this.sendingEmail = true;
    this.invoiceService.sendInvoicePdf(factura.id).subscribe({
      next: () => {
        this.sendingEmail = false;
        void this.alertService.success('Enviado', 'La factura se ha enviado por email correctamente.');
      },
      error: (err) => {
        this.sendingEmail = false;
        console.error('Error al enviar factura por email', err);
        this.alertService.error('Error', 'No se pudo enviar la factura por email.');
      }
    });
  }

  private loadFacturas(): void {
    this.loading = true;
    this.errorMessage = '';
    this.invoiceService.getInvoices().subscribe({
      next: (invoices) => {
        this.facturas = invoices.map((invoice) => this.mapInvoiceToFactura(invoice));
        this.loading = false;
      },
      error: (err) => {
        console.error('Error al cargar facturas', err);
        this.errorMessage = 'No se pudieron cargar las facturas.';
        this.loading = false;
      }
    });
  }

  private mapInvoiceToFactura(invoice: InvoiceApi): Factura {
    const subtotal = this.toNumber(invoice.subtotal);
    const iva = this.toNumber(invoice.taxAmount ?? invoice.tax);
    const total = this.toNumber(invoice.totalAmount);
    const resolvedSubtotal = subtotal ?? (total !== null && iva !== null ? total - iva : total ?? 0);
    const resolvedIva = iva ?? (total !== null ? Math.max(total - resolvedSubtotal, 0) : 0);
    const itemNames = this.getInvoiceItemNames(invoice);

    return {
      id: invoice.id,
      numero: invoice.invoiceNumber || String(invoice.id),
      fecha: this.formatDate(invoice.invoiceDate),
      cliente: this.getClientName(invoice),
      mascota: this.getPetName(invoice),
      concepto: invoice.concept || invoice.description || itemNames.join(', ') || `Factura ${invoice.invoiceNumber || invoice.id}`,
      subtotal: resolvedSubtotal,
      iva: resolvedIva,
      total: total ?? resolvedSubtotal + resolvedIva,
      estado: this.mapEstado(invoice.status),
      metodoPago: invoice.paymentMethod ?? invoice.method ?? undefined
    };
  }

  private mapEstado(status?: string | null): EstadoFactura {
    const normalized = (status || '').toLowerCase();
    if (normalized === 'paid' || normalized === 'pagada' || normalized === 'pagado') return 'pagada';
    if (normalized === 'overdue' || normalized === 'atrasada') return 'atrasada';
    if (normalized === 'cancelled' || normalized === 'canceled' || normalized === 'cancelada') return 'cancelada';
    return 'pendiente';
  }

  async handlePagarFactura(factura: Factura) {
    const result = await Swal.fire({
      title: 'Selecciona el metodo de pago',
      input: 'select',
      inputOptions: {
        'metálico': 'Metálico',
        'tarjeta de crédito': 'Tarjeta de crédito',
        'transferencia': 'Transferencia',
        'otro': 'Otro'
      },
      inputPlaceholder: 'Selecciona un metodo',
      showCancelButton: true,
      confirmButtonText: 'Pagar',
      cancelButtonText: 'Cancelar'
    });

    if (!result.isConfirmed || !result.value) return;
    const metodoPago = result.value as string;

    this.invoiceService.payInvoice(factura.id, { paymentMethod: metodoPago }).subscribe({
      next: () => {
        factura.estado = 'pagada';
        factura.metodoPago = metodoPago;
        this.alertService.success('Factura pagada', 'La factura se ha marcado como pagada.');
      },
      error: (err) => {
        console.error('Error al pagar factura', err);
        this.alertService.error('Error', 'No se pudo pagar la factura.');
      }
    });
  }

  async handleCancelarFactura(factura: Factura) {
    const ok = await this.alertService.confirm(
      'Cancelar factura',
      'Estas seguro de que quieres cancelar esta factura?'
    );
    if (!ok) return;
    this.invoiceService.cancelInvoice(factura.id).subscribe({
      next: () => {
        factura.estado = 'cancelada';
        this.alertService.success('Factura cancelada', 'La factura se ha cancelado correctamente.');
      },
      error: (err) => {
        console.error('Error al cancelar factura', err);
        this.alertService.error('Error', 'No se pudo cancelar la factura.');
      }
    });
  }

  private getClientName(invoice: InvoiceApi): string {
    if (invoice.clientName) return invoice.clientName;
    const client = invoice.client;
    if (client?.fullName) return client.fullName;
    const first = client?.firstName || '';
    const last = client?.lastName || '';
    return `${first} ${last}`.trim() || 'Sin cliente';
  }

  private getPetName(invoice: InvoiceApi): string {
    if (invoice.petName) return invoice.petName;
    return invoice.pet?.name || 'Sin mascota';
  }

  private getInvoiceItemNames(invoice: InvoiceApi): string[] {
    if (invoice.invoiceItems && invoice.invoiceItems.length > 0) {
      return invoice.invoiceItems.map((item) => item.serviceName || 'Servicio');
    }
    if (invoice.services && invoice.services.length > 0) {
      return invoice.services.map((service) => {
        if (typeof service === 'string') return service;
        return service.name || service.description || 'Servicio';
      });
    }
    return [];
  }

  private formatDate(value?: string | null): string {
    if (!value) return '';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return date.toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' });
  }

  private toNumber(value?: number | string | null): number | null {
    if (value === null || value === undefined) return null;
    const parsed = typeof value === 'number' ? value : Number(value);
    return Number.isFinite(parsed) ? parsed : null;
  }
  getInitials(nombre: string): string {
    const parts = nombre.trim().split(/\s+/);
    const first = parts[0]?.[0] ?? '';
    const second = parts.length > 1 ? parts[1][0] : '';
    return (first + second).toUpperCase();
  }

  badgeEstadoClasses(estado: EstadoFactura): string {
    if (estado === 'pagada') return 'bg-green-100 text-green-700';
    if (estado === 'atrasada') return 'bg-amber-100 text-amber-700';
    if (estado === 'cancelada') return 'bg-gray-200 text-gray-700';
    return 'bg-red-100 text-red-700';
  }

  estadoLabel(estado: EstadoFactura): string {
    if (estado === 'pagada') return 'Pagada';
    if (estado === 'atrasada') return 'Atrasada';
    if (estado === 'cancelada') return 'Cancelada';
    return 'Pendiente';
  }

  filtroBtnClasses(active: boolean, color: 'teal' | 'red' | 'green' | 'amber' | 'gray'): string {
    if (!active) {
      return 'border border-gray-200 bg-white text-gray-700 hover:bg-gray-50';
    }
    if (color === 'teal') return 'bg-[#009688] text-white';
    if (color === 'red') return 'bg-red-600 text-white';
    if (color === 'green') return 'bg-green-600 text-white';
    if (color === 'amber') return 'bg-amber-500 text-white';
    return 'bg-gray-500 text-white';
  }
}



