import { Injectable, NgZone } from '@angular/core';
import { fromEvent, merge, Subscription, timer } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class InactivityService {

  private readonly timeoutMs = 30  * 60 * 1000; // 30 minutos
  private readonly warningMs = this.timeoutMs - 5 * 60 * 1000; // Avisa 5 minutos antes de caducar

  private activitySub?: Subscription;
  private timerSub?: Subscription;
  private warningSub?: Subscription;

  constructor(private zone: NgZone) {}

  start(onWarning: () => void, onTimeout: () => void): void {
    this.zone.runOutsideAngular(() => {
      const activity$ = merge(
        fromEvent(window, 'mousemove'),
        fromEvent(window, 'keydown'),
        fromEvent(window, 'click'),
        fromEvent(window, 'scroll'),
        fromEvent(window, 'touchstart')
      );

      this.activitySub?.unsubscribe();
      this.activitySub = activity$.subscribe(() => {
        this.resetTimers(onWarning, onTimeout);
      });

      this.resetTimers(onWarning, onTimeout);
    });
  }

  stop(): void {
    this.activitySub?.unsubscribe();
    this.timerSub?.unsubscribe();
    this.warningSub?.unsubscribe();
  }

  private resetTimers(onWarning: () => void, onTimeout: () => void): void {
    this.timerSub?.unsubscribe();
    this.warningSub?.unsubscribe();

    // Aviso de inactividad
    this.warningSub = timer(this.warningMs).subscribe(() => {
      onWarning();
    });

    // Logout definitivo
    this.timerSub = timer(this.timeoutMs).subscribe(() => {
      onTimeout();
    });
  }
}
