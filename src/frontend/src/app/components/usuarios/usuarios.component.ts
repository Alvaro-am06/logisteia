import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { UsuarioService, Usuario } from '../../services/usuario.service';

@Component({
  selector: 'app-usuarios',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './usuarios.component.html',
  styleUrls: ['./usuarios.component.scss']
})
export class UsuariosComponent implements OnInit {
  private usuarioService = inject(UsuarioService);

  usuarios: Usuario[] = [];
  loading = true;
  error = '';

  ngOnInit() {
    this.loadUsuarios();
  }

  loadUsuarios() {
    this.loading = true;
    this.error = '';

    this.usuarioService.getUsuarios().subscribe({
      next: (response) => {
        this.loading = false;
        if (response.success && response.data) {
          this.usuarios = response.data;
        } else {
          this.error = response.error || 'Error al cargar usuarios';
        }
      },
      error: (err) => {
        this.loading = false;
        this.error = 'Error de conexión con el servidor';
        console.error('Error cargando usuarios:', err);
      }
    });
  }

  cambiarRol(dni: string, operacion: string) {
    const motivo = prompt(`Motivo para ${operacion} el usuario (opcional):`);

    if (confirm(`¿Estás seguro de ${operacion} este usuario?`)) {
      this.usuarioService.cambiarRol(dni, operacion, motivo || undefined).subscribe({
        next: (response) => {
          if (response.success) {
            alert(`Usuario ${operacion}do correctamente`);
            this.loadUsuarios(); // Recargar la lista
          } else {
            alert('Error: ' + (response.error || 'No se pudo realizar la operación'));
          }
        },
        error: (err) => {
          alert('Error de conexión con el servidor');
          console.error('Error cambiando rol:', err);
        }
      });
    }
  }

  getEstadoClass(estado: string): string {
    switch (estado?.toLowerCase()) {
      case 'activo':
        return 'estado-activo';
      case 'suspendido':
        return 'estado-suspendido';
      case 'eliminado':
        return 'estado-eliminado';
      default:
        return 'estado-desconocido';
    }
  }
}