import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { LucideAngularModule } from 'lucide-angular';
import {
  ReactiveFormsModule,
  FormBuilder,
  FormGroup,
  Validators
} from '@angular/forms';
import { Router, ActivatedRoute } from '@angular/router';
import { AuthService } from '../../../core/services/auth.service';
import {AlertService} from '../../../core/services/alert.service';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, LucideAngularModule],
  templateUrl: './login.component.html'
})
export class LoginComponent {

  loginForm: FormGroup;
  recoveryForm: FormGroup;
  loading = false;
  errorMessage = '';
  showRecoveryModal = false;
  recoverySent = false;
  recoveryEmail = '';
  showPassword = false;
  private readonly returnUrl = '/';

  constructor(
    private fb: FormBuilder,
    private authService: AuthService,
    private alertService: AlertService,
    private router: Router,
    private route: ActivatedRoute
  ) {
    this.loginForm = this.fb.group({
      username: ['', [Validators.required, Validators.email]],
      password: ['', Validators.required],
    });

    this.recoveryForm = this.fb.group({
      email: ['', [Validators.required, Validators.email]]
    });

    this.returnUrl =
      this.route.snapshot.queryParams['returnUrl'] || '/';
  }

  handleSubmit(): void {
    if (this.loginForm.invalid) {
      return;
    }

    const { username, password } = this.loginForm.value;

    this.loading = true;

    this.authService.login(username, password).subscribe({
      next: () => {
        this.loading = false;
        this.router.navigateByUrl(this.returnUrl);
      },
      error: (err: { status: number}) => {
        this.loading = false;

        if (err.status === 401 || err.status === 403) {
          this.alertService.error(
            'Credenciales incorrectas',
            'El usuario o la contraseña no son válidos'
          );
        } else {
          this.alertService.error(
            'Error del servidor',
            'No se puedo iniciar sesión. Inténtelo más tarde'
          );
        }
      }
    });
  }

  togglePasswordVisibility(): void {
    this.showPassword = !this.showPassword;
  }

  toggleRecoveryModal(): void {
    this.showRecoveryModal = !this.showRecoveryModal;
    if (!this.showRecoveryModal) {
      this.recoverySent = false;
      this.recoveryForm.reset();
    }
  }

  sendRecoveryEmail(): void {
    console.log('🔥 sendRecoveryEmail()');

    if (this.recoveryForm.valid) {
      const email = this.recoveryForm.get('email')!.value!;

      this.authService.recoverPassword(email).subscribe({
        next: ()=> {
          this.recoverySent = true;
        },
        error: (err) => {
           console.log(err);
        }
      })
    }
  }
}

