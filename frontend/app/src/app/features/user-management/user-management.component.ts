import {Component, inject, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {FormsModule, ReactiveFormsModule} from '@angular/forms';
import {LucideAngularModule} from 'lucide-angular';
import {UserService} from '../../core/services/user.service';
import {User} from '../../core/models/user.model';
import {AlertService} from '../../core/services/alert.service';
import {AuthService} from '../../core/services/auth.service';

@Component({
  selector: 'app-user-management',
  standalone: true,
  imports: [CommonModule, FormsModule, ReactiveFormsModule, LucideAngularModule],
  templateUrl: './user-management.component.html'
})
export class UserManagementComponent implements OnInit {
  private userService = inject(UserService);
  private alertService = inject(AlertService);
  private authService = inject(AuthService);

  usuarios: User[] = [];
  searchTerm: string = "";
  filterRol: string = "todos";
  isDialogOpen: boolean = false;
  editingUsuario: User | null = null;
  currentUserId: number | undefined;

/** usuarioForm: any = {
    firstName: "",
    lastName: "",
    email: "",
    phone: "",
    role: "",
  };*/
  usuarioForm: any = {
    nombre: "", // Lo mapearemos a firstName/lastName antes de enviar
    apellido: "",
    dni: "",
    email: "",
    telefono: "",
    rol: "",
  };

  ngOnInit(): void {
    this.loadUsers();
    this.currentUserId = this.authService.getCurrentUserId();
  }

  loadUsers(): void {
    this.userService.getUsers().subscribe({
      next: (data) => this.usuarios = data,
      error: (err) => console.error('Error al cargar usuarios desde el backend', err)
    });
  }

  
  get filteredUsuarios() {
    return this.usuarios.filter((u) => {
      const nombreCompleto = `${u.firstName} ${u.lastName}`.toLowerCase();
      const matchSearch =
        nombreCompleto.includes(this.searchTerm.toLowerCase()) ||
        u.email.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
        u.phone.includes(this.searchTerm);

      const matchRol = this.filterRol === "todos" || u.role === this.filterRol;
      return matchSearch && matchRol;
    });
  }

  get countVeterinarios(): number {
    return this.usuarios.filter(u => u.role === 'admin' || u.role === 'vet').length;
  }

  get countClientes(): number {
    return this.usuarios.filter(u => u.role === 'client').length;
  }

  get countActivos(): number {
    return this.usuarios.filter(u => u.isActive).length;
  }

  getRolLabel(rol: string): string {
    switch (rol) {
      case "admin": return "Administrador";
      case "vet": return "Veterinario";
      case "client": return "Cliente";
      default: return rol;
    }
  }

  // Métodos de UI
  openDialog(usuario?: User) {
    if (usuario && usuario.id === this.currentUserId) {
      this.alertService.error(
        'Acción no permitida',
        'Puedes editar tus datos desde el apartado Perfil.'
      );
      return;
    }

    if (usuario) {
      this.editingUsuario = usuario;
      this.usuarioForm = {
        nombre: usuario.firstName,
        apellido: usuario.lastName,
        dni: usuario.dni,
        email: usuario.email,
        telefono: usuario.phone,
        rol: usuario.role
      };
    } else {
      this.editingUsuario = null;
      this.usuarioForm = { firstName: "", lastName: "", email: "", phone: "", role: ""};
    }
    this.isDialogOpen = true;
  }

  closeDialog() {
    this.isDialogOpen = false;
    this.editingUsuario = null;
  }

  saveUsuario() {

    if (!this.editingUsuario && this.usuarioForm.rol === 'client') {
      this.alertService.error(
        'Acción no permitida',
        'Los clientes no se crean desde este apartado.'
      );
      return;
    }

    const phoneLimpio = this.usuarioForm.telefono.replace(/\s+/g, '');

    const roleInternal =
      this.usuarioForm.rol === 'admin' ? 'ROLE_ADMIN' :
        this.usuarioForm.rol === 'vet' ? 'ROLE_VET' :
          'ROLE_CLIENT';

    const userData = {
      firstName: this.usuarioForm.nombre,
      lastName: this.usuarioForm.apellido,
      dni: this.usuarioForm.dni,
      email: this.usuarioForm.email,
      phone: phoneLimpio,
      roles: [roleInternal]
    };

    const request$ = this.editingUsuario
      ? this.userService.updateUser(this.editingUsuario.id, userData)
      : this.userService.createUser(userData);

    request$.subscribe({
      next: () => {
        this.loadUsers();
        this.closeDialog();
      },

      error: (err) => {
        this.handleSaveUserError(err)
      }
    });
  }

  toggleEstado(usuario: User) {
    const activar = !usuario.isActive;
    console.log(activar);

    if (activar) {
      this.restoreUsuario(usuario)
    } else {
      this.deactivateUsuario(usuario)
    }
  }

  restoreUsuario(usuario: User) {
    this.userService.restoreUser(usuario.id).subscribe({
      next: () => {
        this.usuarios = this.usuarios.map(u =>
          u.id === usuario.id ? { ...u, isActive: true } : u
        );
        void this.alertService.success(
          'Usuario restaurado',
          'El usuario se ha restaurado correctamente.'
        );
      },
      error: (err) => console.error('Error al restaurar usuario', err)
    });
  }

  deactivateUsuario(usuario: User) {

    if (usuario.id === this.currentUserId) {
      this.alertService.error(
        'Acción no permitida',
        'No puedes desactivar tu propio usuario.'
      );
      return;
    }

    this.userService.deleteUser(usuario.id).subscribe({
      next: () => {
        this.usuarios = this.usuarios.map(u =>
          u.id === usuario.id ? { ...u, isActive: false } : u
        );

        void this.alertService.success(
          'Usuario desactivado',
          'El usuario se ha desactivado correctamente.'
        );
      },
      error: (err) => console.error('Error al desactivar usuario', err)
    });
  }

  private handleSaveUserError(err: any): void {

    /// =========================
    // 409 → CONFLICTO (UNICIDAD)
    // =========================
    if (err.status === 409) {
      const details: string =
        err?.error?.error?.details?.toLowerCase() ?? '';

      if (
        details.includes('@') ||
        details.includes('email') ||
        details.includes('e7927c74')
      ) {
        this.alertService.error(
          'Email duplicado',
          'Ya existe un usuario con ese email.'
        );
        return;
      }

      if (details.includes('dni')) {
        this.alertService.error(
          'DNI duplicado',
          'Ya existe un usuario con ese DNI.'
        );
        return;
      }

      this.alertService.error(
        'Datos duplicados',
        'Ya existe un usuario con esos datos.'
      );
      return;
    }

    // =========================
    // 400 → VALIDACIÓN DE CAMPOS
    // =========================
    if (err.status === 400) {
      const fieldErrors = err?.error?.error?.details;

      if (fieldErrors?.dni) {
        this.alertService.error(
          'DNI no válido',
          fieldErrors.dni
        );
        return;
      }

      if (fieldErrors?.email) {
        this.alertService.error(
          'Email no válido',
          fieldErrors.email
        );
        return;
      }

      if (fieldErrors?.phone) {
        this.alertService.error(
          'Teléfono no válido',
          fieldErrors.phone
        );
        return;
      }

      // Fallback validación
      this.alertService.error(
        'Datos no válidos',
        'Revisa los campos del formulario.'
      );
      return;
    }

    // =========================
    // ERROR NO CONTROLADO
    // =========================
    this.alertService.error(
      'Error',
      'No se pudo guardar el usuario. Inténtalo de nuevo.'
    );
  }
}
