import { Component, inject, OnInit, PLATFORM_ID } from '@angular/core';
import { CommonModule, isPlatformBrowser } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { SidebarComponent } from '../sidebar/sidebar.component';
import { formatearRol } from '../../utils/formatear-rol';
import { environment } from '../../../environments/environment';

@Component({
  selector: 'app-registrar-cliente',
  standalone: true,
  imports: [CommonModule, FormsModule, SidebarComponent],
  templateUrl: './registrar-cliente.component.html',
  styleUrls: ['./registrar-cliente.component.css']
})
export class RegistrarClienteComponent implements OnInit {
  private http = inject(HttpClient);
  private router = inject(Router);
  private platformId = inject(PLATFORM_ID);

  cliente = {
    nombre: '',
    email: '',
    empresa: '',
    telefono: '',
    direccion: '',
    cif_nif: '',
    notas: ''
  };

  mensaje = '';
  error = '';
  guardando = false;
  usuarioNombre = 'Usuario';
  usuarioRol = '';
  usuarioDni = '';

  formatearRol = formatearRol; // Hacer la función accesible en el template

  ngOnInit() {
    // Solo ejecutar en el navegador
    if (isPlatformBrowser(this.platformId)) {
      const usuarioData = localStorage.getItem('usuario');
      if (usuarioData) {
        const usuario = JSON.parse(usuarioData);
        this.usuarioNombre = usuario.nombre || 'Usuario';
        this.usuarioRol = formatearRol(usuario.rol) || 'Usuario';
        this.usuarioDni = usuario.dni || '';
      }
    }
  }

  registrarCliente() {
    // Validar campos obligatorios
    if (!this.cliente.nombre || !this.cliente.email || !this.cliente.cif_nif) {
      this.error = 'Los campos nombre, email y CIF/NIF son obligatorios';
      this.mensaje = '';
      return;
    }

    // Validar formato de email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(this.cliente.email)) {
      this.error = 'El formato del email no es válido';
      this.mensaje = '';
      return;
    }

    this.guardando = true;
    this.error = '';
    this.mensaje = '';

    // Preparar headers con el DNI del usuario
    const headers = {
      'Content-Type': 'application/json',
      'X-User-DNI': this.usuarioDni
    };

    this.http.post(`${environment.apiUrl}/api/clientes.php`, {
      nombre: this.cliente.nombre,
      email: this.cliente.email,
      empresa: this.cliente.empresa || null,
      telefono: this.cliente.telefono || null,
      direccion: this.cliente.direccion || null,
      cif_nif: this.cliente.cif_nif || null,
      notas: this.cliente.notas || null
    }, { headers }).subscribe({
      next: (response: any) => {
        this.guardando = false;
        if (response.success) {
          this.mensaje = 'Cliente creado correctamente';
          this.error = '';
          
          // Actualizar contador en localStorage
          if (isPlatformBrowser(this.platformId)) {
            const clientesCountStored = localStorage.getItem('clientesCount');
            let nuevoCuenta = 1;
            if (clientesCountStored) {
              nuevoCuenta = parseInt(clientesCountStored, 10) + 1;
            }
            localStorage.setItem('clientesCount', nuevoCuenta.toString());
          }
          
          // Limpiar formulario
          this.cliente = {
            nombre: '',
            email: '',
            empresa: '',
            telefono: '',
            direccion: '',
            cif_nif: '',
            notas: ''
          };
          // Redirigir después de 2 segundos
          setTimeout(() => {
            this.volver();
          }, 2000);
        } else {
          this.error = response.error || 'Error al crear el cliente';
          this.mensaje = '';
        }
      },
      error: (err) => {
        this.guardando = false;
        // Intentar obtener el mensaje de error del servidor
        if (err.error && typeof err.error === 'object') {
          this.error = err.error.error || err.error.message || 'Error al registrar el cliente';
          if (err.error.details) {
            this.error += ': ' + err.error.details;
          }
        } else {
          this.error = 'Error de conexión con el servidor';
        }
        this.mensaje = '';
      }
    });
  }

  volver() {
    // Detectar el rol del usuario y redirigir al panel apropiado
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
        this.router.navigate(['/clientes']);
      }
    }
  }

  limpiarFormulario() {
    this.cliente = {
      nombre: '',
      email: '',
      empresa: '',
      telefono: '',
      direccion: '',
      cif_nif: '',
      notas: ''
    };
    this.mensaje = '';
    this.error = '';
  }

  cerrarSesion() {
    // Limpiar datos de sesión
    if (isPlatformBrowser(this.platformId)) {
      localStorage.removeItem('usuario');
    }
    this.router.navigate(['/']);
  }
}
