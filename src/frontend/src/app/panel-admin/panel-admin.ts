import { Component, inject } from '@angular/core';
import { Router, RouterLink } from '@angular/router';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-panel-admin',
  imports: [CommonModule, RouterLink],
  templateUrl: './panel-admin.html',
})
export class PanelAdmin {
  private router = inject(Router);
  
  totalUsuarios = 0;
  totalClientes = 0;
  pedidosPendientes = 0;
  ventasMes = 0;

  cerrarSesion() {
    // Limpiar datos de sesión
    localStorage.removeItem('token');
    localStorage.removeItem('usuario');
    this.router.navigate(['/login']);
  }
}
