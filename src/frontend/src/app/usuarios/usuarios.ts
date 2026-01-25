import { Component, OnInit, inject, PLATFORM_ID } from '@angular/core';
import { CommonModule, isPlatformBrowser } from '@angular/common';
import { Router } from '@angular/router';
import { UsuarioService, Usuario } from '../services/usuario.service';
import { getEstadoClass } from '../utils/estado-utils';

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

  // Exponer utilidad para el template
  getEstadoClass = getEstadoClass;

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
        alert('Error de conexión con el servidor');
      }
    });
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
