import { Component, inject, PLATFORM_ID } from '@angular/core';
import { Router, RouterLink, RouterLinkActive } from '@angular/router';
import { CommonModule, isPlatformBrowser } from '@angular/common';

@Component({
  selector: 'app-panel-jefe-equipo',
  imports: [CommonModule, RouterLink, RouterLinkActive],
  templateUrl: './panel-jefe-equipo.html',
})
export class PanelJefeEquipo {
  private router = inject(Router);
  private platformId = inject(PLATFORM_ID);

  // Datos del jefe de equipo
  nombreUsuario = 'Jefe de equipo';
  trabajadoresCount = 0;
  equipoNombre = '';
  proyectosTotal = 0;
  proyectosEnProceso = 0;
  proyectosFinalizados = 0;

  ngOnInit() {
    // Solo ejecutar en el navegador
    if (!isPlatformBrowser(this.platformId)) {
      return;
    }

    // Cargar datos del usuario desde localStorage
    const usuarioData = localStorage.getItem('usuario');
    if (usuarioData) {
      const usuario = JSON.parse(usuarioData);
      this.nombreUsuario = usuario.nombre || 'Jefe de equipo';
      this.trabajadoresCount = usuario.miembros_count || 0;
      this.equipoNombre = usuario.equipo_nombre || 'Mi Equipo';
      this.proyectosTotal = usuario.proyectos_total || 0;
      this.proyectosEnProceso = usuario.proyectos_en_proceso || 0;
      this.proyectosFinalizados = usuario.proyectos_finalizados || 0;
    }
  }

  cerrarSesion() {
    // Solo ejecutar en el navegador
    if (isPlatformBrowser(this.platformId)) {
      // Limpiar datos de sesión
      localStorage.removeItem('usuario');
    }
    this.router.navigate(['/']);
  }
}
