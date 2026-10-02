import { Component, EventEmitter, Input, OnInit, Output, ChangeDetectionStrategy } from '@angular/core';
import { AuthService } from '../../core/services/auth.service';

import { RouterModule } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { LucideDynamicIcon } from '@lucide/angular';
import { AlertService} from '../../core/services/alert.service';

type UserRole = 'cliente' | 'veterinario' | 'veterinario-admin';

interface ProfileFormData {
  firstName: string;
  lastName: string;
  email: string;
  phone: string;
  address: string;
  city: string;
  zipCode: string;
  country: string;
  colegiado?: string;
  especialidad?: string;
}
interface PasswordData {
  actual: string;
  nueva: string;
  confirmar: string;
}


@Component({
  selector: 'app-profile',
  standalone: true,
  imports: [RouterModule, FormsModule, LucideDynamicIcon],
  changeDetection: ChangeDetectionStrategy.Eager,
  templateUrl: './profile.component.html'
})

export class ProfileComponent implements OnInit {

  userData: any = null;
  loading = true;
  errorMessage = '';
  originalProfile: any = null;
  apiError = '';
  fieldErrors: Record<string, string> = {};
  passwordErrors: Record<string, string> = {};
  passwordApiError = '';

  userName = '';
  userRole: UserRole = 'cliente';

  @Output() logout = new EventEmitter<void>();

  formData: ProfileFormData = {
    firstName: '',
    lastName: '',
    email: '',
    phone: '',
    address: '',
    city: '',
    zipCode: '',
    country: '',
    colegiado: '',
    especialidad: ''
  };

  passwordData: PasswordData = {
    actual: '',
    nueva: '',
    confirmar: ''
  };

  private mapPasswordField(field: string): string {
  switch (field) {
    case 'currentPassword':
      return 'actual';
    case 'newPassword':
      return 'nueva';
    case 'confirmPassword':
      return 'confirmar';
    default:
      return field;
  }
}

  // pestaña activa: 'datos' | 'seguridad'
  selectedTab: 'datos' | 'seguridad' = 'datos';

constructor(
  private authService: AuthService,
  private alertService: AlertService)
{};

ngOnInit(): void {
  this.loadProfile();
}

private loadProfile(): void {
  this.authService.getProfile().subscribe({
    next: (res) => {
      if (res?.success === 'ok') {
        const data = res.data;

        // Guardamos el perfil original completo
        this.originalProfile = {
          email: data.email,
          firstName: data.firstName,
          lastName: data.lastName,
          phone: data.phone,
          address: data.address,
          city: data.city,
          zipCode: data.zipCode,
          country: data.country
        };

        // Pintamos el formulario
        this.formData.firstName = data.firstName || '';
        this.formData.lastName = data.lastName || '';
        this.formData.email = data.email;
        this.formData.phone = data.phone || '';
        this.formData.address = data.address || '';
        this.formData.city = data.city || '';
        this.formData.zipCode = data.zipCode || '';
        this.formData.country = data.country || '';

        this.userName = `${this.formData.firstName} ${this.formData.lastName}`.trim();
      }

      this.loading = false;
    },
    error: (err) => {
      console.error('Error cargando perfil', err);
      this.errorMessage = 'No se pudo cargar el perfil';
      this.loading = false;
    }
  });
}

  get isVetOrAdmin(): boolean {
    return this.userRole === 'veterinario' || this.userRole === 'veterinario-admin';
  }

  get userInitial(): string {
    if (!this.userName || this.userName.length === 0) {
      return '?';
    }
    return this.userName.charAt(0).toUpperCase();
  }

  get roleLabel(): string {
    if (this.userRole === 'veterinario-admin') {
      return 'Veterinario Administrativo';
    }
    return this.userRole;
  }

  setTab(tab: 'datos' | 'seguridad'): void {
    this.selectedTab = tab;
  }

handleSaveProfile(event: Event): void {
  event.preventDefault();
  this.apiError = '';

  const payload = {
    email: this.formData.email,
    firstName: this.formData.firstName.trim(),
    lastName: this.formData.lastName.trim(),
    phone: this.formData.phone,
    address: this.formData.address,
    city: this.formData.city,
    zipCode: this.formData.zipCode,
    country: this.formData.country
  };

  this.authService.updateProfile(payload).subscribe({
    next: async () => {
      await this.alertService.success('¡Actualizado!', 'Los datos se han guardado correctamente');

      // sincronizamos el estado local
      this.originalProfile = { ...payload };
      this.userName = `${this.formData.firstName} ${this.formData.lastName}`.trim();
    },
    error: (err) => {
      this.alertService.error('Error', 'No se pudo actualizar el perfil');
      this.handleApiError(err);
    }
  });
}

handleApiError(err: any): void {
  console.log('Error completo del backend', err);

  // Reiniciamos errores
  this.apiError = '';
  this.fieldErrors = {};

  // Extraemos los errores de distintos patrones que pueda enviar el backend
  const details =
    err?.error?.details ||
    err?.error?.error?.details ||
    err?.error?.response?.details ||
    {};

  if (details && Object.keys(details).length > 0) {
    // Convertimos todo a string por si algún valor no es string
    for (const key of Object.keys(details)) {
      this.fieldErrors[key] = String(details[key]);
    }
  } else if (err?.error?.message) {
    this.apiError = String(err.error.message);
  } else {
    this.apiError = 'Error al actualizar el perfil';
  }

  console.log('fieldErrors', this.fieldErrors);
}


//Cambio de contraseña
handleChangePassword(event: Event): void {
  event.preventDefault();
  this.passwordErrors = {};

  const { actual, nueva, confirmar } = this.passwordData;

  // --- VALIDACIÓN CLIENTE ---
  if (nueva !== confirmar) {
    this.passwordErrors['confirmar'] = 'Las contraseñas no coinciden';
    return;
  }

  const payload = {
    currentPassword: actual,
    newPassword: nueva,
    confirmPassword: confirmar
  };

  this.authService.changePassword(payload).subscribe({
    next: async () => {
      //alert('Contraseña actualizada correctamente');
      await this.alertService.success('¡Actualizado!', 'Contraseña actualizada correctamente')
      this.passwordData = { actual: '', nueva: '', confirmar: '' };
      this.passwordErrors = {};
    },
    error: (err) => {
      this.alertService.error('Error', 'No se pudo actualizar. Inténtalo de nuevo.')
      console.log('Error cambio contraseña', err);

      // Caso 1: errores por campo
      const details = err?.error?.error?.details;

      if (details) {
        for (const key of Object.keys(details)) {
          const mappedKey = this.mapPasswordField(key);
          this.passwordErrors[mappedKey] = String(details[key]);
        }
        return;
      }

      // Caso 2: error general (ej: contraseña actual incorrecta)
      const message = err?.error?.error?.message;
      if (message) {
        this.passwordErrors['actual'] = message;
      } else {
        this.passwordErrors['general'] = 'Error al actualizar la contraseña';
      }
    }
  });
}


  handleLogout(): void {
    this.authService.logout();
  }
}

