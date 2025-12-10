import { Routes } from '@angular/router';
import { App } from './app';
import { PanelAdmin } from './panel-admin/panel-admin';
import { Plantilla } from './plantilla/plantilla';
import { Usuarios } from './usuarios/usuarios';

export const routes: Routes = [
  { path: '', redirectTo: '/login', pathMatch: 'full' },
  { path: 'login', component: App },
  { path: 'panel-admin', component: PanelAdmin },
  { path: 'plantilla', component: Plantilla },
  { path: 'usuarios', component: Usuarios },
];
