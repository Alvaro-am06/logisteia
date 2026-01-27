import { Component, OnInit, inject, PLATFORM_ID } from '@angular/core';
import { CommonModule, isPlatformBrowser } from '@angular/common';
import { Router } from '@angular/router';
import { UsuarioService, Usuario } from '../services/usuario.service';

@Component({
  selector: 'app-usuarios',
  imports: [CommonModule],
  templateUrl: './usuarios.html',
})
export class Usuarios implements OnInit {
  private usuarioService = inject(UsuarioService);
  private router = inject(Router);
  private platformId = inject(PLATFORM_ID);

  usuarios: Usuario[] = [];
  loading = true;
  error = '';

  ngOnInit() {
    // Solo ejecutar en el navegador
    if (!isPlatformBrowser(this.platformId)) {
      return;
    }

    // Verificar que el usuario sea administrador
    const usuarioData = localStorage.getItem('usuario');
    if (!usuarioData) {
      this.router.navigate(['/login']);
      return;
    }

    const usuario = JSON.parse(usuarioData);
    if (usuario.rol !== 'administrador') {
      this.router.navigate(['/panel-registrado']);
      return;
    }

    this.cargarUsuarios();
  }

  cargarUsuarios() {
    this.loading = true;
    this.error = '';

    this.usuarioService.getUsuarios().subscribe({
      next: (response) => {
        if (response.success && response.data) {
          this.usuarios = response.data;
        } else {
          this.error = response.error || 'Error al cargar usuarios';
        }
        this.loading = false;
      },
      error: (err) => {
        console.error('Error al cargar usuarios:', err);
        this.error = 'Error de conexión con el servidor';
        this.loading = false;
      }
    });
  }

  cambiarEstado(dni: string, operacion: string) {
    // Confirmar acción
    let mensaje = '';
    switch (operacion) {
      case 'activar':
        mensaje = '¿Deseas activar este usuario como administrador?';
        break;
      case 'suspender':
        mensaje = '¿Deseas suspender este usuario (cambiar a registrado)?';
        break;
      case 'eliminar':
        mensaje = '¿Estás seguro de eliminar este usuario? Esta acción no se puede deshacer.';
        break;
    }

    if (!confirm(mensaje)) {
      return;
    }

    // Solicitar motivo (opcional)
    const motivo = prompt(`Motivo para ${operacion} el usuario (opcional):`);

    // Ejecutar operación
    this.usuarioService.cambiarRol(dni, operacion, motivo || undefined).subscribe({
      next: (response) => {
        if (response.success) {
          alert(`Usuario ${operacion === 'activar' ? 'activado' : operacion === 'suspender' ? 'suspendido' : 'eliminado'} correctamente`);
          this.cargarUsuarios(); // Recargar lista
        } else {
          alert('Error: ' + (response.error || 'No se pudo realizar la operación'));
        }
      },
      error: (err) => {
        console.error('Error al cambiar estado:', err);
        alert('Error de conexión con el servidor');
      }
    });
  }

  getEstadoClass(estado: string): string {
    switch (estado.toLowerCase()) {
      case 'activo':
        return 'bg-green-100 text-green-800';
      case 'suspendido':
        return 'bg-yellow-100 text-yellow-800';
      case 'eliminado':
        return 'bg-red-100 text-red-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  }

  getRolClass(rol: string): string {
    return rol === 'administrador' 
      ? 'bg-blue-100 text-blue-800' 
      : 'bg-gray-100 text-gray-800';
  }

  contarPorRol(rol: string): number {
    return this.usuarios.filter(u => u.rol === rol).length;
  }
}
