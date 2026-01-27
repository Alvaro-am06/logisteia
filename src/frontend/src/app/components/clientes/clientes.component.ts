import { Component, OnInit, inject, PLATFORM_ID, ChangeDetectorRef } from '@angular/core';
import { CommonModule, isPlatformBrowser } from '@angular/common';
import { Router, RouterLink } from '@angular/router';
import { ClienteService, Cliente } from '../../services/cliente.service';
import { SidebarComponent } from '../sidebar/sidebar.component';
import { formatearRol } from '../../utils/formatear-rol';

@Component({
  selector: 'app-clientes',
  standalone: true,
  imports: [CommonModule, SidebarComponent],
  templateUrl: './clientes.component.html',
  styleUrls: ['./clientes.component.css']
})
export class ClientesComponent implements OnInit {
  private clienteService = inject(ClienteService);
  private router = inject(Router);
  private platformId = inject(PLATFORM_ID);
  private cdr = inject(ChangeDetectorRef);

  clientes: Cliente[] = [];
  loading = true;
  error = '';
  mensaje = '';
  usuarioNombre = 'Usuario';
  usuarioRol = '';
  clienteSeleccionado: Cliente | null = null;
  mostrarModal = false;

  ngOnInit() {
    // Solo ejecutar en el navegador
    if (isPlatformBrowser(this.platformId)) {
      // Cargar datos del usuario desde localStorage
      const usuarioData = localStorage.getItem('usuario');
      if (usuarioData) {
        const usuario = JSON.parse(usuarioData);
        this.usuarioNombre = usuario.nombre || 'Usuario';
        this.usuarioRol = formatearRol(usuario.rol) || 'Usuario';
      }
    }
    
    this.loadClientes();
  }

  loadClientes() {
    this.loading = true;
    this.error = '';

    this.clienteService.getClientes().subscribe({
      next: (response) => {
        this.loading = false;
        if (response.success && response.clientes) {
          this.clientes = response.clientes;
          this.cdr.markForCheck();
        } else {
          this.error = response.error || 'Error al cargar clientes';
          this.cdr.markForCheck();
        }
      },
      error: (err) => {
        this.loading = false;
        this.error = 'Error de conexión con el servidor';
        this.cdr.markForCheck();
      }
    });
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

  registrarNuevo() {
    this.router.navigate(['/registrar-cliente']);
  }

  verDetalle(cliente: Cliente) {
    this.clienteSeleccionado = cliente;
    this.mostrarModal = true;
    this.cdr.markForCheck();
  }

  cerrarModal() {
    this.mostrarModal = false;
    this.clienteSeleccionado = null;
    this.cdr.markForCheck();
  }

  eliminarCliente(cif_nif: string | undefined, nombre: string) {
    if (!cif_nif) {
      this.error = 'No se puede eliminar: el cliente no tiene CIF/NIF registrado';
      return;
    }

    if (!confirm(`¿Estás seguro de que deseas eliminar al cliente ${nombre}?`)) {
      return;
    }

    this.clienteService.eliminarCliente(cif_nif).subscribe({
      next: (response) => {
        if (response.success) {
          this.mensaje = 'Cliente eliminado exitosamente';
          this.error = '';
          // Recargar lista de clientes
          this.loadClientes();
          // Limpiar mensaje después de 3 segundos
          setTimeout(() => {
            this.mensaje = '';
          }, 3000);
        } else {
          this.error = response.error || 'Error al eliminar el cliente';
        }
      },
      error: (err) => {
        this.error = 'Error de conexión con el servidor';
      }
    });
  }

  cerrarSesion() {
    // Limpiar datos de sesión
    if (isPlatformBrowser(this.platformId)) {
      localStorage.removeItem('usuario');
    }
    this.router.navigate(['/']);
  }
}