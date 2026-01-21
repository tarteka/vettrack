import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, map } from 'rxjs';
import { API_BASE_URL } from './api.config';

interface ApiResponse<T> {
  success: string;
  code: number;
  data: T;
}

export interface InvoiceLineItem {
  name?: string | null;
  description?: string | null;
  quantity?: number | null;
  unitPrice?: number | null;
  serviceId?: number | null;
}

export interface InvoiceItemApi {
  id: number;
  serviceId: number;
  serviceName: string;
  quantity: number;
  unitPrice: number;
  taxRate: number;
  subTotal: number;
  taxAmount: number;
  totalAmount: number;
}

export interface InvoiceClientInfo {
  id?: number | null;
  fullName?: string | null;
  firstName?: string | null;
  lastName?: string | null;
}

export interface InvoicePetInfo {
  id?: number | null;
  name?: string | null;
}

export interface InvoiceApi {
  id: number;
  invoiceNumber?: string | null;
  invoiceDate?: string | null;
  userId?: number | null;
  petId?: number | null;
  status?: string | null;
  totalAmount?: number | null;
  subtotal?: number | null;
  taxAmount?: number | null;
  tax?: number | null;
  concept?: string | null;
  description?: string | null;
  services?: Array<string | InvoiceLineItem> | null;
  invoiceItems?: InvoiceItemApi[] | null;
  notes?: string | null;
  paymentMethod?: string | null;
  method?: string | null;
  clientName?: string | null;
  petName?: string | null;
  client?: InvoiceClientInfo | null;
  pet?: InvoicePetInfo | null;
}

export interface InvoiceCreatePayload {
  userId: number;
  petId?: number | null;
  invoiceDate?: string | null;
  notes?: string | null;
  invoiceItems: Array<{
    serviceId: number;
    quantity: number;
  }>;
}

export interface InvoiceUpdatePayload {
  notes?: string | null;
  invoiceItems?: Array<{
    serviceId: number;
    quantity: number;
  }>;
}

export interface InvoicePayPayload {
  paymentMethod: string;
  paymentDate?: string | null;
}

@Injectable({ providedIn: 'root' })
export class InvoiceService {
  private http = inject(HttpClient);
  private baseUrl = `${API_BASE_URL}/api/invoices`;

  getMyInvoices(): Observable<InvoiceApi[]> {
    return this.http.get<ApiResponse<InvoiceApi[]>>(this.baseUrl).pipe(
      map((response) => response.data || [])
    );
  }

  getInvoices(): Observable<InvoiceApi[]> {
    return this.getMyInvoices();
  }

  getInvoiceById(id: number): Observable<InvoiceApi> {
    return this.http.get<ApiResponse<InvoiceApi>>(`${this.baseUrl}/${id}`).pipe(
      map((response) => response.data)
    );
  }

  createInvoice(payload: InvoiceCreatePayload): Observable<InvoiceApi> {
    return this.http.post<ApiResponse<InvoiceApi>>(this.baseUrl, payload).pipe(
      map((response) => response.data)
    );
  }

  updateInvoice(id: number, payload: InvoiceUpdatePayload): Observable<InvoiceApi> {
    return this.http.put<ApiResponse<InvoiceApi>>(`${this.baseUrl}/${id}`, payload).pipe(
      map((response) => response.data)
    );
  }

  payInvoice(id: number, payload: InvoicePayPayload): Observable<void> {
    return this.http.post<void>(`${this.baseUrl}/${id}/pay`, payload);
  }

  cancelInvoice(id: number): Observable<void> {
    return this.http.post<void>(`${this.baseUrl}/${id}/cancel`, {});
  }

  deleteInvoice(id: number): Observable<void> {
    return this.http.delete<void>(`${this.baseUrl}/${id}`);
  }

  downloadInvoicePdf(id: number): Observable<Blob> {
    return this.http.get(`${this.baseUrl}/${id}/pdf`, { responseType: 'blob' });
  }

  sendInvoicePdf(id: number): Observable<void> {
    return this.http.post<void>(`${this.baseUrl}/${id}/send-email`, {});
  }
}
