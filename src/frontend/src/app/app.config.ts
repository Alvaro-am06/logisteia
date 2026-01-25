import { ApplicationConfig, provideBrowserGlobalErrorListeners, provideZoneChangeDetection } from '@angular/core';
import { provideRouter } from '@angular/router';

import { routes } from './app.routes';
<<<<<<< HEAD
=======
import { provideClientHydration, withEventReplay } from '@angular/platform-browser';
>>>>>>> sprint1-alvaro

export const appConfig: ApplicationConfig = {
  providers: [
    provideBrowserGlobalErrorListeners(),
    provideZoneChangeDetection({ eventCoalescing: true }),
<<<<<<< HEAD
    provideRouter(routes)
=======
    provideRouter(routes), provideClientHydration(withEventReplay())
>>>>>>> sprint1-alvaro
  ]
};
