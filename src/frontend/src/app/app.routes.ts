import { Routes } from '@angular/router';
import { Login } from './login/login';
import { PanelAdmin } from './panel-admin/panel-admin';
import { PanelRegistrado } from './panel-registrado/panel-registrado';
import { PanelModeradorComponent } from './panel-moderador/panel-moderador';
import { PanelJefeEquipo } from './panel-jefe-equipo/panel-jefe-equipo';
import { MiEquipo } from './mi-equipo/mi-equipo';
import { Plantilla } from './plantilla/plantilla';
import { Usuarios } from './usuarios/usuarios';
import { PresupuestoWizard } from './presupuesto/presupuesto-wizard';
import { PresupuestosComponent } from './presupuestos/presupuestos';
import { MisProyectos } from './mis-proyectos/mis-proyectos';
import { RegistrarClienteComponent } from './components/registrar-cliente/registrar-cliente.component';
import { UsuarioDetalleComponent } from './components/usuario-detalle/usuario-detalle.component';
import { ClientesComponent } from './components/clientes/clientes.component';
import { PerfilComponent } from './components/perfil/perfil.component';
import { CompletarRegistro } from './completar-registro/completar-registro';
import { AuthGuard } from './guards/auth.guard';

export const routes: Routes = [
  { path: '', redirectTo: '/login', pathMatch: 'full' },
  { path: 'login', component: Login },
  { path: 'completar-registro', component: CompletarRegistro },
  { path: 'panel-admin', component: PanelAdmin, canActivate: [AuthGuard] },
  { path: 'panel-registrado', component: PanelRegistrado, canActivate: [AuthGuard] },
  { path: 'panel-moderador', component: PanelModeradorComponent, canActivate: [AuthGuard] },
  { path: 'panel-jefe-equipo', component: PanelJefeEquipo, canActivate: [AuthGuard] },
  { path: 'mi-equipo', component: MiEquipo, canActivate: [AuthGuard] },
  { path: 'plantilla', component: Plantilla, canActivate: [AuthGuard] },
  { path: 'usuarios', component: Usuarios, canActivate: [AuthGuard] },
  { path: 'usuarios/:dni', component: UsuarioDetalleComponent, canActivate: [AuthGuard] },
  { path: 'presupuesto', component: PresupuestoWizard, canActivate: [AuthGuard] },
  { path: 'mis-presupuestos', component: PresupuestosComponent, canActivate: [AuthGuard] },
  { path: 'mis-proyectos', component: MisProyectos, canActivate: [AuthGuard] },
  { path: 'registrar-cliente', component: RegistrarClienteComponent, canActivate: [AuthGuard] },
  { path: 'clientes', component: ClientesComponent, canActivate: [AuthGuard] },
  { path: 'perfil', component: PerfilComponent, canActivate: [AuthGuard] }
];
