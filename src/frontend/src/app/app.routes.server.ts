import { RenderMode, ServerRoute } from '@angular/ssr';

export const serverRoutes: ServerRoute[] = [
  // Rutas con parámetros dinámicos - usar SSR
  {
    path: 'usuarios/:dni',
    renderMode: RenderMode.Server
  },
  // Rutas que requieren datos del backend - usar SSR
  {
    path: 'panel-admin',
    renderMode: RenderMode.Server
  },
  {
    path: 'panel-registrado',
    renderMode: RenderMode.Server
  },
  {
    path: 'panel-moderador',
    renderMode: RenderMode.Server
  },
  {
    path: 'panel-jefe-equipo',
    renderMode: RenderMode.Server
  },
  {
    path: 'mi-equipo',
    renderMode: RenderMode.Server
  },
  {
    path: 'usuarios',
    renderMode: RenderMode.Server
  },
  {
    path: 'mis-proyectos',
    renderMode: RenderMode.Server
  },
  {
    path: 'clientes',
    renderMode: RenderMode.Server
  },
  {
    path: 'perfil',
    renderMode: RenderMode.Server
  },
  // Rutas estáticas - prerendering
  {
    path: '**',
    renderMode: RenderMode.Prerender
  }
];
