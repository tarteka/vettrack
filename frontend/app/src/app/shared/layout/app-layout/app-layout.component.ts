import {
  Component,
  Input,
  OnInit,
  OnDestroy,
  HostListener
} from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router, RouterModule, NavigationEnd } from '@angular/router';
import { LucideAngularModule } from 'lucide-angular';
import { AuthService } from '../../../core/services/auth.service';
import { Subscription, filter } from 'rxjs';

/**
 * Elemento del menú lateral
 */
interface MenuItem {
  id: string;
  label: string;
  icon: string;
  route: string;
}

/**
 * Tipos de viewport que nos interesan
 */
type Viewport = 'mobile' | 'tablet' | 'desktop';

@Component({
  selector: 'app-app-layout',
  standalone: true,
  imports: [CommonModule, RouterModule, LucideAngularModule],
  templateUrl: './app-layout.component.html'
})
export class AppLayoutComponent
  implements OnInit, OnDestroy {

  /* =============================
   * Inputs
   * ============================= */

  @Input() userName: string = 'Usuario';
  @Input() userRole: 'veterinario' | 'veterinario-admin' | 'cliente' = 'cliente';
  @Input() currentPage: string = '';

  /* =============================
   * Estado del sidebar
   * ============================= */

  sidebarOpen = false;
  sidebarExpanded = false;
  sidebarOverlay = false;

  /**
   * Viewport actual para evitar reaccionar
   * a cada resize innecesariamente
   */
  private currentViewport: Viewport | null = null;

  private subs = new Subscription();

  constructor(
    private router: Router,
    private authService: AuthService
  ) {}

  /* =============================
   * Ciclo de vida
   * ============================= */

  ngOnInit(): void {
    // Ajuste inicial del sidebar según el tamaño de pantalla
    this.syncSidebarWithViewport();

    // Suscripción al usuario autenticado
    const authSub = this.authService.user$.subscribe(user => {
      if (!user) return;

      this.userName = user.fullName ?? user.username;
      this.userRole = this.mapRole(user.roles);


      this.updateCurrentPageFromUrl();
    });
    this.subs.add(authSub);

    // Actualizar el menú activo tras cada navegación
    const navSub = this.router.events
      .pipe(filter(event => event instanceof NavigationEnd))
      .subscribe(() => this.updateCurrentPageFromUrl());
    this.subs.add(navSub);

    this.updateCurrentPageFromUrl();
  }

  ngOnDestroy(): void {
    this.subs.unsubscribe();
  }

  /**
   * Escucha los cambios reales de tamaño de ventana
   * y sincroniza solo si cambia el breakpoint
   */
  @HostListener('window:resize')
  onResize(): void {
    this.syncSidebarWithViewport();
  }

  /* =============================
   * Sidebar logic
   * ============================= */

  /**
   * Determina si estamos en un escritorio.
   */
  get isDesktop(): boolean {
    // lg breakpoint de Tailwind = 1280px
    return window.innerWidth >= 1280;
  }

  /**
   * Sincroniza el estado del sidebar únicamente
   * cuando cambia el breakpoint
   */
  private syncSidebarWithViewport(): void {
    const viewport = this.getViewport(window.innerWidth);

    // Si seguimos en el mismo breakpoint, no tocamos nada
    if (viewport === this.currentViewport) return;

    this.currentViewport = viewport;

    switch (viewport) {
      case 'desktop':
        this.sidebarOpen = true;   // Siempre abierto
        break;

      case 'tablet':
        this.sidebarOpen = false;  // Solo iconos
        break;

      case 'mobile':
        this.sidebarOpen = false;  // Oculto
        break;
    }
  }

  /**
   * Devuelve el tipo de viewport según el ancho
   */
  private getViewport(width: number): Viewport {
    if (width >= 1024) return 'desktop';
    if (width >= 768) return 'tablet';
    return 'mobile';
  }

  /* =============================
   * Navegación
   * ============================= */

  navigate(item: MenuItem): void {
    this.currentPage = item.id;
    void this.router.navigate([item.route]);

    // En móvil cerramos el sidebar tras navegar
    if (this.currentViewport === 'mobile') {
      this.sidebarOpen = false;
    }
  }

  logout(): void {
    this.authService.logout();
    void this.router.navigate(['/auth/login']);
  }

  /* =============================
   * Utilidades
   * ============================= */

  get userInitial(): string {
    return this.userName.charAt(0).toUpperCase();
  }

  private mapRole(
    roles: string[]
  ): 'veterinario' | 'veterinario-admin' | 'cliente' {
    if (roles.includes('ROLE_ADMIN')) return 'veterinario-admin';
    if (roles.includes('ROLE_VET')) return 'veterinario';
    return 'cliente';
  }

  /**
   * Marca como activo el item del menú
   * según la URL actual
   */
  private updateCurrentPageFromUrl(): void {
    const url = this.router.url || '/';
    const found = this.menu.find(item =>
      url === item.route ||
      url.startsWith(item.route + '/') ||
      url.startsWith(item.route)
    );

    if (found) {
      this.currentPage = found.id;
    }
  }

  /* =============================
   * Menús por rol
   * ============================= */

  veterinarioMenu: MenuItem[] = [
    { id: 'dashboard-vet', label: 'Dashboard', icon: 'layout-dashboard', route: '/dashboard-vet' },
    { id: 'clientes', label: 'Propietarios', icon: 'users', route: '/clients' },
    { id: 'pacientes', label: 'Mascotas', icon: 'paw-print', route: '/pacientes' },
    { id: 'calendario', label: 'Citas', icon: 'calendar', route: '/calendario' },
    { id: 'facturas-vet', label: 'Facturas', icon: 'dollar-sign', route: '/facturas-vet' },
    { id: 'perfil', label: 'Perfil', icon: 'user', route: '/profile' }
  ];

  veterinarioAdminMenu: MenuItem[] = [
    ...this.veterinarioMenu,
    { id: 'servicios', label: 'Servicios', icon: 'briefcase', route: '/servicios' },
    { id: 'cuentas', label: 'Cuentas', icon: 'user-cog', route: '/cuentas' }
  ];

  clienteMenu: MenuItem[] = [
    { id: 'dashboard-client', label: 'Inicio', icon: 'home', route: '/dashboard-client' },
    { id: 'mis-mascotas', label: 'Mascotas', icon: 'paw-print', route: '/mascotas' },
    { id: 'mis-citas', label: 'Citas', icon: 'calendar', route: '/mis-citas' },
    { id: 'mis-facturas', label: 'Facturas', icon: 'dollar-sign', route: '/mis-facturas' },
    { id: 'perfil', label: 'Perfil', icon: 'user', route: '/profile' }
  ];

  get menu(): MenuItem[] {
    switch (this.userRole) {
      case 'veterinario-admin':
        return this.veterinarioAdminMenu;
      case 'veterinario':
        return this.veterinarioMenu;
      default:
        return this.clienteMenu;
    }
  }
}
