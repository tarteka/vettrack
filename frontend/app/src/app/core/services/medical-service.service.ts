import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { Servicio, ServiceCategory } from '../models/service.model';
import { API_BASE_URL } from './api.config';
import { map } from 'rxjs/operators';

@Injectable({ providedIn: 'root' })
export class MedicalService {
  private http = inject(HttpClient);
  private apiUrl = `${API_BASE_URL}/api/services`; 

  getServices(): Observable<Servicio[]> {
    return this.http.get<any>(this.apiUrl).pipe(
      // Extraemos la propiedad 'data' que es donde está el array real
      map(response => response.data)
    );
  }

  // Opcional: Métodos para CRUD
  createService(payload: Partial<Servicio>): Observable<Servicio> {
    return this.http.post<Servicio>(this.apiUrl, payload);
  }

  deleteService(id: number): Observable<void> {
    return this.http.delete<void>(`${this.apiUrl}/${id}`);
  }

  updateService(id: number, payload: Partial<Servicio>): Observable<Servicio> {
    return this.http.put<Servicio>(`${this.apiUrl}/${id}`, payload);
  }

  activateService(id: number): Observable<Servicio> {
    return this.http.post<Servicio>(`${this.apiUrl}/${id}/activate`, {});
  }

  deactivateService(id: number): Observable<Servicio> {
    return this.http.post<Servicio>(`${this.apiUrl}/${id}/deactivate`, {});
  }

  getCategories(): Observable<ServiceCategory[]> {
    return this.http.get<any>(`${API_BASE_URL}/api/service-categories`).pipe(
      map(response => response.data || [])
    );
  }
}
