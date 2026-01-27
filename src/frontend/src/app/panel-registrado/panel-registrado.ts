import { Component, inject, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-panel-registrado',
  imports: [CommonModule],
  templateUrl: './panel-registrado.html',
})
export class PanelRegistrado implements OnInit {
  private router = inject(Router);

  // Datos del usuario registrado
  nombreUsuario = 'Usuario Registrado';
  proyectosCreados = 0;
  proyectosCompletados = 0;

  ngOnInit() {
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
    // Limpiar datos de sesión
    localStorage.removeItem('usuario');
    this.router.navigate(['/']);
  }
}