import { Component, inject, PLATFORM_ID } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { CommonModule, isPlatformBrowser } from '@angular/common';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';

@Component({
  selector: 'app-login',
  imports: [FormsModule, CommonModule],
  templateUrl: './login.html',
})
export class Login {
  private http = inject(HttpClient);
  private router = inject(Router);
  private platformId = inject(PLATFORM_ID);
  email = '';
  password = '';
  dni = '';
  nombre = '';
  telefono = '';
  message = '';
  isLogin = true;

  toggleMode() {
    this.isLogin = !this.isLogin;
    this.message = '';
  }

  onSubmit() {
    const startTime = Date.now();
    console.log('Iniciando petición de login...');

    this.http.post('http://localhost/logisteia/src/www/api/login.php', { email: this.email, password: this.password })
      .subscribe({
        next: (response: any) => {
          const endTime = Date.now();
          console.log(`Petición completada en ${endTime - startTime} ms`);

          if (response.success) {
            this.message = 'Login exitoso';
            // Guardar datos del usuario en localStorage
            if (isPlatformBrowser(this.platformId)) {
              localStorage.setItem('usuario', JSON.stringify(response.data));
            }
            // Redirigir basado en el rol del usuario
            if (response.data.rol === 'administrador') {
              this.router.navigate(['/panel-admin']);
            } else if (response.data.rol === 'registrado') {
              this.router.navigate(['/panel-registrado']);
            } else {
              this.message = 'Rol de usuario desconocido';
            }
          } else {
            this.message = 'Error: ' + response.error;
          }
        },
        error: (error) => {
          const endTime = Date.now();
          console.log(`Error en ${endTime - startTime} ms:`, error);
          this.message = 'Error de conexión: ' + error.message;
        }
      });
  }

  onRegister() {
    const startTime = Date.now();
    console.log('Iniciando petición de registro...', {
      dni: this.dni,
      nombre: this.nombre,
      telefono: this.telefono,
      email: this.email,
      password: '***'
    });

    // Guardar email y password para login automático
    const emailGuardado = this.email;
    const passwordGuardado = this.password;

    this.http.post('http://localhost/logisteia/src/www/api/RegistroUsuario.php', {
      dni: this.dni,
      nombre: this.nombre,
      telefono: this.telefono,
      email: this.email,
      password: this.password
    })
      .subscribe({
        next: (response: any) => {
          const endTime = Date.now();
          console.log(`Petición completada en ${endTime - startTime} ms`, response);

          if (response.success) {
            this.message = 'Registro exitoso. Iniciando sesión...';
            
            // Hacer login automático después del registro
            setTimeout(() => {
              this.email = emailGuardado;
              this.password = passwordGuardado;
              this.onSubmit();
            }, 1000);
          } else {
            this.message = response.error || 'Error desconocido';
          }
        },
        error: (error) => {
          const endTime = Date.now();
          console.log(`Error en ${endTime - startTime} ms:`, error);
          if (error.error && error.error.error) {
            this.message = error.error.error;
          } else {
            this.message = 'Error de conexión: ' + (error.message || 'Error desconocido');
          }
        }
      });
  }
}
