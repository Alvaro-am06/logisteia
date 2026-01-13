import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { ClienteService, Cliente } from '../../services/cliente.service';

@Component({
  selector: 'app-clientes',
  standalone: true,
  imports: [CommonModule, RouterLink],
  templateUrl: './clientes.component.html',
  styleUrls: ['./clientes.component.scss']
})
export class ClientesComponent implements OnInit {
  private clienteService = inject(ClienteService);

  clientes: Cliente[] = [];
  loading = true;
  error = '';
  mensaje = '';

  ngOnInit() {
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