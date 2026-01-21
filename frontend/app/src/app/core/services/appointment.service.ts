import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, map } from 'rxjs';
import { Appointment } from '../models/appointment.model';
import { API_BASE_URL } from './api.config';

@Injectable({ providedIn: 'root' })
export class AppointmentService {
  private http = inject(HttpClient);
  private apiUrl = `${API_BASE_URL}/api/client/appointments`;

    getUpcomingAppointments(): Observable<Appointment[]> {
        return this.http.get<any>(`${this.apiUrl}/upcoming`).pipe(
        map(response => response.data || [])
        );
    }

    getPastAppointments(): Observable<Appointment[]> {
        return this.http.get<any>(`${this.apiUrl}/history`).pipe(
            map(response => response.data || [])
        );
    }

    //Solicitar cita Cliente
    requestAppointment(payload: any): Observable<any> {
        return this.http.post(`${this.apiUrl}`, payload);
    }

    getAppointmentTypes(): Observable<any[]> {
        return this.http.get<any>(`${API_BASE_URL}/api/appointment-types`).pipe(
            map(response => response.data || [])
        );
    }

    getAvailableDates(): Observable<string[]> {
        return this.http.get<any>(`${API_BASE_URL}/api/appointment-slots/available-dates`).pipe(
            map(res => res.data || [])
        );
    }

    getAvailableSlots(date: string): Observable<any[]> {
        // Pasamos la fecha como parámetro de consulta
        return this.http.get<any>(`${API_BASE_URL}/api/appointment-slots/available-slots?date=${date}`).pipe(
            map(res => res.data || [])
        );
    }

    //Cancelar cita Cliente
    cancelAppointment(id: number): Observable<any> {
        return this.http.put(`${this.apiUrl}/${id}/cancel`, {});
    }
    //Modificar cita Cliente
    updateAppointment(id: number, payload: any): Observable<any> {
        return this.http.patch(`${this.apiUrl}/${id}`, payload);
    }

    //Calendario Vet
    getAdminCalendar(start: string, end: string): Observable<any[]> {
        // Extraemos solo la parte de la fecha (YYYY-MM-DD) para evitar problemas de formato
        const startDate = start.split('T')[0];
        const endDate = end.split('T')[0];
        // FullCalendar envía fechas de inicio y fin al cambiar de semana/mes
        return this.http.get<any>(`${API_BASE_URL}/api/admin/appointments/calendar`, {
            params: {
            startDate: startDate,
            endDate: endDate
            }
        }).pipe(
            map(res => res.data || [])
        );
    }

    // POST /api/admin/appointments
    createAppointment(payload: any): Observable<any> {
        return this.http.post(`${API_BASE_URL}/api/admin/appointments`, payload);
    }

    // PATCH /api/client/appointments/{id}
    updateAppointmentById(id: string | number, payload: any): Observable<any> {
        return this.http.patch(`${API_BASE_URL}/api/admin/appointments/${id}`, payload);
    }

    // PUT /api/admin/appointments/{id}/cancel
    cancelAdminAppointment(id: string | number): Observable<any> {
        return this.http.put(`${API_BASE_URL}/api/admin/appointments/${id}/cancel`, {});
    }

    // GET /api/admin/appointments/{id}
    getAppointmentById(id: string | number): Observable<any> {
        return this.http.get(`${API_BASE_URL}/api/admin/appointments/${id}`);
    }

    // PATCH /api/admin/appointments/{id}/confirm/
    confirmAdminAppointment(id: string | number): Observable<any> {
        return this.http.patch(`${API_BASE_URL}/api/admin/appointments/${id}/confirm`, {});
    }
}
