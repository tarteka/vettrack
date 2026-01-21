import { CommonModule } from '@angular/common';
import { Component, EventEmitter, OnInit, Output } from '@angular/core';
import { LucideAngularModule, LucideIconProvider, LUCIDE_ICONS, FileText, Download, ArrowLeft } from 'lucide-angular';
import { InvoiceApi, InvoiceLineItem, InvoiceService } from '../../core/services/invoice.service';
import { Router } from '@angular/router';

type EstadoFactura = 'todas' | 'pendiente' | 'pagada' | 'atrasada' | 'cancelada';

type Factura = {
  id: number;
  numero: string;
  fecha: string;
  concepto: string;
  servicios: string[];
  subtotal: number;
  iva: number;
  total: number;
  estado: 'pendiente' | 'pagada' | 'atrasada' | 'cancelada';
  metodoPago: string | null;
};

@Component({
  selector: 'app-mis-facturas',
  standalone: true,
  imports: [
    CommonModule,
    LucideAngularModule,
  ],
  providers: [
    {
      provide: LUCIDE_ICONS,
      multi: true,
      useValue: new LucideIconProvider({ FileText, Download, ArrowLeft })
    }
  ],
  templateUrl: './mis-facturas.component.html',
})
export class MisFacturasComponent implements OnInit {

  // icons
  readonly FileText = FileText;
  readonly Download = Download;
  readonly ArrowLeft = ArrowLeft;

  filterEstado: EstadoFactura = 'todas';

  facturas: Factura[] = [];
  loading = false;
  errorMessage = '';

  constructor(
    private invoiceService: InvoiceService,
    private router: Router
  ) {}

  ngOnInit(): void {
    this.loadFacturas();
  }

  onNavigate(page: string) {
    this.router.navigate([`/${page}`]);
  }

  setFilter(estado: EstadoFactura) {
    this.filterEstado = estado;
  }

  get filteredFacturas(): Factura[] {
    return this.facturas.filter(
      (f) => this.filterEstado === 'todas' || f.estado === this.filterEstado
    );
  }

  get totalPendiente(): number {
    return this.facturas
      .filter((f) => f.estado === 'pendiente')
      .reduce((sum, f) => sum + f.total, 0);
  }

  get totalPagado(): number {
    return this.facturas
      .filter((f) => f.estado === 'pagada')
      .reduce((sum, f) => sum + f.total, 0);
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
      error: (err) => {
        console.error('Error al descargar PDF', err);
      }
    });
  }

  private loadFacturas(): void {
    this.loading = true;
    this.errorMessage = '';
    this.invoiceService.getMyInvoices().subscribe({
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
      concepto: invoice.concept || invoice.description || itemNames.join(', ') || `Factura ${invoice.invoiceNumber || invoice.id}`,
      servicios: itemNames.length > 0 ? itemNames : this.normalizeServicios(invoice.services),
      subtotal: resolvedSubtotal,
      iva: resolvedIva,
      total: total ?? resolvedSubtotal + resolvedIva,
      estado: this.mapEstado(invoice.status),
      metodoPago: invoice.paymentMethod ?? invoice.method ?? null
    };
  }

  private getInvoiceItemNames(invoice: InvoiceApi): string[] {
    if (invoice.invoiceItems && invoice.invoiceItems.length > 0) {
      return invoice.invoiceItems.map((item) => item.serviceName || 'Servicio');
    }
    return [];
  }

  private normalizeServicios(services?: Array<string | InvoiceLineItem> | null): string[] {
    if (!services || services.length === 0) return [];
    return services.map((service) => {
      if (typeof service === 'string') return service;
      return service.name || service.description || 'Servicio';
    });
  }

  private mapEstado(status?: string | null): 'pendiente' | 'pagada' | 'atrasada' | 'cancelada' {
    const normalized = (status || '').toLowerCase();
    if (normalized === 'paid' || normalized === 'pagada' || normalized === 'pagado') return 'pagada';
    if (normalized === 'overdue' || normalized === 'atrasada') return 'atrasada';
    if (normalized === 'cancelled' || normalized === 'canceled' || normalized === 'cancelada') return 'cancelada';
    return 'pendiente';
  }

  badgeEstadoClasses(estado: 'pendiente' | 'pagada' | 'atrasada' | 'cancelada'): string {
    if (estado === 'pagada') return 'bg-green-100 text-green-700';
    if (estado === 'atrasada') return 'bg-amber-100 text-amber-700';
    if (estado === 'cancelada') return 'bg-gray-200 text-gray-700';
    return 'bg-red-100 text-red-700';
  }

  estadoLabel(estado: 'pendiente' | 'pagada' | 'atrasada' | 'cancelada'): string {
    if (estado === 'pagada') return 'Pagada';
    if (estado === 'atrasada') return 'Atrasada';
    if (estado === 'cancelada') return 'Cancelada';
    return 'Pendiente';
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
}
