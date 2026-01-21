import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, map } from 'rxjs';
import { UserResponse, User } from '../models/user.model';
import { API_BASE_URL } from './api.config';

@Injectable({ providedIn: 'root' })
export class UserService {
  private http = inject(HttpClient);
  private apiUrl = `${API_BASE_URL}/api/users`;

  getUsers(): Observable<User[]> {
    return this.http.get<UserResponse>(this.apiUrl).pipe(
      map(response => response.data) // Mapea la respuesta para obtener solo el array de usuarios
    );
  }

  // Método para eliminar (DELETE)
  deleteUser(id: number): Observable<void> {
    return this.http.delete<void>(`${this.apiUrl}/${id}`);
  }
  restoreUser(id: number): Observable<void> {
    return this.http.post<any>(`${this.apiUrl}/${id}/restore`, {});
  }

  createUser(user: any): Observable<User> {
    return this.http.post<any>(this.apiUrl, user).pipe(map(res => res.data));
  }

  updateUser(id: number, user: any): Observable<User> {
    return this.http.patch<any>(`${this.apiUrl}/${id}`, user).pipe(
      map(res => res.data)
    );
  }

  getVeterinarians() {
    return this.http.get<{ success: string; data: User[] }>(this.apiUrl)
      .pipe(
        map(res => res.data.filter(u => u.role === 'vet'))
      );
  }
}
