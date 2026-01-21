import { Routes } from '@angular/router';
import { authGuard } from './core/guards/auth.guard';
import { roleGuard } from './core/guards/role.guard';
import { AppLayoutComponent } from './shared/layout/app-layout/app-layout.component';
import { NotFoundComponent } from './features/not-found/not-found.component';

export const routes: Routes = [
    // Ruta por defecto que redirige al dashboard (o login si no está logueado)
    //{ path: '', redirectTo: '/auth/login', pathMatch: 'full' },

    //Ruta de autentificación
    {
        path: 'auth',
        loadChildren: () => import('./features/auth/auth.routes').then(m => m.AUTH_ROUTES)
    },

    //Ruta 404
    {
        path: 'not-found',
        component: NotFoundComponent
    },

    //Rutas protegidas con layout
    {
        path: '',
        component: AppLayoutComponent,
        canActivate: [authGuard], // todas las rutas dentro requieren login
        children: [
                {
                    path: 'dashboard-client',
                    loadChildren: () => import('./features/dashboard/dashboard-client/dashboard-client.routes').then(m => m.DASHBOARD_CLIENT_ROUTES),
                    canActivate: [roleGuard],
                    data: { roles: ['ROLE_CLIENT'] }
                },
                {
                    path: 'dashboard-vet',
                    loadChildren: () => import('./features/dashboard/dashboard-vet/dashboard-vet.routes').then(m => m.DASHBOARD_VET_ROUTES),
                    canActivate: [roleGuard],
                    data: { roles: ['ROLE_VET', 'ROLE_ADMIN'] }
                },
                {
                    path: 'clients',
                    loadChildren: () => import('./features/clientes/clientes.routes').then(m => m.CLIENTES_ROUTES),
                    canActivate: [roleGuard],
                    data: { roles: ['ROLE_VET', 'ROLE_ADMIN'] }
                },

                {
                    path: 'profile',
                    loadChildren: () => import('./features/profile/profile.routes').then(m => m.PROFILE_ROUTES),
                    canActivate: [authGuard],
                    data: { roles: ['ROLE_CLIENT', 'ROLE_VET', 'ROLE_ADMIN'] }
                },

                {
                    path: 'pacientes',
                    loadChildren: () => import('./features/pacientes/pacientes.routes').then(m => m.PACIENTES_ROUTES),
                    canActivate: [roleGuard],
                    data: { roles: ['ROLE_VET', 'ROLE_ADMIN'] }
                },

                {
                    path: 'mascotas',
                    loadChildren: () => import('./features/mascotas/mascotas.routes').then(m => m.MASCOTAS_ROUTES),
                    canActivate: [roleGuard],
                    data: { roles: ['ROLE_CLIENT'] }
                },

                {
                    path: 'mis-citas',
                    loadChildren: () => import('./features/mis-citas/mis-citas.routes').then(m => m.MIS_CITAS_ROUTES),
                    canActivate: [roleGuard],
                    data: { roles: ['ROLE_CLIENT'] }
                },

                {
                    path: 'cuentas',
                    loadChildren: () => import('./features/user-management/user-management.routes').then(m => m.USER_MANAGEMENT_ROUTES),
                    canActivate: [authGuard, roleGuard],
                    data: { roles: ['ROLE_ADMIN'] }
                },

                {
                    path: 'servicios',
                    loadChildren: () => import('./features/servicios/servicios.routes').then(m => m.SERVICIOS_ROUTES),
                    canActivate: [roleGuard],
                    data: { roles: ['ROLE_ADMIN'] }
                },

                {
                    path: 'facturas-vet',
                    loadComponent: () => import('./features/facturas/facturas.component').then(m => m.FacturasComponent),
                },

                {
                    path: 'calendario',
                    loadChildren: () => import('./features/calendario/calendario.routes').then(m => m.CALENDARIO_ROUTES),
                    canActivate: [roleGuard],
                    data: { roles: ['ROLE_VET', 'ROLE_ADMIN'] }
                },

                {
                    path: 'crear-factura',
                    loadComponent: () => import('./features/crear-factura/crear-factura.component').then(m => m.CrearFacturaComponent),
                    canActivate: [roleGuard],
                    data: { roles: ['ROLE_VET', 'ROLE_ADMIN'] }
                },
                {
                    path: 'crear-factura/:invoiceId',
                    loadComponent: () => import('./features/crear-factura/crear-factura.component').then(m => m.CrearFacturaComponent),
                    canActivate: [roleGuard],
                    data: { roles: ['ROLE_VET', 'ROLE_ADMIN'] }
                },

                {
                    path: 'mis-facturas',
                    loadComponent: () => import('./features/mis-facturas/mis-facturas.component').then(m => m.MisFacturasComponent),
                    canActivate: [roleGuard],
                    data: { roles: ['ROLE_CLIENT'] }
                },

                {
                    path: '',
                    redirectTo: 'dashboard-client',
                    pathMatch: 'full' // fallback dentro del layout
                }
            ]
        },

    //Manejo de rutas no encontradas (404)
    { path: '**', redirectTo: '/not-found' }
];
