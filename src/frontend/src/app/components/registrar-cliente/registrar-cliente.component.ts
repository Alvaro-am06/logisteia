import { Component, inject, OnInit, PLATFORM_ID } from '@angular/core';
import { CommonModule, isPlatformBrowser } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { HttpClient } from '@angular/common/http';

@Component({
  selector: 'app-registrar-cliente',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './registrar-cliente.component.html',
  styleUrls: ['./registrar-cliente.component.css']
})
export class RegistrarClienteComponent implements OnInit {
  private http = inject(HttpClient);
  private router = inject(Router);
  private platformId = inject(PLATFORM_ID);

  cliente = {
    dni: '',
    nombre: '',
    email: '',
    telefono: '',
    password: ''
  };

  mensaje = '';
  error = '';
  guardando = false;
  usuarioNombre = 'Usuario';
  usuarioRol = '';

  ngOnInit() {
    // Solo ejecutar en el navegador
    if (isPlatformBrowser(this.platformId)) {
      const usuarioData = localStorage.getItem('usuario');
      if (usuarioData) {
        const usuario = JSON.parse(usuarioData);
        this.usuarioNombre = usuario.nombre || 'Usuario';
        this.usuarioRol = usuario.rol || '';
      }
    }
  }

  registrarCliente() {
    // Validar campos obligatorios
    if (!this.cliente.dni || !this.cliente.nombre || !this.cliente.email || 
        !this.cliente.telefono || !this.cliente.password) {
      this.error = 'Todos los campos son obligatorios';
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

    // Validar DNI (8 números + letra)
    const dniRegex = /^\d{8}[A-Za-z]$/;
    if (!dniRegex.test(this.cliente.dni)) {
      this.error = 'El DNI debe tener 8 números seguidos de una letra';
      this.mensaje = '';
      return;
    }

    this.guardando = true;
    this.error = '';
    this.mensaje = '';

    this.http.post('http://localhost/logisteia/src/www/api/clientes.php', {
      dni: this.cliente.dni,
      nombre: this.cliente.nombre,
      email: this.cliente.email,
      telefono: this.cliente.telefono,
      password: this.cliente.password
    }).subscribe({
      next: (response: any) => {
        this.guardando = false;
        if (response.success) {
          this.mensaje = 'Cliente registrado correctamente';
          this.error = '';
          // Limpiar formulario
          this.cliente = {
            dni: '',
            nombre: '',
            email: '',
            telefono: '',
            password: ''
          };
          // Redirigir después de 2 segundos
          setTimeout(() => {
            this.volver();
          }, 2000);
        } else {
          this.error = response.error || 'Error al registrar el cliente';
          this.mensaje = '';
        }
      },
      error: (err) => {
        this.guardando = false;
        this.error = 'Error de conexión con el servidor';
        this.mensaje = '';
        console.error('Error al registrar cliente:', err);
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
      dni: '',
      nombre: '',
      email: '',
      telefono: '',
      password: ''
    };
    this.mensaje = '';
    this.error = '';
  }
}
