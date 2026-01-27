import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { AuthService } from '../../services/auth.service';
import { UsuarioService } from '../../services/usuario.service';
import { ClienteService } from '../../services/cliente.service';

@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.scss']
})
export class DashboardComponent implements OnInit {
  private authService = inject(AuthService);
  private usuarioService = inject(UsuarioService);
  private clienteService = inject(ClienteService);

  adminName = '';
  stats = {
    usuarios: 0,
    clientes: 0
  };
  loading = true;

  ngOnInit() {
    this.loadStats();
  }

  loadStats() {
    this.loading = true;

    // Cargar estadísticas de usuarios
    this.usuarioService.getUsuarios().subscribe({
      next: (response) => {
        if (response.success && response.data) {
          this.stats.usuarios = response.data.length;
        }
      },
      error: (err) => console.error('Error cargando usuarios:', err)
    });

    // Cargar estadísticas de clientes
    this.clienteService.getClientes().subscribe({
      next: (response) => {
        if (response.success && response.data) {
          this.stats.clientes = response.data.length;
        }
        this.loading = false;
      },
      error: (err) => {
        console.error('Error cargando clientes:', err);
        this.loading = false;
      }
    });
  }

  logout() {
    this.authService.logout().subscribe({
      next: () => {
        this.authService.clearSession();
        window.location.href = '/login';
      },
      error: (err) => {
        console.error('Error en logout:', err);
        // Forzar logout del lado cliente
        this.authService.clearSession();
        window.location.href = '/login';
      }
    });
  }
}