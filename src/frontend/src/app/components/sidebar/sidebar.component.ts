import { Component, Input, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Router } from '@angular/router';

export interface MenuItem {
  label: string;
  route: string;
  icon?: string;
}

@Component({
  selector: 'app-sidebar',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './sidebar.component.html',
  styleUrls: ['./sidebar.component.css']
})
export class SidebarComponent implements OnInit {
  @Input() rol: 'jefe_equipo' | 'trabajador' | 'moderador' | 'admin' = 'trabajador';
  
  isCollapsed = true; // Comienza colapsado
  menuItems: MenuItem[] = [];
  isHidden = false; // Controla si el sidebar está oculto

  constructor(private router: Router) {}

  ngOnInit() {
    this.menuItems = this.getMenuItemsByRole();
    // Detectar cambios de ruta
    this.router.events.subscribe(() => {
      this.updateSidebarVisibility();
    });
    this.updateSidebarVisibility();
  }

  private updateSidebarVisibility() {
    // Rutas donde el sidebar debe estar oculto
    const hiddenRoutes: string[] = [];
    this.isHidden = hiddenRoutes.some(route => this.router.url.startsWith(route));
  }

  private getMenuItemsByRole(): MenuItem[] {
    switch (this.rol) {
      case 'jefe_equipo':
        return [
          { label: 'Dashboard', route: '/panel-jefe-equipo', icon: '/icons/home-2-svgrepo-com.svg' },
          { label: 'Mi Equipo', route: '/mi-equipo', icon: '/icons/people-svgrepo-com.svg' },
          { label: 'Proyectos', route: '/mis-proyectos', icon: '/icons/layout-1-svgrepo-com.svg' },
          // { label: 'Presupuestos', route: '/mis-presupuestos', icon: '/icons/document-svgrepo-com.svg' }, // Temporalmente deshabilitado
          { label: 'Clientes', route: '/clientes', icon: '/icons/folder-svgrepo-com.svg' },
          { label: 'Perfil', route: '/perfil', icon: '/icons/user-3-svgrepo-com.svg' }
        ];
      
      case 'trabajador':
        return [
          { label: 'Dashboard', route: '/panel-registrado', icon: '/icons/home-2-svgrepo-com.svg' },
          { label: 'Proyectos', route: '/mis-proyectos', icon: '/icons/code-box-svgrepo-com.svg' },
          { label: 'Perfil', route: '/perfil', icon: '/icons/user-3-svgrepo-com.svg' }
        ];
      
      case 'moderador':
        return [
          { label: 'Dashboard', route: '/panel-moderador', icon: '/icons/home-2-svgrepo-com.svg' },
          { label: 'Baneos', route: '/panel-moderador', icon: '/icons/close-svgrepo-com.svg' },
          { label: 'Usuarios', route: '/usuarios', icon: '/icons/people-svgrepo-com.svg' },
          { label: 'Perfil', route: '/perfil', icon: '/icons/user-3-svgrepo-com.svg' }
        ];
      
      case 'admin':
        return [
          { label: 'Dashboard', route: '/panel-admin', icon: '/icons/home-2-svgrepo-com.svg' },
          { label: 'Usuarios', route: '/usuarios', icon: '/icons/people-svgrepo-com.svg' },
          { label: 'Clientes', route: '/clientes', icon: '/icons/clientes.svg' },
          { label: 'Perfil', route: '/perfil', icon: '/icons/user-3-svgrepo-com.svg' }
        ];
      
      default:
        return [];
    }
  }
}
