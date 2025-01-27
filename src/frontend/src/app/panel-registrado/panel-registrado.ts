import { Component, inject, OnInit, PLATFORM_ID } from '@angular/core';
import { Router, RouterLink, RouterLinkActive } from '@angular/router';
import { CommonModule, isPlatformBrowser } from '@angular/common';

@Component({
  selector: 'app-panel-registrado',
  imports: [CommonModule, RouterLink, RouterLinkActive],
  templateUrl: './panel-registrado.html',
})
export class PanelRegistrado implements OnInit {
  private router = inject(Router);
  private platformId = inject(PLATFORM_ID);

  // Datos del usuario registrado
  nombreUsuario = 'Usuario Registrado';
  proyectosCreados = 0;
  proyectosCompletados = 0;

  ngOnInit() {
    // Solo ejecutar en el navegador
    if (!isPlatformBrowser(this.platformId)) {
      return;
    }

    // Cargar datos del usuario desde localStorage
    const usuarioData = localStorage.getItem('usuario');
    if (usuarioData) {
      const usuario = JSON.parse(usuarioData);
      this.nombreUsuario = usuario.nombre || 'Usuario Registrado';
      this.proyectosCreados = usuario.proyectos_creados || 0;
      this.proyectosCompletados = usuario.proyectos_completados || 0;
    }

  }

  cerrarSesion() {
    // Solo ejecutar en el navegador
    if (isPlatformBrowser(this.platformId)) {
      // Limpiar datos de sesión
      localStorage.removeItem('usuario');
    }
    this.router.navigate(['/']);
  }

  crearPresupuesto() {
    this.router.navigate(['/presupuesto']);
  }
}