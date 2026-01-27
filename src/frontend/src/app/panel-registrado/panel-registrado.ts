import { Component, inject, OnInit, PLATFORM_ID } from '@angular/core';
import { Router } from '@angular/router';
import { CommonModule, isPlatformBrowser } from '@angular/common';
import { SidebarComponent } from '../components/sidebar/sidebar.component';
import { EquipoTrabajadorService } from '../services/equipo-trabajador.service';

@Component({
  selector: 'app-panel-registrado',
  imports: [CommonModule, SidebarComponent],
  templateUrl: './panel-registrado.html',
})
export class PanelRegistrado implements OnInit {
  private router = inject(Router);
  private platformId = inject(PLATFORM_ID);
  private equipoService = inject(EquipoTrabajadorService);

  // Datos del usuario registrado
  nombreUsuario = 'Usuario Registrado';
  usuarioRol: 'jefe_equipo' | 'trabajador' | 'moderador' | 'admin' = 'trabajador';
  equipoNombre = '';
  jefeNombre = '';
  rolProyecto = '';
  proyectosCreados = 0;
  proyectosCompletados = 0;

  ngOnInit() {
    // Solo ejecutar en el navegador
    if (!isPlatformBrowser(this.platformId)) {
      return;
    }

    // Cargar datos del usuario desde localStorage
    const usuarioData = localStorage.getItem('usuario');
    if (usuarioData) {
      const usuario = JSON.parse(usuarioData);
      this.nombreUsuario = usuario.nombre || 'Usuario Registrado';
      this.usuarioRol = this.validarRol(usuario.rol);
      this.proyectosCreados = usuario.proyectos_creados || 0;
      this.proyectosCompletados = usuario.proyectos_completados || 0;
    }

    // Cargar información del equipo desde el backend solo si es trabajador
    if (this.usuarioRol === 'trabajador') {
      this.cargarEquipo();
    }
  }

  cargarEquipo() {
    this.equipoService.getMiEquipo().subscribe({
      next: (response) => {
        if (response.success && response.data) {
          this.equipoNombre = response.data.equipo_nombre;
          this.jefeNombre = response.data.jefe_nombre;
          this.rolProyecto = response.data.rol_proyecto;
        }
      },
      error: (error) => {
      }
    });
  }

  cerrarSesion() {
    if (confirm('¿Está seguro de que desea cerrar sesión?')) {
      // Solo ejecutar en el navegador
      if (isPlatformBrowser(this.platformId)) {
        // Limpiar datos de sesión
        localStorage.removeItem('usuario');
        localStorage.removeItem('token');
      }
      this.router.navigate(['/login']);
    }
  }

  private validarRol(rol: any): 'jefe_equipo' | 'trabajador' | 'moderador' | 'admin' {
    const rolesValidos: ('jefe_equipo' | 'trabajador' | 'moderador' | 'admin')[] = ['jefe_equipo', 'trabajador', 'moderador', 'admin'];
    return rolesValidos.includes(rol) ? rol : 'trabajador';
  }
}