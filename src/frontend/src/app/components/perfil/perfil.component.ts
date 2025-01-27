import { Component, inject, Input, OnInit, PLATFORM_ID } from '@angular/core';
import { CommonModule, isPlatformBrowser } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { SidebarComponent } from '../sidebar/sidebar.component';
import { formatearRol } from '../../utils/formatear-rol';
import { environment } from '../../../environments/environment';

@Component({
  selector: 'app-perfil',
  standalone: true,
  imports: [CommonModule, FormsModule, SidebarComponent],
  templateUrl: './perfil.component.html',
  styleUrls: ['./perfil.component.css']
})
export class PerfilComponent implements OnInit {
  private http = inject(HttpClient);
  private router = inject(Router);
  private platformId = inject(PLATFORM_ID);

  @Input() mostrarNavbar: boolean = true;

  usuario: any = {
    dni: '',
    nombre: '',
    email: '',
    telefono: '',
    rol: '',
    estado: '',
    fecha_registro: null,
    fecha_modificacion: null
  };

  usuarioOriginal: any = null;
  usuarioNombre = 'Usuario';
  usuarioRol = '';
  modoEdicion = false;
  guardando = false;
  mensaje = '';
  error = '';
  nuevaPassword = '';
  confirmarPassword = '';

  formatearRol = formatearRol; // Hacer la función accesible en el template

  ngOnInit() {
    if (isPlatformBrowser(this.platformId)) {
      this.cargarDatosUsuario();
    }
  }

  cargarDatosUsuario() {
    const usuarioData = localStorage.getItem('usuario');
    if (usuarioData) {
      this.usuario = JSON.parse(usuarioData);
      this.usuarioNombre = this.usuario.nombre || 'Usuario';
      this.usuarioOriginal = { ...this.usuario };
    } else {
      this.router.navigate(['/login']);
    }
  }

  activarEdicion() {
    this.modoEdicion = true;
    this.mensaje = '';
    this.error = '';
  }

  cancelar() {
    if (this.modoEdicion) {
      // Restaurar datos originales
      this.usuario = { ...this.usuarioOriginal };
      this.modoEdicion = false;
      this.nuevaPassword = '';
      this.confirmarPassword = '';
      this.mensaje = '';
      this.error = '';
    } else {
      this.volver();
    }
  }

  guardarCambios() {
    // Validar campos obligatorios
    if (!this.usuario.nombre || !this.usuario.email) {
      this.error = 'El nombre y el email son obligatorios';
      this.mensaje = '';
      return;
    }

    // Validar email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(this.usuario.email)) {
      this.error = 'El formato del email no es válido';
      this.mensaje = '';
      return;
    }

    // Validar contraseñas si se están cambiando
    if (this.nuevaPassword || this.confirmarPassword) {
      if (this.nuevaPassword !== this.confirmarPassword) {
        this.error = 'Las contraseñas no coinciden';
        this.mensaje = '';
        return;
      }
      if (this.nuevaPassword.length < 8) {
        this.error = 'La contraseña debe tener al menos 8 caracteres';
        this.mensaje = '';
        return;
      }
    }

    this.guardando = true;
    this.error = '';
    this.mensaje = '';

    const datosActualizar: any = {
      dni: this.usuario.dni,
      nombre: this.usuario.nombre,
      email: this.usuario.email,
      telefono: this.usuario.telefono || null
    };

    // Agregar contraseña si se está cambiando
    if (this.nuevaPassword) {
      datosActualizar.password = this.nuevaPassword;
    }

    this.http.put(`${environment.apiUrl}/api/perfil.php`, datosActualizar)
      .subscribe({
        next: (response: any) => {
          this.guardando = false;
          if (response.success) {
            this.mensaje = 'Perfil actualizado correctamente';
            this.error = '';
            this.modoEdicion = false;
            this.nuevaPassword = '';
            this.confirmarPassword = '';
            
            // Actualizar datos en localStorage
            if (isPlatformBrowser(this.platformId)) {
              const usuarioActualizado = { ...this.usuario, ...response.data };
              localStorage.setItem('usuario', JSON.stringify(usuarioActualizado));
              this.usuario = usuarioActualizado;
              this.usuarioOriginal = { ...usuarioActualizado };
              this.usuarioNombre = usuarioActualizado.nombre;
              this.usuarioRol = usuarioActualizado.rol || 'Usuario';
            }

            // Limpiar mensaje después de 3 segundos
            setTimeout(() => {
              this.mensaje = '';
            }, 3000);
          } else {
            this.error = response.error || 'Error al actualizar el perfil';
            this.mensaje = '';
          }
        },
        error: (err) => {
          this.guardando = false;
          this.error = 'Error de conexión con el servidor';
          this.mensaje = '';
        }
      });
  }

  volver() {
    if (isPlatformBrowser(this.platformId)) {
      const usuarioData = localStorage.getItem('usuario');
      if (usuarioData) {
        const usuario = JSON.parse(usuarioData);
        if (usuario.rol === 'moderador') {
          this.router.navigate(['/panel-moderador']);
        } else if (usuario.rol === 'administrador') {
          this.router.navigate(['/panel-admin']);
        } else if (usuario.rol === 'jefe_equipo') {
          this.router.navigate(['/panel-jefe-equipo']);
        } else {
          this.router.navigate(['/panel-registrado']);
        }
      } else {
        this.router.navigate(['/login']);
      }
    }
  }

  cerrarSesion() {
    // Limpiar datos de sesión
    if (isPlatformBrowser(this.platformId)) {
      localStorage.removeItem('usuario');
    }
    this.router.navigate(['/']);
  }
}
