import { Routes } from '@angular/router';
import { Login } from './login/login';
import { PanelAdmin } from './panel-admin/panel-admin';
import { PanelRegistrado } from './panel-registrado/panel-registrado';
import { Plantilla } from './plantilla/plantilla';
import { Usuarios } from './usuarios/usuarios';

export const routes: Routes = [
  { path: '', redirectTo: '/login', pathMatch: 'full' },
  { path: 'login', component: Login },
  { path: 'panel-admin', component: PanelAdmin },
  { path: 'panel-registrado', component: PanelRegistrado },
  { path: 'plantilla', component: Plantilla },
  { path: 'usuarios', component: Usuarios },
];
