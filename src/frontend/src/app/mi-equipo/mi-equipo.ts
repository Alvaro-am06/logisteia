import { Component, inject, OnInit, PLATFORM_ID } from '@angular/core';
import { Router } from '@angular/router';
import { CommonModule, isPlatformBrowser } from '@angular/common';
import { EquipoService, MiembroEquipo, Equipo, AgregarMiembroRequest } from '../services/equipo.service';
import { FormsModule } from '@angular/forms';
import { SidebarComponent } from '../components/sidebar/sidebar.component';
import { formatearRol } from '../utils/formatear-rol';

@Component({
  selector: 'app-mi-equipo',
  imports: [CommonModule, FormsModule, SidebarComponent],
  templateUrl: './mi-equipo.html',
  styleUrl: './mi-equipo.css',
})
export class MiEquipo implements OnInit {
  private router = inject(Router);
  private equipoService = inject(EquipoService);
  private platformId = inject(PLATFORM_ID);

  // Datos del equipo
  equipo: Equipo | null = null;
  miembros: MiembroEquipo[] = [];
  loading = false;
  error = '';

  // Datos del usuario
  nombreUsuario = 'Jefe de equipo';
  usuarioRol = 'jefe_equipo';

  // Formulario para agregar miembro
  showAgregarMiembro = false;
  nuevoMiembro: AgregarMiembroRequest = {
    email_trabajador: ''
  };

  // Exponer utilidad para el template
  formatearRol = formatearRol;

  ngOnInit() {
    // Solo ejecutar en el navegador
    if (!isPlatformBrowser(this.platformId)) {
      return;
    }

    // Verificar que el usuario esté logueado
    const usuarioData = localStorage.getItem('usuario');
    if (!usuarioData) {
      this.error = 'Debes iniciar sesión para acceder a esta página';
      return;
    }

    // Cargar datos del usuario
    const usuario = JSON.parse(usuarioData);
    this.nombreUsuario = usuario.nombre || 'Jefe de equipo';
    this.usuarioRol = usuario.rol || 'jefe_equipo';

    this.cargarMiembrosEquipo();
  }

  cargarMiembrosEquipo() {
    this.loading = true;
    this.error = '';

    this.equipoService.getMiembrosEquipo().subscribe({
      next: (response) => {
        this.loading = false;
        if (response.success && response.data) {
          this.equipo = response.data.equipo;
          this.miembros = response.data.miembros || []; // Asegurar que siempre sea un array
        } else {
          this.error = response.error || 'Error al cargar los miembros del equipo';
          this.miembros = []; // Resetear a array vacío en caso de error
        }
      },
      error: (error) => {
        this.loading = false;
        this.miembros = []; // Resetear a array vacío en caso de error
        this.error = 'Error de conexión al cargar los miembros del equipo';
      }
    });
  }

  toggleAgregarMiembro() {
    this.showAgregarMiembro = !this.showAgregarMiembro;
    if (!this.showAgregarMiembro) {
      // Resetear formulario
      this.nuevoMiembro = {
        email_trabajador: ''
      };
    }
  }

  agregarMiembro() {
    if (!this.nuevoMiembro.email_trabajador.trim()) {
      this.error = 'El correo electrónico del trabajador es requerido';
      return;
    }

    // Validar formato de email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(this.nuevoMiembro.email_trabajador.trim())) {
      this.error = 'El correo electrónico no tiene un formato válido';
      return;
    }

    this.loading = true;
    this.error = '';

    this.equipoService.agregarMiembroEquipo(this.nuevoMiembro).subscribe({
      next: (response) => {
        this.loading = false;
        if (response.success) {
          // Recargar la lista de miembros
          this.cargarMiembrosEquipo();
          // Cerrar el formulario
          this.toggleAgregarMiembro();
          // Mostrar mensaje de éxito
          alert('Invitación enviada exitosamente. El trabajador recibirá un email para confirmar su participación.');
        } else {
          this.error = response.error || 'Error al agregar el miembro';
        }
      },
      error: (error) => {
        this.loading = false;
        
        // Intentar obtener el mensaje de error del servidor
        let errorMessage = 'Error de conexión al agregar el miembro';
        
        if (error.error && error.error.error) {
          errorMessage = error.error.error;
        } else if (error.status === 403) {
          errorMessage = 'No tienes permisos para realizar esta acción. Verifica que estés logueado como jefe de equipo.';
        } else if (error.status === 400) {
          errorMessage = error.error?.error || 'Error en los datos proporcionados';
        } else if (error.status === 0) {
          errorMessage = 'No se pudo conectar con el servidor. Verifica que el servidor backend esté ejecutándose.';
        }
        
        this.error = errorMessage;
      }
    });
  }

  getEstadoBadgeClass(estado: string): string {
    switch (estado) {
      case 'activo':
        return 'bg-green-100 text-green-800';
      case 'baneado':
        return 'bg-red-100 text-red-800';
      case 'eliminado':
        return 'bg-gray-100 text-gray-800';
      default:
        return 'bg-yellow-100 text-yellow-800';
    }
  }

  eliminarMiembro(miembro: any) {
    if (!confirm(`¿Estás seguro de eliminar a ${miembro.nombre} del equipo?`)) {
      return;
    }

    this.loading = true;
    this.error = '';

    this.equipoService.eliminarMiembroEquipo(miembro.dni).subscribe({
      next: (response) => {
        this.loading = false;
        if (response.success) {
          alert(`${miembro.nombre} ha sido eliminado del equipo correctamente.`);
          this.cargarMiembrosEquipo();
        } else {
          this.error = response.error || 'Error al eliminar el miembro';
        }
      },
      error: (error) => {
        this.loading = false;
        this.error = error.error?.error || 'Error de conexión al eliminar el miembro';
      }
    });
  }

  cerrarSesion() {
    // Limpiar datos de sesión
    if (typeof window !== 'undefined' && window.localStorage) {
      localStorage.removeItem('usuario');
    }
    this.router.navigate(['/']);
  }
}
