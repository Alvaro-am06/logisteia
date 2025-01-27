import { Component, OnInit, inject, PLATFORM_ID } from '@angular/core';
import { CommonModule, isPlatformBrowser } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../environments/environment';

@Component({
  selector: 'app-completar-registro',
  imports: [CommonModule, FormsModule],
  templateUrl: './completar-registro.html',
})
export class CompletarRegistro implements OnInit {
  private http = inject(HttpClient);
  private router = inject(Router);
  private platformId = inject(PLATFORM_ID);

  datosGoogle: any = {
    email: '',
    nombre: '',
    picture: '',
    googleToken: ''
  };

  dni = '';
  rol = '';
  telefono = '';
  password = '';
  confirmPassword = '';
  mensaje = '';

  ngOnInit() {
    if (isPlatformBrowser(this.platformId)) {
      // Obtener datos temporales de Google guardados en sessionStorage
      const datosTemp = sessionStorage.getItem('google_temp_data');
      
      if (!datosTemp) {
        // Si no hay datos, redirigir al login
        this.router.navigate(['/']);
        return;
      }

      this.datosGoogle = JSON.parse(datosTemp);
    }
  }

  completarRegistro() {
    // Validaciones
    if (!this.dni || !this.rol) {
      this.mensaje = 'Por favor completa los campos obligatorios';
      return;
    }

    if (this.password && this.password !== this.confirmPassword) {
      this.mensaje = 'Las contraseñas no coinciden';
      return;
    }

    if (this.password && this.password.length < 6) {
      this.mensaje = 'La contraseña debe tener al menos 6 caracteres';
      return;
    }

    // Enviar datos al backend
    const datosCompletos = {
      email: this.datosGoogle.email,
      nombre: this.datosGoogle.nombre,
      picture: this.datosGoogle.picture,
      googleToken: this.datosGoogle.googleToken,
      dni: this.dni.trim(),
      rol: this.rol,
      telefono: this.telefono.trim(),
      password: this.password || null
    };

    this.http.post(`${environment.apiUrl}/api/completar-registro-google.php`, datosCompletos)
      .subscribe({
        next: (response: any) => {
          if (response.success) {
            // Guardar datos del usuario en localStorage
            if (isPlatformBrowser(this.platformId)) {
              localStorage.setItem('usuario', JSON.stringify(response.data));
              sessionStorage.removeItem('google_temp_data');
            }

            this.mensaje = 'Registro completado exitosamente';
            
            // Redirigir según el rol
            setTimeout(() => {
              this.redirectByRole(response.data.rol);
            }, 1000);
          } else {
            this.mensaje = response.error || 'Error al completar el registro';
          }
        },
        error: (error) => {
          this.mensaje = 'Error de conexión con el servidor';
        }
      });
  }

  cancelar() {
    if (isPlatformBrowser(this.platformId)) {
      sessionStorage.removeItem('google_temp_data');
    }
    this.router.navigate(['/']);
  }

  private redirectByRole(rol: string) {
    switch (rol) {
      case 'moderador':
        this.router.navigate(['/panel-moderador']);
        break;
      case 'jefe_equipo':
        this.router.navigate(['/panel-jefe-equipo']);
        break;
      case 'trabajador':
        this.router.navigate(['/panel-trabajador']);
        break;
      default:
        this.router.navigate(['/']);
    }
  }
}
