import { Component, inject, PLATFORM_ID, OnInit, OnDestroy, ChangeDetectorRef } from '@angular/core';
import { Router } from '@angular/router';
import { CommonModule, isPlatformBrowser } from '@angular/common';
import { HttpClient } from '@angular/common/http';
import { SidebarComponent } from '../components/sidebar/sidebar.component';
import { environment } from '../../environments/environment';

@Component({
  selector: 'app-panel-jefe-equipo',
  imports: [CommonModule, SidebarComponent],
  templateUrl: './panel-jefe-equipo.html',
})
export class PanelJefeEquipo implements OnInit, OnDestroy {
  private router = inject(Router);
  private platformId = inject(PLATFORM_ID);
  private http = inject(HttpClient);
  private cdr = inject(ChangeDetectorRef);

  // Datos del jefe de equipo
  nombreUsuario = 'Jefe de equipo';
  trabajadoresCount = 0;
  clientesCount = 0;
  equipoNombre = '';
  proyectosTotal = 0;
  proyectosEnProceso = 0;
  proyectosFinalizados = 0;
  usuarioDni = '';
  totalFacturado = 0;

  ngOnInit() {
    // Solo ejecutar en el navegador
    if (!isPlatformBrowser(this.platformId)) {
      return;
    }

    // Cargar datos del usuario desde localStorage
    const usuarioData = localStorage.getItem('usuario');
    if (usuarioData) {
      const usuario = JSON.parse(usuarioData);
      this.nombreUsuario = usuario.nombre || 'Jefe de equipo';
      this.trabajadoresCount = usuario.miembros_count || 0;
      this.equipoNombre = usuario.equipo_nombre || 'Mi Equipo';
      // No inicializar proyectosTotal desde localStorage, se cargará desde servidor
      this.proyectosEnProceso = usuario.proyectos_en_proceso || 0;
      this.proyectosFinalizados = usuario.proyectos_finalizados || 0;
      this.usuarioDni = usuario.dni || '';
    }

    // Intentar cargar contador de clientes desde localStorage primero
    const clientesCountStored = localStorage.getItem('clientesCount');
    if (clientesCountStored) {
      this.clientesCount = parseInt(clientesCountStored, 10);
    }

    // Cargar total facturado desde localStorage
    const totalFacturadoStored = localStorage.getItem('totalFacturado');
    if (totalFacturadoStored) {
      this.totalFacturado = parseFloat(totalFacturadoStored);
    }

    // Cargar conteo actual de clientes desde servidor
    this.cargarClientesCount();
    
    // Cargar conteo de proyectos desde localStorage (se actualiza desde mis-proyectos.ts)
    const proyectosCountStored = localStorage.getItem('proyectosTotal');
    if (proyectosCountStored) {
      this.proyectosTotal = parseInt(proyectosCountStored, 10);
    }
    
    // Cargar proyectos desde servidor
    this.cargarProyectosCount();
    
    // Escuchar cambios en localStorage
    window.addEventListener('storage', (event) => {
      if (event.key === 'proyectosTotal') {
        this.proyectosTotal = parseInt(event.newValue || '0', 10);
        this.cdr.markForCheck();
      }
      if (event.key === 'clientesCount') {
        this.clientesCount = parseInt(event.newValue || '0', 10);
      }
    });
  }

  cargarProyectosCount() {
    // Obtener conteo de proyectos del servidor
    const headers = {
      'X-User-DNI': this.usuarioDni
    };
    
    this.http.get<any>(`${environment.apiUrl}/api/proyectos.php`, { headers })
      .subscribe({
        next: (response) => {
          if (response && response.proyectos && Array.isArray(response.proyectos)) {
            this.proyectosTotal = response.proyectos.length;
            // Guardar en localStorage para persistencia
            localStorage.setItem('proyectosTotal', this.proyectosTotal.toString());
          } else {
            this.proyectosTotal = 0;
          }
        },
        error: (err) => {
          // Si falla, intenta usar el valor guardado en localStorage
          const proyectosCountStored = localStorage.getItem('proyectosTotal');
          if (proyectosCountStored) {
            this.proyectosTotal = parseInt(proyectosCountStored, 10);
          } else {
            this.proyectosTotal = 0;
          }
        }
      });
  }

  cargarClientesCount() {
    // Obtener conteo de clientes del servidor
    const headers = {
      'X-User-DNI': this.usuarioDni
    };
    
    this.http.get<any>(`${environment.apiUrl}/api/clientes.php`, { headers })
      .subscribe({
        next: (response) => {
          if (response.clientes && Array.isArray(response.clientes)) {
            this.clientesCount = response.clientes.length;
            // Guardar en localStorage para persistencia
            localStorage.setItem('clientesCount', this.clientesCount.toString());
          }
        },
        error: (err) => {
          // Si falla, intenta usar el valor guardado en localStorage
          const clientesCountStored = localStorage.getItem('clientesCount');
          if (clientesCountStored) {
            this.clientesCount = parseInt(clientesCountStored, 10);
          } else {
            this.clientesCount = 0;
          }
        }
      });
  }

  actualizarClientesCount() {
    // Método para actualizar el contador (llamado desde registrar-cliente)
    this.cargarClientesCount();
  }

  cerrarSesion() {
    // Solo ejecutar en el navegador
    if (isPlatformBrowser(this.platformId)) {
      // Limpiar datos de sesión
      localStorage.removeItem('usuario');
    }
    this.router.navigate(['/']);
  }

  ngOnDestroy() {
    // No necesita limpiar nada
  }
}
