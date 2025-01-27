import { Component, inject, OnInit, PLATFORM_ID } from '@angular/core';
import { Router } from '@angular/router';
import { CommonModule, isPlatformBrowser } from '@angular/common';
import { SidebarComponent } from '../components/sidebar/sidebar.component';

@Component({
  selector: 'app-panel-admin',
  imports: [CommonModule, SidebarComponent],
  templateUrl: './panel-admin.html',
})
export class PanelAdmin implements OnInit {
  private router = inject(Router);
  private platformId = inject(PLATFORM_ID);
  
  admin: any = null;
  totalUsuarios = 0;
  totalClientes = 0;
  pedidosPendientes = 0;
  ventasMes = 0;

  ngOnInit() {
    if (isPlatformBrowser(this.platformId)) {
      const usuarioData = localStorage.getItem('usuario');
      if (usuarioData) {
        this.admin = JSON.parse(usuarioData);
      }
    }
  }

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
