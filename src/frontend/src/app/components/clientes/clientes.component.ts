import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ClienteService, Cliente } from '../../services/cliente.service';

@Component({
  selector: 'app-clientes',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './clientes.component.html',
  styleUrls: ['./clientes.component.scss']
})
export class ClientesComponent implements OnInit {
  private clienteService = inject(ClienteService);

  clientes: Cliente[] = [];
  loading = true;
  error = '';

  ngOnInit() {
    this.loadClientes();
  }

  loadClientes() {
    this.loading = true;
    this.error = '';

    this.clienteService.getClientes().subscribe({
      next: (response) => {
        this.loading = false;
        if (response.success && response.data) {
          this.clientes = response.data;
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
}