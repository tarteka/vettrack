import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { AuthService } from '../services/auth.service';

export const roleGuard: CanActivateFn = (route, state) => {
  const authService = inject(AuthService);
  const router = inject(Router);

  const allowedRoles = route.data['roles'] as string[];
  const user = authService.getUserData();

  // 1. Si no hay usuario o no tiene roles, al login
  if (!user || !user.roles) {
    return router.createUrlTree(['/auth/login']);
  }

  // 2. Comprobamos si tiene al menos un rol permitido
  const hasRole = user.roles.some((role: string) => allowedRoles.includes(role));

  if (!hasRole) {
    // Redirige al dashboard correspondiente segun rol para evitar bucles.
    if (user.roles.includes('ROLE_ADMIN') || user.roles.includes('ROLE_VET')) {
      return router.createUrlTree(['/dashboard-vet']);
    }
    if (user.roles.includes('ROLE_CLIENT')) {
      return router.createUrlTree(['/dashboard-client']);
    }
    return router.createUrlTree(['/auth/login']);
  }

  return true; // Todo OK
};
