import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, map } from 'rxjs';
import { API_BASE_URL } from './api.config';

interface ApiResponse<T> {
  success: string;
  code: number;
  data: T;
}

export interface MedicalRecord {
  id: number;
  petId: number;
  type: string;
  description: string;
  veterinarian: string;
  diagnosis?: string | null;
  notes?: string | null;
  procedures?: string | null;
  date: string;
}

export interface MedicalRecordCreatePayload {
  type: string;
  description: string;
  diagnosis: string;
  procedures?: string | null;
  notes?: string | null;
}

@Injectable({ providedIn: 'root' })
export class MedicalRecordService {
  private http = inject(HttpClient);

  getMedicalRecords(petId: number): Observable<MedicalRecord[]> {
    return this.http.get<ApiResponse<MedicalRecord[]>>(`${API_BASE_URL}/api/pets/${petId}/medical-records`).pipe(
      map(response => response.data || [])
    );
  }

  createMedicalRecord(
    petId: number,
    payload: MedicalRecordCreatePayload
  ): Observable<ApiResponse<{ id: number }>> {
    return this.http.post<ApiResponse<{ id: number }>>(
      `${API_BASE_URL}/api/pets/${petId}/medical-records`,
      payload
    );
  }

}
