import { Component, OnInit, inject, PLATFORM_ID } from '@angular/core';
import { CommonModule, isPlatformBrowser } from '@angular/common';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { SidebarComponent } from '../components/sidebar/sidebar.component';
import { environment } from '../../environments/environment';

interface Presupuesto {
  numero_presupuesto: string;
  usuario_dni: string;
  nombre_proyecto: string;
  cliente_nombre: string;
  total: number;
  estado: string;
  fecha_creacion: string;
}

@Component({
  selector: 'app-presupuestos',
  standalone: true,
  imports: [CommonModule, SidebarComponent],
  templateUrl: './presupuestos.html',
})
export class PresupuestosComponent implements OnInit {
  private http = inject(HttpClient);
  private router = inject(Router);
  private platformId = inject(PLATFORM_ID);
  
  presupuestos: Presupuesto[] = [];
  loading = false;
  error = '';
  usuarioDni = '';
  usuarioRol = '';
  nombreUsuario = '';
  mostrarModalDetalle = false;
  presupuestoSeleccionado: Presupuesto | null = null;

  ngOnInit() {
    if (isPlatformBrowser(this.platformId)) {
      const usuarioStr = localStorage.getItem('usuario');
      if (usuarioStr) {
        const usuario = JSON.parse(usuarioStr);
        this.usuarioDni = usuario.dni;
        this.usuarioRol = usuario.rol;
        this.nombreUsuario = usuario.nombre || 'Usuario';
        
        // Solo jefes de equipo pueden ver presupuestos
        if (this.usuarioRol === 'jefe_equipo') {
          this.cargarPresupuestos();
        } else {
          this.error = 'No tienes permisos para ver presupuestos';
        }
      }
    }
  }

  cargarPresupuestos() {
    this.loading = true;
    this.error = '';

    this.http.get<any>(`${environment.apiUrl}/api/mis-presupuestos-wizard.php?dni=${this.usuarioDni}`)
      .subscribe({
        next: (response) => {
          this.loading = false;
          if (response && response.success) {
            this.presupuestos = response.data || [];
          } else {
            this.error = response.error || 'Error al cargar presupuestos';
          }
        },
        error: (err) => {
          this.loading = false;
          this.error = 'Error de conexión al cargar presupuestos';
          console.error('Error:', err);
        }
      });
  }

  formatearRol(rol: string): string {
    const roles: { [key: string]: string } = {
      'jefe_equipo': 'Jefe de Equipo',
      'trabajador': 'Trabajador',
      'moderador': 'Moderador'
    };
    return roles[rol] || rol;
  }

  verDetallePresupuesto(presupuesto: Presupuesto) {
    this.presupuestoSeleccionado = presupuesto;
    this.mostrarModalDetalle = true;
  }

  cerrarModalDetalle() {
    this.mostrarModalDetalle = false;
    this.presupuestoSeleccionado = null;
  }

  imprimirPDFPresupuesto(presupuesto: Presupuesto | null) {
    if (!presupuesto) return;
    window.open(`${environment.apiUrl}/api/exportar-presupuesto-pdf.php?numero=${presupuesto.numero_presupuesto}`, '_blank');
  }

  enviarPDFPresupuesto(presupuesto: Presupuesto | null) {
    if (!presupuesto) return;
    alert('Funcionalidad de enviar PDF por correo en desarrollo');
  }

  finalizarPresupuesto(presupuesto: Presupuesto | null) {
    if (!presupuesto) return;
    if (!confirm(`¿Estás seguro de marcar el presupuesto ${presupuesto.numero_presupuesto} como finalizado?`)) {
      return;
    }
    alert('Funcionalidad de finalizar presupuesto en desarrollo');
  }

  eliminarPresupuesto(presupuesto: Presupuesto | null) {
    if (!presupuesto) return;
    if (!confirm(`¿Estás seguro de eliminar el presupuesto ${presupuesto.numero_presupuesto}?`)) {
      return;
    }

    this.http.post(`${environment.apiUrl}/api/eliminar-presupuesto.php`, {
      numero_presupuesto: presupuesto.numero_presupuesto
    }).subscribe({
      next: (response: any) => {
        if (response.success) {
          alert('Presupuesto eliminado correctamente');
          this.cerrarModalDetalle();
          this.cargarPresupuestos();
        } else {
          alert('Error al eliminar: ' + (response.error || 'Error desconocido'));
        }
      },
      error: (err) => {
        alert('Error de conexión al eliminar presupuesto');
        console.error('Error:', err);
      }
    });
  }

  cerrarSesion() {
    if (isPlatformBrowser(this.platformId)) {
      localStorage.removeItem('usuario');
      this.router.navigate(['/login']);
    }
  }
}
