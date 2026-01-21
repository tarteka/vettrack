import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, map } from 'rxjs';
import { API_BASE_URL } from './api.config';

@Injectable({
  providedIn: 'root'
})
export class TreatmentService {
  private http = inject(HttpClient);
  private apiUrl = `${API_BASE_URL}/api/pets/`; //${petId}/treatments/
  constructor() { }

  getTreatments(petId: string): Observable<any[]> {
    return this.http
      .get<{ success: string; code: number; data: any[] }>(`${this.apiUrl}${petId}/treatments`)
      .pipe(
        map(response => response.data || [])
      );
  }

  getTreatmentById(petId: string, treatmentId: string): Observable<any> {
    return this.http
      .get<{ success: string; code: number; data: any }>(`${this.apiUrl}${petId}/treatments/${treatmentId}`)
      .pipe(
        map(response => response.data)
      );
  }

  deleteTreatment(petId: string, treatmentId: string): Observable<void> {
    return this.http.delete<void>(`${this.apiUrl}${petId}/treatments/${treatmentId}`);
  }

  updateTreatment(petId: string, treatmentId: string, treatmentData: any): Observable<any> {
    return this.http.put<any>(`${this.apiUrl}${petId}/treatments/${treatmentId}`, treatmentData);
  }

  addTreatment(petId: string, treatmentData: any): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}${petId}/treatments`, treatmentData);
  }

  suspendTreatment(petId: string, treatmentId: string): Observable<any> {
    return this.http.patch<any>(`${this.apiUrl}${petId}/treatments/${treatmentId}/suspend`, {});
  }

}
