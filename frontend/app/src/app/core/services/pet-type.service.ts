import {inject, Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {API_BASE_URL} from './api.config';
import {map, Observable} from 'rxjs';
import {PetType} from '../models/pet-type.model';

// Interface para la respuesta desde la API
interface ApiResponse<T> {
  success: string;
  code: number;
  data: T;
}

@Injectable({providedIn: 'root'})
export class PetTypeService {

  private http = inject(HttpClient);
  private apiUrl =`${API_BASE_URL}/api/pet-types`

  // Obtiene todos los tipos de mascota
  getAll(): Observable<PetType[]> {
    return this.http
      .get<ApiResponse<PetType[]>>(this.apiUrl)
      .pipe(map(response => response.data));
  }

  // Obtiene un tipo concreto por ID
  getById(id: number): Observable<PetType> {
    return this.http
      .get<ApiResponse<PetType>>(`this.apiUrl/${id}`)
      .pipe(map(response => response.data));

  }

  // Crea un nuevo tipo
  create(data: Pick<PetType, 'name' | 'description'>): Observable<PetType> {
    return this.http
      .post<ApiResponse<PetType>>(this.apiUrl, data)
      .pipe(map(response => response.data));
  }

  // Actualiza un tipo existente
  update(id: number, data: Pick<PetType, 'name' | 'description'>): Observable<PetType> {
    return this.http
      .put<ApiResponse<PetType>>(`${this.apiUrl}/${id}`, data)
      .pipe(map(response => response.data));
  }

  // Elimina un tipo
  delete(id: number): Observable<void> {
    return this.http
      .delete<ApiResponse<null>>(`${this.apiUrl}/${id}`)
      .pipe(map(() => void 0));
  }

}
