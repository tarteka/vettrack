import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, map } from 'rxjs';
import { API_BASE_URL } from './api.config';

interface ApiResponse<T> {
  success: string;
  code: number;
  data: T;
}

export interface ClientListItem {
  id: number;
  email: string;
  firstName: string;
  lastName: string;
  dni: string;
  phone?: string | null;
  address?: string | null;
  city?: string | null;
  zipCode?: string | null;
  petCount: number;
  role: string;
  additionalNotes?: string | null;
}

export interface ClientDetail {
  id: number;
  email: string;
  firstName: string;
  lastName: string;
  dni: string;
  phone?: string | null;
  city?: string | null;
  zipCode?: string | null;
  address?: string | null;
  pets?: any[];
  petCount?: number;
  role?: string;
  createdAt?: string;
}

export interface ClientCreatePayload {
  email: string;
  firstName: string;
  lastName: string;
  dni: string;
  phone?: string | null;
  address?: string | null;
  city?: string | null;
  zipCode?: string | null;
  roles: string[];
  additionalNotes?: string | null;
}

export interface ClientUpdatePayload {
  email?: string | null;
  firstName?: string | null;
  lastName?: string | null;
  dni?: string | null;
  phone?: string | null;
  address?: string | null;
  city?: string | null;
  zipCode?: string | null;
  roles?: string[] | null;
  additionalNotes?: string | null;
}

@Injectable({ providedIn: 'root' })
export class ClientService {
  private http = inject(HttpClient);
  private baseUrl = `${API_BASE_URL}/api/users`;

  getClients(): Observable<ClientListItem[]> {
    return this.http.get<ApiResponse<ClientListItem[]>>(`${this.baseUrl}/clients`).pipe(
      map(response => response.data || [])
    );
  }

  getClientById(id: number): Observable<ClientDetail> {
    return this.http.get<ApiResponse<ClientDetail>>(`${this.baseUrl}/${id}`).pipe(
      map(response => response.data)
    );
  }

  createClient(payload: ClientCreatePayload): Observable<ClientDetail> {
    return this.http.post<ApiResponse<any>>(this.baseUrl, payload).pipe(
      map(response => response.data?.user ?? response.data)
    );
  }

  updateClient(id: number, payload: ClientUpdatePayload): Observable<ClientDetail> {
    return this.http.patch<ApiResponse<ClientDetail>>(`${this.baseUrl}/${id}`, payload).pipe(
      map(response => response.data)
    );
  }

  deleteClient(id: number): Observable<void> {
    return this.http.delete<void>(`${this.baseUrl}/${id}`);
  }
}
