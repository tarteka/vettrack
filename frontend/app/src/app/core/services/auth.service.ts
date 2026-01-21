import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Router} from '@angular/router';
import {BehaviorSubject, catchError, Observable, of, tap, throwError} from 'rxjs';
import {switchMap} from 'rxjs/operators';
import {API_BASE_URL} from './api.config';
import { AlertService } from './alert.service';
import { jwtDecode } from 'jwt-decode';

// Interface del login
export interface LoginResponse {
  token: string;
  refresh_token: string;
}

//Nuevo
export interface AuthUser {
  username: string;
  roles: string[];
  id?: number;
  firstName?: string;
  lastName?: string;
  fullName?: string;
}

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private apiUrl = `${API_BASE_URL}/api/auth/login`;
  private tokenKey = 'jwt_token';
  private userDataKey = 'user_data';
  private refreshTokenKey = 'refresh_token';

  //Nuevo, observable para el usuario autenticado
  private userSubject = new BehaviorSubject<AuthUser | null>(this.getUserData());
  user$ = this.userSubject.asObservable();

  // Estado de autenticación reactivo
  private isAuthenticatedSubject = new BehaviorSubject<boolean>(this.hasToken());
  // isAuthenticated$ = this.isAuthenticatedSubject.asObservable();

  private refreshTimeout?: ReturnType<typeof setTimeout>;

  constructor(
    private http: HttpClient,
    private router: Router,
    private alertService: AlertService
  ) {
    this.initAuth();
  }

  /**
   * Verifica si se ejecuta en el navegador (no SSR)
   */
  private isBrowser(): boolean {
    return typeof window !== 'undefined' && typeof localStorage !== 'undefined';
  }

  /**
   * Envia las credenciales al backend y gestiona el login
   */
  login(username: string, password: string): Observable<LoginResponse> {
    return this.http.post<LoginResponse>(this.apiUrl, { username, password }).pipe(
      tap(response => {

        if (response.token && response.refresh_token) {
          this.saveToken(response.token);
          this.setRefreshToken(response.refresh_token);

          const user = this.decodeToken(response.token);
          this.saveUserData(user);
          this.isAuthenticatedSubject.next(true);
        }
      })
    );
  }

  /**
   * Guarda el token en localStorage (solo en navegador)
   */
  private saveToken(token: string): void {
    if (!this.isBrowser()) return;
    localStorage.setItem(this.tokenKey, token);
  }

  /**
   * Devuelve el token actual (solo en navegador)
   */
  getToken(): string | null {
    if (!this.isBrowser()) return null;
    return localStorage.getItem(this.tokenKey);
  }

  /**
   * Guarda el token actualizado
   */
  setToken(token: string): void {
    if (!this.isBrowser()) return;
    localStorage.setItem(this.tokenKey, token);
    this.scheduleTokenRefresh(token);
  }

  /**
   * Guarda el refreshToken (si lo devuelve el backend)
   */
  setRefreshToken(token: string): void {
    if (!this.isBrowser()) return;
    localStorage.setItem(this.refreshTokenKey, token);
  }

  /**
   * Obtiene el refresh token
   */
  getRefreshToken(): string | null {
    if (!this.isBrowser()) return null;
    return localStorage.getItem(this.refreshTokenKey);
  }

  /**
   * Devuelve la fecha de expiración del token en ms
   */
  private getTokenExpiration(token: string): number {
    const payload: any = this.decodeToken(token);
    return payload?.exp ? payload.exp * 1000 : 0;
  }

  /**
   * Llama al endpoint de refresh-token
   * Devuelve un observable con el nuevo accessToken
   */
  refreshToken(): Observable<string> {
    const refreshToken = this.getRefreshToken();

    if (!refreshToken) {
      // No hay refresh token, forzamos logout
      this.alertService.error('Sesión expirada', 'No fue posible encontrar el token para renovar la sesión.');
      this.logout();
      return throwError(() => new Error('No refresh token available'));
    }

    const body = new URLSearchParams();
    body.set('refresh_token', refreshToken);

    return this.http.post<{ token: string }>(
      `${API_BASE_URL}/api/auth/refresh`,
            body.toString(),
            {
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
              }
            }
      ).pipe(tap(res => {
        if (res.token) {
          this.setToken(res.token);
        }
      }),
      switchMap(res => {
        if (!res.token) {
          this.alertService.error('Error al renovar sesión', 'El servidor no devolvió un nuevo token. Inicia sesión nuevamente.');
          return throwError(() => new Error('No token returned from refresh'));
        }
        return of(res.token);
      }),
      catchError(error => {
        this.alertService.error('Sesión expirada', 'Tu sesión ha caducado. Por favor, inicia sesión nuevamente.');
        this.logout();
        return throwError(() => error);
      })
    );
  }

  /**
   * Decodifica el token JWT sin necesidad de librerias externas
   */
  decodeToken(token: string): any {
    try {
      return jwtDecode<AuthUser>(token);
    } catch (error) {
      console.error('Error al decodificar el token:', error);
      return null;
    }
  }

  /**
   * Guarda los datos del usuario decodificados del token
   */
  saveUserData(user: any): void {
    if (!this.isBrowser()) return;
    localStorage.setItem(this.userDataKey, JSON.stringify(user));
    this.userSubject.next(user);
  }

  /**
   * Obtiene los datos del usuario actual
   */
  getUserData(): any {
    if (!this.isBrowser()) return null;
    const user = localStorage.getItem(this.userDataKey);
    return user ? JSON.parse(user) : null;
  }

  /**
   * Devuelve el ID del usuario autenticado
   */
  getCurrentUserId(): number {
    const user = this.getUserData();
    return user.id;
  }

  /**
   * Guarda los datos modificados del usuario
   */
  updateProfile(payload: Partial<{
    email: string;
    firstName: string;
    lastName: string;
    phone: string;
    address: string;
    city: string;
    zipCode: string;
    country: string;
  }>): Observable<any> {
    return this.http.put(`${API_BASE_URL}/api/profile`, payload);
  }

  /**
   * Recuperar contraseña
   */
  recoverPassword(email: string): Observable<any> {

    console.log('email: ', email);

    return this.http.post(`${API_BASE_URL}/api/recovery-password?email=`.concat(email),{});
  }

  /**
   * Cambio de contraseña
   */
  changePassword(data: { currentPassword: string; newPassword: string; confirmPassword: string }) {
    return this.http.put<any>(`${API_BASE_URL}/api/profile/password`, data);
  }

  /**
   * Verifica si hay token almacenado
   */
  private hasToken(): boolean {
    if (!this.isBrowser()) return false;
    return !!localStorage.getItem(this.tokenKey);
  }

  /**
   * Verifica si el usuario está logueado y el token es válido
   */
  isLoggedIn(): boolean {
    const token = this.getToken();
    if (!token) return false;

    const payload = this.decodeToken(token);
    if (!payload?.exp) return false;

    return payload.exp * 1000 > Date.now();
  }

  getProfile(): Observable<any> {
    return this.http.get<any>(`${API_BASE_URL}/api/profile`);
  }

  /**
   * Cierra sesion
   */
  logout(): void {

    if (this.refreshTimeout) {
      clearTimeout(this.refreshTimeout);
    }

    if (this.isBrowser()) {
      localStorage.removeItem(this.tokenKey);
      localStorage.removeItem(this.refreshTokenKey);
      localStorage.removeItem(this.userDataKey);
    }
    this.userSubject.next(null);
    this.isAuthenticatedSubject.next(false);
    void this.router.navigate(['/auth/login']);
  }

  /**
   * Programa el refresh automático del token
   */
  private scheduleTokenRefresh(token: string): void {
    const expiresAt = this.getTokenExpiration(token);
    if (!expiresAt) return;

    const refreshIn = expiresAt - Date.now() - 60_000;
    // 1 minuto antes de expirar

    if (this.refreshTimeout) {
      clearTimeout(this.refreshTimeout);
    }

    this.refreshTimeout = setTimeout(() => {
      this.refreshToken().subscribe();
    }, Math.max(refreshIn, 0));
  }

  /**
   * Inicializa la sesión si hay token almacenado
   */
  initAuth(): void {
    const token = this.getToken();
    if (token && this.isLoggedIn()) {
      this.scheduleTokenRefresh(token);
      this.saveUserData(this.decodeToken(token));
      this.isAuthenticatedSubject.next(true);
    }
  }

}
