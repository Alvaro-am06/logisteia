import { Routes } from '@angular/router';
import { Login } from './login/login';
import { PanelAdmin } from './panel-admin/panel-admin';
import { PanelRegistrado } from './panel-registrado/panel-registrado';
import { PanelModeradorComponent } from './panel-moderador/panel-moderador';
import { PanelJefeEquipo } from './panel-jefe-equipo/panel-jefe-equipo';
import { Plantilla } from './plantilla/plantilla';
import { Usuarios } from './usuarios/usuarios';
import { Presupuesto } from './presupuesto/presupuesto';
import { MisProyectos } from './mis-proyectos/mis-proyectos';
import { RegistrarClienteComponent } from './components/registrar-cliente/registrar-cliente.component';
import { UsuarioDetalleComponent } from './components/usuario-detalle/usuario-detalle.component';
import { ClientesComponent } from './components/clientes/clientes.component';

export const routes: Routes = [
  { path: '', redirectTo: '/login', pathMatch: 'full' },
  { path: 'login', component: Login },
  { path: 'panel-admin', component: PanelAdmin },
  { path: 'panel-registrado', component: PanelRegistrado },
  { path: 'panel-moderador', component: PanelModeradorComponent },
  { path: 'panel-jefe-equipo', component: PanelJefeEquipo },
  { path: 'plantilla', component: Plantilla },
  { path: 'usuarios', component: Usuarios },
  { path: 'usuarios/:dni', component: UsuarioDetalleComponent },
  { path: 'presupuesto', component: Presupuesto },
  { path: 'mis-proyectos', component: MisProyectos },
  { path: 'registrar-cliente', component: RegistrarClienteComponent },
  { path: 'clientes', component: ClientesComponent },
];
