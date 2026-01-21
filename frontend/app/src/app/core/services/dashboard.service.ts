import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, map } from 'rxjs';
import { API_BASE_URL } from './api.config';

interface ApiResponse<T> {
  success: string;
  code: number;
  data: T;
}

export interface DashboardPet {
  id: number;
  name: string;
  petType: string;
  breed?: string;
}

export interface DashboardAppointment {
  id: number;
  petName: string;
  clientName: string;
  veterinarianName?: string | null;
  appointmentType: string;
  date?: string | null;
  time: string;
}

export interface DashboardInvoice {
  id: number;
  totalAmount: number;
  invoiceDate: string;
  invoiceNumber: string;
  status: string;
}

export interface DashboardNotification {
  id: number;
  title: string;
  message: string;
  priority: string;
  dateTime: string;
  type: string;
}

@Injectable({ providedIn: 'root' })
export class DashboardService {
  private http = inject(HttpClient);
  private baseUrl = `${API_BASE_URL}/api/dashboard`;

  getPets(): Observable<DashboardPet[]> {
    return this.http.get<ApiResponse<DashboardPet[]>>(`${this.baseUrl}/pets`).pipe(
      map(response => response.data || [])
    );
  }

  getAppointments(): Observable<DashboardAppointment[]> {
    return this.http.get<ApiResponse<DashboardAppointment[]>>(`${this.baseUrl}/appointments`).pipe(
      map(response => response.data || [])
    );
  }

  getInvoices(): Observable<DashboardInvoice[]> {
    return this.http.get<ApiResponse<DashboardInvoice[]>>(`${this.baseUrl}/invoices`).pipe(
      map(response => response.data || [])
    );
  }

  getNotifications(): Observable<DashboardNotification[]> {
    return this.http.get<ApiResponse<DashboardNotification[]>>(`${this.baseUrl}/notifications`).pipe(
      map(response => response.data || [])
    );
  }
}
