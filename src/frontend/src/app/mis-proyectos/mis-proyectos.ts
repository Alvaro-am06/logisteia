import { Component, inject, OnInit, PLATFORM_ID } from '@angular/core';
import { CommonModule, isPlatformBrowser } from '@angular/common';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';

interface Presupuesto {
  id_presupuesto: number;
  numero_presupuesto: string;
  fecha_creacion: string;
  estado: string;
  total: number;
  validez_dias: number;
  notas: string;
}

interface DetallePresupuesto {
  id_linea: number;
  servicio_nombre: string;
  cantidad: number;
  preci: number;
  comentario: string;
}

@Component({
  selector: 'app-mis-proyectos',
  imports: [CommonModule],
  templateUrl: './mis-proyectos.html',
})
export class MisProyectos implements OnInit {
  private http = inject(HttpClient);
  private router = inject(Router);
  private platformId = inject(PLATFORM_ID);

  presupuestos: Presupuesto[] = [];
  loading: boolean = false;
  message: string = '';
  usuarioDni: string = '';
  usuarioNombre: string = '';

  // Modal
  mostrarModal: boolean = false;
  presupuestoSeleccionado: Presupuesto | null = null;
  detallesPresupuesto: DetallePresupuesto[] = [];
  cargandoDetalles: boolean = false;

  ngOnInit() {
    // Solo ejecutar en el navegador
    if (!isPlatformBrowser(this.platformId)) {
      return;
    }

    // Obtener datos del usuario
    const usuarioData = localStorage.getItem('usuario');
    if (!usuarioData) {
      this.router.navigate(['/login']);
      return;
    }

    const usuario = JSON.parse(usuarioData);
    this.usuarioDni = usuario.dni;
    this.usuarioNombre = usuario.nombre || 'Usuario';

    // Cargar presupuestos
    this.cargarPresupuestos();
  }

  cargarPresupuestos() {
    this.loading = true;
    this.http.get(`http://localhost/logisteia/src/www/api/mis-presupuestos.php?dni=${this.usuarioDni}`)
      .subscribe({
        next: (response: any) => {
          this.loading = false;
          if (response.success) {
            // Convertir total a número
            this.presupuestos = response.data.map((p: any) => ({
              ...p,
              total: parseFloat(p.total)
            }));
          } else {
            this.message = 'Error al cargar presupuestos';
          }
        },
        error: (error) => {
          this.loading = false;
          this.message = 'Error de conexión: ' + error.message;
        }
      });
  }

  verDetalle(presupuesto: Presupuesto) {
    this.presupuestoSeleccionado = presupuesto;
    this.mostrarModal = true;
    this.cargarDetallesPresupuesto(presupuesto.numero_presupuesto);
  }

  cargarDetallesPresupuesto(numeroPresupuesto: string) {
    this.cargandoDetalles = true;
    this.http.get(`http://localhost/logisteia/src/www/api/detalle-presupuesto.php?numero=${numeroPresupuesto}`)
      .subscribe({
        next: (response: any) => {
          this.cargandoDetalles = false;
          if (response.success) {
            this.detallesPresupuesto = response.data.map((d: any) => ({
              ...d,
              preci: parseFloat(d.preci),
              cantidad: parseInt(d.cantidad)
            }));
          }
        },
        error: (error) => {
          this.cargandoDetalles = false;
          console.error('Error al cargar detalles:', error);
        }
      });
  }

  cerrarModal() {
    this.mostrarModal = false;
    this.presupuestoSeleccionado = null;
    this.detallesPresupuesto = [];
  }

  crearNuevo() {
    this.router.navigate(['/presupuesto']);
  }

  volver() {
    this.router.navigate(['/panel-registrado']);
  }

  getEstadoClass(estado: string): string {
    switch (estado) {
      case 'borrador':
        return 'bg-gray-100 text-gray-700';
      case 'enviado':
        return 'bg-blue-100 text-blue-700';
      case 'aprobado':
        return 'bg-green-100 text-green-700';
      case 'rechazado':
        return 'bg-red-100 text-red-700';
      default:
        return 'bg-gray-100 text-gray-700';
    }
  }

  getEstadoTexto(estado: string): string {
    switch (estado) {
      case 'borrador':
        return 'Borrador';
      case 'enviado':
        return 'Enviado';
      case 'aprobado':
        return 'Aprobado';
      case 'rechazado':
        return 'Rechazado';
      default:
        return estado;
    }
  }

  contarPorEstado(estado: string): number {
    return this.presupuestos.filter(p => p.estado === estado).length;
  }
}
