import { Component, OnInit, inject, PLATFORM_ID } from '@angular/core';
import { CommonModule, isPlatformBrowser } from '@angular/common';
import { Router, RouterLink } from '@angular/router';
import { ClienteService, Cliente } from '../../services/cliente.service';

@Component({
  selector: 'app-clientes',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './clientes.component.html',
  styleUrls: ['./clientes.component.css']
})
export class ClientesComponent implements OnInit {
  private clienteService = inject(ClienteService);
  private router = inject(Router);
  private platformId = inject(PLATFORM_ID);

  clientes: Cliente[] = [];
  loading = true;
  error = '';
  mensaje = '';
  usuarioNombre = 'Usuario';

  ngOnInit() {
    // Solo ejecutar en el navegador
    if (isPlatformBrowser(this.platformId)) {
      // Cargar datos del usuario desde localStorage
      const usuarioData = localStorage.getItem('usuario');
      if (usuarioData) {
        const usuario = JSON.parse(usuarioData);
        this.usuarioNombre = usuario.nombre || 'Usuario';
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
        } else {
          this.error = response.error || 'Error al cargar clientes';
        }
      },
      error: (err) => {
        this.loading = false;
        this.error = 'Error de conexión con el servidor';
        console.error('Error cargando clientes:', err);
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
    // Implementar vista de detalle si es necesario
    console.log('Ver detalle de cliente:', cliente);
  }

  eliminarCliente(dni: string, nombre: string) {
    if (!confirm(`¿Estás seguro de que deseas eliminar al cliente ${nombre}?`)) {
      return;
    }

    this.clienteService.eliminarCliente(dni).subscribe({
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
        console.error('Error eliminando cliente:', err);
      }
    });
  }
}