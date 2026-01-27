import { Component, inject, PLATFORM_ID, OnInit } from '@angular/core';
import { CommonModule, isPlatformBrowser } from '@angular/common';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { PerfilComponent } from '../components/perfil/perfil.component';
import { formatearRol } from '../utils/formatear-rol';
import { environment } from '../../environments/environment';

interface EstadisticasModeradorData {
  usuarios_total: number;
  usuarios_jefes: number;
  usuarios_trabajadores: number;
  usuarios_baneados: number;
  usuarios_eliminados: number;
  equipos_total: number;
  proyectos_total: number;
  proyectos_planificacion: number;
  proyectos_en_proceso: number;
  proyectos_finalizados: number;
  proyectos_cancelados: number;
  baneos_activos: number;
  acciones_ultima_semana: number;
}

interface BaneoHistorial {
  id: number;
  usuario_dni: string;
  usuario_nombre: string;
  usuario_email: string;
  jefe_dni: string;
  jefe_nombre: string;
  motivo: string;
  fecha_baneo: string;
  fecha_desbaneo: string | null;
  activo: boolean;
}

interface ProyectoResumen {
  id: number;
  codigo: string;
  nombre: string;
  descripcion: string;
  jefe_nombre: string;
  cliente_nombre: string;
  estado: string;
  fecha_inicio: string;
  fecha_fin: string | null;
  horas_estimadas: number;
  precio_estimado: number;
}

@Component({
  selector: 'app-panel-moderador',
  standalone: true,
  imports: [CommonModule, PerfilComponent],
  templateUrl: './panel-moderador.html',
  styleUrls: ['./panel-moderador.css']
})
export class PanelModeradorComponent implements OnInit {
  private http = inject(HttpClient);
  private router = inject(Router);
  private platformId = inject(PLATFORM_ID);

  moderador: any = null;
  usuarioRol = '';
  estadisticas: EstadisticasModeradorData = {
    usuarios_total: 0,
    usuarios_jefes: 0,
    usuarios_trabajadores: 0,
    usuarios_baneados: 0,
    usuarios_eliminados: 0,
    equipos_total: 0,
    proyectos_total: 0,
    proyectos_planificacion: 0,
    proyectos_en_proceso: 0,
    proyectos_finalizados: 0,
    proyectos_cancelados: 0,
    baneos_activos: 0,
    acciones_ultima_semana: 0
  };
  historialBaneos: BaneoHistorial[] = [];
  proyectos: ProyectoResumen[] = [];
  
  vistaActual: 'dashboard' | 'baneos' | 'proyectos' | 'usuarios' | 'perfil' = 'dashboard';
  cargando = false;

  ngOnInit() {
    // Cargar datos del moderador desde localStorage
    if (isPlatformBrowser(this.platformId)) {
      const usuarioData = localStorage.getItem('usuario');
      if (usuarioData) {
        this.moderador = JSON.parse(usuarioData);
        this.usuarioRol = formatearRol(this.moderador.rol) || 'Moderador';
        // Cargar estadísticas desde el servidor
        this.cargarEstadisticasServidor();
      } else {
        this.router.navigate(['/login']);
      }
    }
  }

  logout() {
    if (isPlatformBrowser(this.platformId)) {
      localStorage.removeItem('usuario');
    }
    this.router.navigate(['/login']);
  }

  cargarEstadisticasServidor() {
    this.http.get<any>(`${environment.apiUrl}/api/moderador/estadisticas.php`)
      .subscribe({
        next: (response) => {
          if (response.success && response.data) {
            this.estadisticas = response.data;
          } else {
            // Fallback a datos del localStorage
            this.cargarEstadisticasLocal();
          }
        },
        error: () => {
          // Fallback a datos del localStorage
          this.cargarEstadisticasLocal();
        }
      });
  }

  cargarEstadisticasLocal() {
    // Las estadísticas ya vienen del login, pero podemos recargarlas
    if (this.moderador) {
      this.estadisticas = {
        usuarios_total: this.moderador.usuarios_total || 0,
        usuarios_jefes: this.moderador.usuarios_jefes || 0,
        usuarios_trabajadores: this.moderador.usuarios_trabajadores || 0,
        usuarios_baneados: this.moderador.usuarios_baneados || 0,
        usuarios_eliminados: this.moderador.usuarios_eliminados || 0,
        equipos_total: this.moderador.equipos_total || 0,
        proyectos_total: this.moderador.proyectos_total || 0,
        proyectos_planificacion: this.moderador.proyectos_planificacion || 0,
        proyectos_en_proceso: this.moderador.proyectos_en_proceso || 0,
        proyectos_finalizados: this.moderador.proyectos_finalizados || 0,
        proyectos_cancelados: this.moderador.proyectos_cancelados || 0,
        baneos_activos: this.moderador.baneos_activos || 0,
        acciones_ultima_semana: this.moderador.acciones_ultima_semana || 0
      };
    }
  }

  cambiarVista(vista: 'dashboard' | 'baneos' | 'proyectos' | 'usuarios' | 'perfil') {
    this.vistaActual = vista;
    
    if (vista === 'baneos' && this.historialBaneos.length === 0) {
      this.cargarHistorialBaneos();
    } else if (vista === 'proyectos' && this.proyectos.length === 0) {
      this.cargarProyectos();
    }
  }

  cargarHistorialBaneos() {
    this.cargando = true;
    this.http.get<any>(`${environment.apiUrl}/api/moderador/historial-baneos.php`)
      .subscribe({
        next: (response) => {
          this.cargando = false;
          if (response.success) {
            this.historialBaneos = response.data || [];
          }
        },
        error: (error) => {
          this.cargando = false;
          this.historialBaneos = [];
        }
      });
  }

  cargarProyectos() {
    this.cargando = true;
    this.http.get<any>(`${environment.apiUrl}/api/moderador/proyectos.php`)
      .subscribe({
        next: (response) => {
          this.cargando = false;
          if (response.success) {
            this.proyectos = response.data || [];
          }
        },
        error: (error) => {
          this.cargando = false;
          this.proyectos = [];
        }
      });
  }

  desbanearUsuario(baneoId: number) {
    if (!confirm('¿Desbanear a este usuario?')) return;

    const headers = this.getAuthHeaders();
    this.http.post<any>(`${environment.apiUrl}/api/moderador/desbanear.php`, { baneo_id: baneoId }, { headers })
      .subscribe({
        next: (response) => {
          if (response.success) {
            this.cargarHistorialBaneos(); // Recargar lista
            this.cargarEstadisticasServidor(); // Actualizar estadísticas
            alert('Usuario desbaneado exitosamente');
          } else {
            alert('Error: ' + (response.error || 'No se pudo desbanear al usuario'));
          }
        },
        error: (error) => {
          alert('Error al desbanear usuario');
        }
      });
  }

  private getAuthHeaders(): { [key: string]: string } {
    if (!isPlatformBrowser(this.platformId)) {
      return {};
    }
    const usuario = localStorage.getItem('usuario');
    if (usuario) {
      const userData = JSON.parse(usuario);
      return {
        'X-User-DNI': userData.dni || '',
        'X-User-Rol': userData.rol || '',
        'X-User-Nombre': userData.nombre || '',
        'X-User-Email': userData.email || ''
      };
    }
    return {};
  }
}
