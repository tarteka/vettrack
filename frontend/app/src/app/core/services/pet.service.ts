import { Injectable, inject } from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import { Observable, map } from 'rxjs';
import { API_BASE_URL } from './api.config';

interface ApiResponse<T> {
  success: string;
  code: number;
  data: T;
}

export interface PetApiClient {
  id: number;
  fullName?: string;
  email?: string;
  phone?: string;
  dni?: string;
}

export interface PetTypeApi {
  id: number;
  name: string;
  description: string;
}

export interface PetApi {
  id: number;
  name: string;
  isActive: boolean;
  petType?: PetTypeApi | any;
  breed?: string | null;
  birthDate?: string | null;
  age?: string | number | null;
  gender?: string | null;
  color?: string | null;
  microchip?: string | null;
  weight?: number | string | null;
  allergies?: string | null;
  sterilized?: boolean | null;
  insuranceProvider?: string | null;
  insurancePolicyNumber?: string | null;
  notes?: string | null;
  client?: PetApiClient | any;
  lastAppointmentDate?: string | null;
}

export interface PetCreatePayload {
  clientId: number;
  name: string;
  petTypeId: number;
  breed?: string | null;
  birthDate?: string | null;
  gender: string;
  color?: string | null;
  microchip?: string | null;
  weight?: number | null;
  allergies?: string | null;
  sterilized?: boolean | null;
  insuranceProvider?: string | null;
  insurancePolicyNumber?: string | null;
  notes?: string | null;
}

export interface PetUpdatePayload {
  clientId?: number | null;
  name?: string | null;
  petTypeId?: number | null;
  breed?: string | null;
  birthDate?: string | null;
  gender?: string | null;
  color?: string | null;
  microchip?: string | null;
  weight?: number | null;
  allergies?: string | null;
  sterilized?: boolean | null;
  insuranceProvider?: string | null;
  insurancePolicyNumber?: string | null;
  notes?: string | null;
}

@Injectable({ providedIn: 'root' })
export class PetService {
  private http = inject(HttpClient);
  private baseUrl = `${API_BASE_URL}/api/pets`;

  getPets(isActive?: boolean): Observable<PetApi[]> {
    let params = new HttpParams();

    if (isActive !== undefined) {
      params = params.set('is_active', String(isActive));
    }

    return this.http.get<ApiResponse<PetApi[]>>(this.baseUrl, { params })
      .pipe(map(response => response.data || []));
  }

  getPetById(id: number): Observable<PetApi | any> {
    return this.http.get<ApiResponse<PetApi>>(`${this.baseUrl}/${id}`)
      .pipe(map(response => response.data));
  }

  createPet(payload: PetCreatePayload): Observable<any> {
    return this.http.post<any>(this.baseUrl, payload);
  }

  updatePet(id: number, payload: PetUpdatePayload): Observable<any> {
    return this.http.patch<any>(`${this.baseUrl}/${id}`, payload);
  }

  deletePet(id: number): Observable<any> {
    return this.http.delete<any>(`${this.baseUrl}/${id}`);
  }

  toggleActivePet(id: number): Observable<any> {
    return this.http.patch<any>(`${this.baseUrl}/${id}/toggle-active`, {});
  }

  activatePet(id: number): Observable<any> {
    return this.http.patch<any>(`${this.baseUrl}/${id}/activate`, {});
  }

  deactivatePet(id: number): Observable<any> {
    return this.http.patch<any>(`${this.baseUrl}/${id}/deactivate`, {});
  }

}
