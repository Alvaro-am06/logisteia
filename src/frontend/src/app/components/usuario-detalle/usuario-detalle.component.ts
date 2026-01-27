import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute, Router, RouterModule } from '@angular/router';
import { UsuarioService, UsuarioDetalle } from '../../services/usuario.service';

@Component({
  selector: 'app-usuario-detalle',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './usuario-detalle.component.html',
  styleUrls: ['./usuario-detalle.component.scss']
})
export class UsuarioDetalleComponent implements OnInit {
  private route = inject(ActivatedRoute);
  private router = inject(Router);
  private usuarioService = inject(UsuarioService);

  dni = '';
  usuario: UsuarioDetalle | null = null;
  loading = true;
  error = '';

  ngOnInit() {
    this.dni = this.route.snapshot.params['dni'];
    if (this.dni) {
      this.loadUsuarioDetalle();
    } else {
      this.error = 'DNI no especificado';
      this.loading = false;
    }
  }

  loadUsuarioDetalle() {
    this.loading = true;
    this.error = '';

    this.usuarioService.getUsuario(this.dni).subscribe({
      next: (response) => {
        this.loading = false;
        if (response.success && response.data) {
          this.usuario = response.data;
        } else {
          this.error = response.error || 'Usuario no encontrado';
        }
      },
      error: (err) => {
        this.loading = false;
        this.error = 'Error de conexión con el servidor';
        console.error('Error cargando detalle de usuario:', err);
      }
    });
  }

  cambiarRol(operacion: string) {
    const motivo = prompt(`Motivo para ${operacion} el usuario (opcional):`);

    if (confirm(`¿Estás seguro de ${operacion} este usuario?`)) {
      this.usuarioService.cambiarRol(this.dni, operacion, motivo || undefined).subscribe({
        next: (response) => {
          if (response.success) {
            alert(`Usuario ${operacion}do correctamente`);
            this.loadUsuarioDetalle(); // Recargar datos
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

  volver() {
    this.router.navigate(['/usuarios']);
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