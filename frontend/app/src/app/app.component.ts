import { Component, OnInit, ChangeDetectionStrategy } from '@angular/core';
import { RouterOutlet } from '@angular/router';

import { InactivityService } from './core/services/inactive.service';
import { AuthService } from './core/services/auth.service';
import { AlertService } from './core/services/alert.service';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [RouterOutlet],
  changeDetection: ChangeDetectionStrategy.Eager,
  templateUrl: './app.component.html'
})
export class AppComponent implements OnInit {

  private inactivityStarted = false;
  private warningShown = false;

  constructor(
    private inactivityService: InactivityService,
    private authService: AuthService,
    private alertService: AlertService
  ) {}

  ngOnInit(): void {
    if (this.authService.isLoggedIn()) {
      this.startInactivityControl();
    }

    this.authService.user$.subscribe(user => {
      if (user) {
        this.startInactivityControl();
      } else {
        this.stopInactivityControl();
      }
    });
  }

  private startInactivityControl(): void {
    if (this.inactivityStarted) return;

    this.inactivityStarted = true;

    this.inactivityService.start(
      () => this.showInactivityWarning(),
      () => this.handleTimeout()
    );
  }

  private stopInactivityControl(): void {
    this.inactivityStarted = false;
    this.warningShown = false;
    this.inactivityService.stop();
    this.alertService.close();
  }

  private async showInactivityWarning(): Promise<void> {
    if (this.warningShown) return;
    this.warningShown = true;

    const keepSession = await this.alertService.confirm(
      'Sesión a punto de caducar',
      'Tu sesión caducará en 5 minutos por inactividad. ¿Quieres seguir conectado?'
    );

    this.warningShown = false;

    if (!keepSession) {
      this.authService.logout();
    }
  }

  private handleTimeout(): void {
    this.alertService.close();
    this.authService.logout();
  }
}
