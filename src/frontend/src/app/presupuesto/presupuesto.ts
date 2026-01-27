import { Component, inject, OnInit, PLATFORM_ID } from '@angular/core';
import { CommonModule, isPlatformBrowser } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { environment } from '../../environments/environment';

interface Servicio {
  nombre: string;
  precio_base: number;
  descripcion: string;
  categoria_nombre: string;
  esta_activo: number;
}

interface ServicioSeleccionado {
  servicio: Servicio;
  cantidad: number;
  comentario: string;
  subtotal: number;
}

interface Cliente {
  id: number;
  nombre: string;
  email: string;
  empresa: string;
  telefono: string;
  direccion: string;
  cif_nif: string;
}

@Component({
  selector: 'app-presupuesto',
  imports: [CommonModule, FormsModule],
  template: `<div>
    <!-- Presupuesto Component Template -->
    <h2>Presupuesto</h2>
    <div *ngIf="message" class="alert alert-info">{{ message }}</div>
    <!-- Add your component HTML here -->
  </div>`,
})
export class Presupuesto implements OnInit {
  private http = inject(HttpClient);
  private router = inject(Router);
  private platformId = inject(PLATFORM_ID);

  servicios: Servicio[] = [];
  serviciosFiltrados: Servicio[] = [];
  categorias: string[] = [];
  categoriaSeleccionada: string = 'Todas';
  busqueda: string = '';
  
  clientes: Cliente[] = [];
  clienteSeleccionadoId: number | null = null;
  clienteSeleccionado: Cliente | null = null;
  
  serviciosSeleccionados: ServicioSeleccionado[] = [];
  
  total: number = 0;
  notas: string = '';
  
  message: string = '';
  loading: boolean = false;
  guardando: boolean = false;

  usuarioDni: string = '';
  usuarioNombre: string = '';
  usuarioRol: string = '';

  ngOnInit() {
    // Solo ejecutar en el navegador
    if (!isPlatformBrowser(this.platformId)) {
      return;
    }

    // Obtener datos del usuario desde localStorage
    const usuarioData = localStorage.getItem('usuario');
    if (!usuarioData) {
      this.router.navigate(['/login']);
      return;
    }

    const usuario = JSON.parse(usuarioData);
    this.usuarioDni = usuario.dni;
    this.usuarioNombre = usuario.nombre || 'Usuario';
    this.usuarioRol = usuario.rol || 'Usuario';

    // Cargar servicios y clientes
    this.cargarServicios();
    this.cargarClientes();
  }

  cargarClientes() {
    // Cargar lista de clientes del usuario
    const headers = {
      'X-User-DNI': this.usuarioDni
    };
    
    this.http.get<any>(`${environment.apiUrl}/api/clientes.php`, { headers })
      .subscribe({
        next: (response) => {
          if (response.clientes && Array.isArray(response.clientes)) {
            this.clientes = response.clientes;
          }
        },
        error: (err) => {
          this.clientes = [];
        }
      });
  }

  seleccionarCliente(clienteId: number) {
    const cliente = this.clientes.find(c => c.id === clienteId);
    if (cliente) {
      this.clienteSeleccionado = cliente;
      this.clienteSeleccionadoId = clienteId;
    }
  }

  cargarServicios() {
    this.loading = true;
    this.http.get('/api/servicios.php')
      .subscribe({
        next: (response: any) => {
          this.loading = false;
          if (response.success) {
            // Convertir precio_base a número
            this.servicios = response.data.map((s: any) => ({
              ...s,
              precio_base: parseFloat(s.precio_base)
            }));
            this.serviciosFiltrados = this.servicios;
            
            // Extraer categorías únicas
            const categoriasSet = new Set(this.servicios.map(s => s.categoria_nombre));
            this.categorias = Array.from(categoriasSet).sort();
          } else {
            this.message = 'Error al cargar servicios';
          }
        },
        error: (error) => {
          this.loading = false;
          this.message = 'Error de conexión: ' + error.message;
        }
      });
  }

  filtrarServicios() {
    this.serviciosFiltrados = this.servicios.filter(servicio => {
      const coincideCategoria = this.categoriaSeleccionada === 'Todas' || 
                                 servicio.categoria_nombre === this.categoriaSeleccionada;
      const coincideBusqueda = !this.busqueda || 
                                servicio.nombre.toLowerCase().includes(this.busqueda.toLowerCase()) ||
                                servicio.descripcion.toLowerCase().includes(this.busqueda.toLowerCase());
      return coincideCategoria && coincideBusqueda;
    });
  }

  agregarServicio(servicio: Servicio) {
    // Verificar si ya está seleccionado
    const yaSeleccionado = this.serviciosSeleccionados.find(
      s => s.servicio.nombre === servicio.nombre
    );

    if (yaSeleccionado) {
      this.message = 'Este servicio ya está agregado al presupuesto';
      setTimeout(() => this.message = '', 3000);
      return;
    }

    const servicioSeleccionado: ServicioSeleccionado = {
      servicio: servicio,
      cantidad: 1,
      comentario: '',
      subtotal: servicio.precio_base
    };

    this.serviciosSeleccionados.push(servicioSeleccionado);
    this.calcularTotal();
  }

  eliminarServicio(index: number) {
    this.serviciosSeleccionados.splice(index, 1);
    this.calcularTotal();
  }

  actualizarSubtotal(servicioSel: ServicioSeleccionado) {
    servicioSel.subtotal = servicioSel.servicio.precio_base * servicioSel.cantidad;
    this.calcularTotal();
  }

  calcularTotal() {
    this.total = this.serviciosSeleccionados.reduce(
      (sum, item) => sum + item.subtotal, 
      0
    );
  }

  guardarPresupuesto() {
    if (this.serviciosSeleccionados.length === 0) {
      this.message = 'Debe agregar al menos un servicio';
      return;
    }

    if (!this.usuarioDni) {
      this.message = 'Error: No se pudo obtener el DNI del usuario. Por favor, inicie sesión nuevamente.';
      setTimeout(() => this.router.navigate(['/login']), 2000);
      return;
    }

    this.guardando = true;
    this.message = '';

    // Preparar detalles
    const detalles = this.serviciosSeleccionados.map(item => ({
      servicio_nombre: item.servicio.nombre,
      cantidad: item.cantidad,
      precio: item.servicio.precio_base,
      comentario: item.comentario || null
    }));

    // Preparar datos del presupuesto
    const datosPresupuesto = {
      usuario_dni: this.usuarioDni,
      total: this.total,
      notas: this.notas || null,
      estado: 'borrador',
      validez_dias: 30,
      detalles: detalles
    };

    this.http.post('/api/presupuestos.php', datosPresupuesto)
      .subscribe({
        next: (response: any) => {
          this.guardando = false;
          if (response.success) {
            this.message = 'Presupuesto guardado exitosamente: ' + response.data.numero_presupuesto;
            
            // Redirigir al panel después de 2 segundos
            setTimeout(() => {
              this.router.navigate(['/panel-registrado']);
            }, 2000);
          } else {
            this.message = 'Error: ' + (response.error || 'Error desconocido');
          }
        },
        error: (error) => {
          this.guardando = false;
          this.message = 'Error de conexión: ' + (error.error?.error || error.message);
        }
      });
  }

  cancelar() {
    this.router.navigate(['/panel-registrado']);
  }

  cerrarSesion() {
    // Limpiar datos de sesión
    if (typeof window !== 'undefined' && window.localStorage) {
      localStorage.removeItem('usuario');
    }
    this.router.navigate(['/']);
  }
}
