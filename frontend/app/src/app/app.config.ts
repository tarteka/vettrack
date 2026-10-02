import { ApplicationConfig, importProvidersFrom, provideZoneChangeDetection } from '@angular/core';
import { provideRouter } from '@angular/router';
import { provideHttpClient, withInterceptors, withXhr } from '@angular/common/http';
import { LucideAngularModule } from 'lucide-angular';
import { LUCIDE_ICONS } from './shared/icons/lucide-icons';

import { routes} from './app.routes';
import { authInterceptor} from './core/interceptors/auth-interceptor';
import { LOCALE_ID, isDevMode } from '@angular/core';
import { provideServiceWorker } from '@angular/service-worker';

export const appConfig: ApplicationConfig = {
  providers: [
    provideZoneChangeDetection({ eventCoalescing: true }),
    provideRouter(routes),
    provideHttpClient(withXhr(), 
      withInterceptors([authInterceptor])
    ),
    importProvidersFrom(
      LucideAngularModule.pick(LUCIDE_ICONS)
    ),
    { provide: LOCALE_ID, useValue: 'es-ES' }, provideServiceWorker('ngsw-worker.js', {
            enabled: !isDevMode(),
            registrationStrategy: 'registerWhenStable:30000'
    }),
  ]
};

