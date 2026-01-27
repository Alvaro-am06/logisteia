import { Component, inject, OnInit, PLATFORM_ID, ChangeDetectorRef } from '@angular/core';
import { CommonModule, isPlatformBrowser } from '@angular/common';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { formatearRol } from '../utils/formatear-rol';
import { getEstadoClass, getEstadoTexto } from '../utils/estado-utils';
import { environment } from '../../environments/environment';
import { ProyectoService, Proyecto, Trabajador, MiembroEquipo } from '../services/proyecto.service';
import { EquipoService } from '../services/equipo.service';
import { FormsModule } from '@angular/forms';
import { SidebarComponent } from '../components/sidebar/sidebar.component';
interface Cliente {
  id: number;
  nombre: string;
  email: string;
  telefono?: string;
}

@Component({
  selector: 'app-mis-proyectos',
  standalone: true,
  imports: [CommonModule, FormsModule, SidebarComponent],
  templateUrl: './mis-proyectos.html',
})
export class MisProyectos implements OnInit {
  private http = inject(HttpClient);
  private router = inject(Router);
  private platformId = inject(PLATFORM_ID);
  private cdr = inject(ChangeDetectorRef);
  private proyectoService = inject(ProyectoService);
  private equipoService = inject(EquipoService);

  proyectos: Proyecto[] = [];
  loading: boolean = false;
  message: string = '';
  usuarioDni: string = '';
  usuarioNombre: string = '';
  usuarioRol: string = '';

  // Modal de crear proyecto
  mostrarModalCrear: boolean = false;
  nuevoProyecto: Partial<Proyecto> = {
    nombre: '',
    descripcion: '',
    tecnologias: '',
    notas: ''
  };
  clientes: Cliente[] = [];
  equipos: any[] = [];
  trabajadoresSeleccionados: Trabajador[] = [];
  miembrosDisponibles: MiembroEquipo[] = [];
  cargandoMiembros: boolean = false;

  // Modal de detalle proyecto
  mostrarModalDetalle: boolean = false;
  proyectoSeleccionado: Proyecto | null = null;
  trabajadoresProyecto: Trabajador[] = [];
  mostrarAsignarTrabajadores: boolean = false;
  miembrosDisponiblesDetalle: MiembroEquipo[] = [];
  
  // Helper para tecnologías
  getTecnologiasArray(proyecto: Proyecto): string[] {
    if (!proyecto.tecnologias) return [];
    if (Array.isArray(proyecto.tecnologias)) return proyecto.tecnologias;
    if (typeof proyecto.tecnologias === 'string') {
      try {
        const parsed = JSON.parse(proyecto.tecnologias);
        return Array.isArray(parsed) ? parsed : [];
      } catch {
        return proyecto.tecnologias.split(',').map(t => t.trim());
      }
    }
    return [];
  }

  // Exponer utilidades para el template
  formatearRol = formatearRol;
  getEstadoClass = getEstadoClass;
  getEstadoTexto = getEstadoTexto;

  ngOnInit() {
    // Solo ejecutar en el navegador
    if (!isPlatformBrowser(this.platformId)) {
      return;
    }

    // Obtener datos del usuario
    const usuarioData = localStorage.getItem('usuario');
    // DEBUG: Si no hay usuario, usar datos de prueba
    let usuario;
    if (!usuarioData) {
      usuario = {
        dni: '99999999Z',
        nombre: 'Usuario Prueba',
        rol: 'jefe_equipo',
        email: 'prueba@example.com'
      };
      localStorage.setItem('usuario', JSON.stringify(usuario));
    } else {
      usuario = JSON.parse(usuarioData);
    }
    
    this.usuarioDni = usuario.dni;
    this.usuarioNombre = usuario.nombre || 'Usuario';
    this.usuarioRol = usuario.rol || '';

    // Cargar proyectos
    this.cargarProyectos();

    // Si es jefe de equipo, cargar datos adicionales para crear proyectos
    if (this.usuarioRol === 'jefe_equipo') {
      this.cargarDatosCreacion();
    }
  }

  cargarProyectos() {
    this.loading = true;
    this.proyectoService.getProyectos().subscribe({
      next: (response) => {
        this.loading = false;
        if (response && response.success) {
          this.proyectos = response.proyectos || [];
          // Actualizar localStorage para el panel
          localStorage.setItem('proyectosTotal', this.proyectos.length.toString());
        } else {
          this.message = 'Error al cargar proyectos';
          this.proyectos = [];
        }
      },
      error: (error) => {
        this.loading = false;
        console.error('Error cargando proyectos:', error);
        this.message = 'Error de conexión al cargar proyectos';
        this.proyectos = [];
      }
    });
  }

  cargarDatosCreacion() {
    // Cargar clientes
    this.http.get(`${environment.apiUrl}/api/clientes.php`).subscribe({
      next: (response: any) => {
        if (response && response.success) {
          this.clientes = response.clientes || [];
        }
      },
      error: (error) => {
        console.error('Error cargando clientes:', error);
        this.clientes = [];
      }
    });

    // Cargar equipos del jefe
    this.equipoService.getEquiposJefe(this.usuarioDni).subscribe({
      next: (response: any) => {
        if (response && response.success) {
          this.equipos = response.equipos || [];
        }
      },
      error: (error) => {
        console.error('Error cargando equipos:', error);
        this.equipos = [];
      }
    });
  }

  // Crear proyecto
  abrirModalCrear() {
    this.mostrarModalCrear = true;
    this.nuevoProyecto = {
      nombre: '',
      descripcion: '',
      tecnologias: '',
      notas: ''
    };
    this.trabajadoresSeleccionados = [];
    this.miembrosDisponibles = [];
  }

  cerrarModalCrear() {
    this.mostrarModalCrear = false;
    this.nuevoProyecto = {};
    this.trabajadoresSeleccionados = [];
    this.miembrosDisponibles = [];
  }

  onEquipoChange() {
    if (this.nuevoProyecto.equipo_id) {
      this.cargarMiembrosDisponibles();
    } else {
      this.miembrosDisponibles = [];
    }
  }

  cargarMiembrosDisponibles() {
    if (!this.nuevoProyecto.equipo_id) return;

    this.cargandoMiembros = true;
    this.proyectoService.getMiembrosDisponibles(this.nuevoProyecto.equipo_id).subscribe({
      next: (response) => {
        this.cargandoMiembros = false;
        if (response.success) {
          this.miembrosDisponibles = response.miembros;
        }
      },
      error: (error: any) => {
        this.cargandoMiembros = false;
        console.error('Error cargando miembros:', error);
      }
    });
  }

  agregarTrabajador(trabajador: MiembroEquipo) {
    if (!this.trabajadoresSeleccionados.find(t => t.dni === trabajador.dni)) {
      this.trabajadoresSeleccionados.push({
        dni: trabajador.dni,
        nombre: trabajador.nombre,
        email: trabajador.email,
        rol: trabajador.rol,
        rol_asignado: 'trabajador' // Por defecto
      });
    }
  }

  removerTrabajador(dni: string) {
    this.trabajadoresSeleccionados = this.trabajadoresSeleccionados.filter(t => t.dni !== dni);
  }

  crearProyecto() {
    if (!this.nuevoProyecto.nombre?.trim()) {
      this.message = 'El nombre del proyecto es obligatorio';
      return;
    }

    const proyectoData = {
      ...this.nuevoProyecto,
      trabajadores: this.trabajadoresSeleccionados
    };

    this.proyectoService.crearProyecto(proyectoData).subscribe({
      next: (response) => {
        if (response.success) {
          this.message = '✅ Proyecto creado exitosamente';
          this.cerrarModalCrear();
          this.cargarProyectos();
        } else {
          this.message = '❌ Error al crear proyecto';
        }
      },
      error: (error) => {
        this.message = '❌ Error de conexión al crear proyecto';
      }
    });
  }

  // Ver detalle de proyecto
  verDetalleProyecto(proyecto: Proyecto) {
    this.proyectoSeleccionado = proyecto;
    this.mostrarModalDetalle = true;
    this.trabajadoresProyecto = []; // Inicializar como array vacío
    this.mostrarAsignarTrabajadores = false;
    this.miembrosDisponiblesDetalle = [];

    // Cargar trabajadores asignados
    this.proyectoService.getTrabajadoresProyecto(proyecto.id).subscribe({
      next: (response) => {
        if (response.success) {
          this.trabajadoresProyecto = response.trabajadores || [];
        }
      },
      error: (error) => {
        console.error('Error cargando trabajadores:', error);
        this.trabajadoresProyecto = []; // Asegurar array vacío en error
      }
    });

    // Cargar miembros del equipo disponibles
    if (proyecto.equipo_id) {
      this.equipoService.getMiembrosEquipo(proyecto.equipo_id).subscribe({
        next: (response) => {
          if (response.success) {
            // Filtrar miembros que ya están asignados
            const trabajadoresDnis = this.trabajadoresProyecto.map(t => t.dni);
            this.miembrosDisponiblesDetalle = (response.miembros || [])
              .filter(m => !trabajadoresDnis.includes(m.dni));
          }
        },
        error: (error) => {
          console.error('Error cargando miembros del equipo:', error);
        }
      });
    }
  }

  agregarTrabajadorDetalle(miembro: MiembroEquipo) {
    if (!this.proyectoSeleccionado) return;

    this.proyectoService.asignarTrabajadores(this.proyectoSeleccionado.id, [miembro.dni]).subscribe({
      next: (response) => {
        if (response.success) {
          this.message = `✅ ${miembro.nombre} agregado al proyecto`;
          // Recargar trabajadores del proyecto
          this.verDetalleProyecto(this.proyectoSeleccionado!);
        } else {
          this.message = '❌ Error al agregar trabajador: ' + (response.error || 'Error desconocido');
        }
      },
      error: (error) => {
        this.message = '❌ Error de conexión al agregar trabajador';
        console.error('Error:', error);
      }
    });
  }

  cerrarModalDetalle() {
    this.mostrarModalDetalle = false;
    this.proyectoSeleccionado = null;
    this.trabajadoresProyecto = [];
    this.mostrarAsignarTrabajadores = false;
    this.miembrosDisponiblesDetalle = [];
  }

  // Eliminar proyecto
  eliminarProyecto(proyecto: Proyecto | null) {
    if (!proyecto) return;
    
    if (!confirm(`¿Estás seguro de eliminar el proyecto "${proyecto.nombre}"? Esta acción no se puede deshacer.`)) {
      return;
    }

    this.proyectoService.eliminarProyecto(proyecto.id).subscribe({
      next: (response) => {
        if (response.success) {
          this.message = '✅ Proyecto eliminado correctamente';
          this.cerrarModalDetalle();
          this.cargarProyectos();
        } else {
          this.message = '❌ Error al eliminar proyecto: ' + (response.error || 'Error desconocido');
        }
      },
      error: (error) => {
        this.message = '❌ Error de conexión al eliminar proyecto';
        console.error('Error:', error);
      }
    });
  }

  // Enviar PDF del proyecto
  enviarPDFProyecto(proyecto: Proyecto | null) {
    if (!proyecto) return;
    
    // Abrir PDF en nueva pestaña
    window.open(`${environment.apiUrl}/api/exportar-proyecto-pdf.php?proyecto_id=${proyecto.id}`, '_blank');
  }

  // Finalizar proyecto
  finalizarProyecto(proyecto: Proyecto | null) {
    if (!proyecto) return;
    
    if (!confirm(`¿Estás seguro de finalizar el proyecto "${proyecto.nombre}"?`)) {
      return;
    }

    this.proyectoService.cambiarEstadoProyecto(proyecto.id, 'finalizado').subscribe({
      next: (response) => {
        if (response.success) {
          this.message = '✅ Proyecto finalizado correctamente.';
          if (this.proyectoSeleccionado) {
            this.proyectoSeleccionado.estado = 'finalizado';
          }
          this.cerrarModalDetalle();
          this.cargarProyectos();
        } else {
          this.message = '❌ Error al finalizar proyecto: ' + (response.error || 'Error desconocido');
        }
      },
      error: (error) => {
        this.message = '❌ Error de conexión al finalizar proyecto';
        console.error('Error:', error);
      }
    });
  }

  crearNuevo() {
    if (this.usuarioRol === 'jefe_equipo') {
      // Redirigir al wizard de presupuesto para crear un nuevo proyecto
      this.router.navigate(['/presupuesto']);
    } else {
      // Para trabajadores, podría redirigir a otra funcionalidad
      this.message = 'Los trabajadores no pueden crear proyectos directamente';
    }
  }

  volver() {
    // Detectar el rol del usuario y redirigir al panel apropiado
    if (isPlatformBrowser(this.platformId)) {
      const usuarioData = localStorage.getItem('usuario');
      if (usuarioData) {
        const usuario = JSON.parse(usuarioData);
        if (usuario.rol === 'moderador') {
          this.router.navigate(['/panel-moderador']);
        } else if (usuario.rol === 'administrador') {
          this.router.navigate(['/panel-admin']);
        } else if (usuario.rol === 'jefe_equipo') {
          this.router.navigate(['/panel-jefe-equipo']);
        } else {
          this.router.navigate(['/panel-registrado']);
        }
      } else {
        this.router.navigate(['/login']);
      }
    }
  }

  cerrarSesion() {
    // Limpiar datos de sesión
    if (isPlatformBrowser(this.platformId)) {
      localStorage.removeItem('usuario');
    }
    this.router.navigate(['/']);
  }
}
