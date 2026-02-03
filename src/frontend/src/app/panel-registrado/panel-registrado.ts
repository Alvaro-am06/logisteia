import { Component, inject, OnInit, PLATFORM_ID } from '@angular/core';
import { Router } from '@angular/router';
import { CommonModule, isPlatformBrowser } from '@angular/common';
import { SidebarComponent } from '../components/sidebar/sidebar.component';
import { EquipoTrabajadorService } from '../services/equipo-trabajador.service';
import { ProyectoService } from '../services/proyecto.service';
import { HttpClient } from '@angular/common/http';

@Component({
  selector: 'app-panel-registrado',
  imports: [CommonModule, SidebarComponent],
  templateUrl: './panel-registrado.html',
})
export class PanelRegistrado implements OnInit {
  private router = inject(Router);
  private platformId = inject(PLATFORM_ID);
  private equipoService = inject(EquipoTrabajadorService);
  private proyectoService = inject(ProyectoService);
  private http = inject(HttpClient);

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
    }

    // Cargar estadísticas de proyectos desde el backend
    this.cargarEstadisticas();

    // Cargar información del equipo desde el backend solo si es trabajador
    if (this.usuarioRol === 'trabajador') {
      this.cargarEquipo();
    }
  }

  cargarEstadisticas() {
    this.proyectoService.getProyectos().subscribe({
      next: (response) => {
        if (response && response.success) {
          const proyectos = response.proyectos || [];
          this.proyectosCreados = proyectos.length;
          this.proyectosCompletados = proyectos.filter(p => p.estado === 'finalizado').length;
        }
      },
      error: (error) => {
        console.error('Error al cargar estadísticas:', error);
      }
    });
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
    // Limpiar datos de sesión
    if (isPlatformBrowser(this.platformId)) {
      localStorage.removeItem('usuario');
      localStorage.removeItem('token');
    }
    this.router.navigate(['/login']);
  }

  private validarRol(rol: any): 'jefe_equipo' | 'trabajador' | 'moderador' | 'admin' {
    const rolesValidos: ('jefe_equipo' | 'trabajador' | 'moderador' | 'admin')[] = ['jefe_equipo', 'trabajador', 'moderador', 'admin'];
    return rolesValidos.includes(rol) ? rol : 'trabajador';
  }
}