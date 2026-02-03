import { Component, OnInit, inject, PLATFORM_ID, ChangeDetectorRef } from '@angular/core';
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
  cliente_email?: string;
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
  private cdr = inject(ChangeDetectorRef);
  
  presupuestos: Presupuesto[] = [];
  loading = false;
  error = '';
  usuarioDni = '';
  usuarioRol = '';
  nombreUsuario = '';
  mostrarModalDetalle = false;
  presupuestoSeleccionado: Presupuesto | null = null;

  ngOnInit() {
    if (!isPlatformBrowser(this.platformId)) {
      return;
    }
    
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

  cargarPresupuestos() {
    this.loading = true;
    this.error = '';

    this.http.get<any>(`${environment.apiUrl}/api/mis-presupuestos.php?dni=${this.usuarioDni}`)
      .subscribe({
        next: (response) => {
          this.loading = false;
          if (response && response.success) {
            this.presupuestos = response.data || [];
          } else {
            this.error = response.error || 'Error al cargar presupuestos';
          }
          this.cdr.markForCheck();
        },
        error: (err) => {
          this.loading = false;
          this.error = 'Error de conexión al cargar presupuestos';
          this.cdr.markForCheck();
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
    console.log('CLICK enviarPDFPresupuesto', presupuesto);
    if (!presupuesto) {
      console.log('No hay presupuesto seleccionado');
      return;
    }
    
    console.log('Enviando petición HTTP...');
    this.http.post(`${environment.apiUrl}/api/enviar-presupuesto-email.php`, {
      numero_presupuesto: presupuesto.numero_presupuesto,
      usuario_dni: this.usuarioDni
    }).subscribe({
      next: (response: any) => {
        console.log('Respuesta recibida:', response);
        if (response.success) {
          const destinatarios = response.destinatarios?.join(', ') || 'destinatarios';
          alert(`Email enviado correctamente\n\nPresupuesto: ${presupuesto.numero_presupuesto}\nEnviado a: ${destinatarios}`);
        } else {
          alert('Error al enviar: ' + (response.error || 'Error desconocido'));
        }
      },
      error: (err) => {
        console.error('Error en petición:', err);
        alert('Error de conexión al enviar presupuesto');
      }
    });
  }

  eliminarPresupuesto(presupuesto: Presupuesto | null) {
    console.log('CLICK eliminarPresupuesto', presupuesto);
    if (!presupuesto) {
      console.log('No hay presupuesto para eliminar');
      return;
    }
    
    console.log('Enviando petición de eliminación...');
    this.http.post(`${environment.apiUrl}/api/eliminar-presupuesto.php`, {
      numero_presupuesto: presupuesto.numero_presupuesto
    }).subscribe({
      next: (response: any) => {
        console.log('Respuesta eliminación:', response);
        if (response.success) {
          alert(`Presupuesto ${presupuesto.numero_presupuesto} eliminado correctamente`);
          this.cerrarModalDetalle();
          this.cargarPresupuestos();
        } else {
          alert('Error al eliminar: ' + (response.error || 'Error desconocido'));
        }
      },
      error: (err) => {
        console.error('Error al eliminar:', err);
        alert('Error de conexión al eliminar presupuesto');
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
