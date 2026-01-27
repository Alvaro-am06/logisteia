import { Component, inject, PLATFORM_ID } from '@angular/core';
import { Router, RouterLink } from '@angular/router';
import { CommonModule, isPlatformBrowser } from '@angular/common';

@Component({
  selector: 'app-panel-admin',
  imports: [CommonModule, RouterLink],
  templateUrl: './panel-admin.html',
})
export class PanelAdmin {
  private router = inject(Router);
  private platformId = inject(PLATFORM_ID);
  
  totalUsuarios = 0;
  totalClientes = 0;
  pedidosPendientes = 0;
  ventasMes = 0;

  cerrarSesion() {
    // Solo ejecutar en el navegador
    if (isPlatformBrowser(this.platformId)) {
      // Limpiar datos de sesión
      localStorage.removeItem('token');
      localStorage.removeItem('usuario');
    }
    this.router.navigate(['/login']);
  }
}
