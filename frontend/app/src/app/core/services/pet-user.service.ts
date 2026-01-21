import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, map } from 'rxjs';
import { Mascota } from '../models/pet-user.model';
import { API_BASE_URL } from './api.config';

interface ApiResponse<T> {
  success: string;
  code: number;
  data: T;
}

@Injectable({
  providedIn: 'root'
})
export class PetUserService {
  private http = inject(HttpClient);
  private apiUrl = `${API_BASE_URL}/api/pets`;

  constructor() { }

  getPets(): Observable<Mascota[]> {
    return this.http
      .get<ApiResponse<Mascota[]>>(this.apiUrl)
      .pipe(
        map(response => response.data || [])
      );
  }

  getPetsById(id: string): Observable<Mascota> {
    return this.http
      .get<ApiResponse<Mascota>>(`${this.apiUrl}/${id}`)
      .pipe(
        map(response => response.data)
      );
  }
}
