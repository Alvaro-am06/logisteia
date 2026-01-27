import { Component, inject, PLATFORM_ID, AfterViewInit, ElementRef, ViewChild } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { CommonModule, isPlatformBrowser } from '@angular/common';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { environment } from '../../environments/environment';

declare const google: any;

@Component({
  selector: 'app-login',
  imports: [FormsModule, CommonModule],
  templateUrl: './login.html',
})
export class Login implements AfterViewInit {
  @ViewChild('googleButtonDiv', { read: ElementRef }) googleButtonDiv?: ElementRef;
  
  private http = inject(HttpClient);
  private router = inject(Router);
  private platformId = inject(PLATFORM_ID);
  email = '';
  password = '';
  dni = '';
  nombre = '';
  telefono = '';
  rol = 'trabajador'; // trabajador o jefe_equipo
  message = '';
  isLogin = true;

  ngAfterViewInit() {
    if (isPlatformBrowser(this.platformId)) {
      // Pequeño delay para asegurar que el DOM esté listo
      setTimeout(() => {
        const observer = new IntersectionObserver((entries) => {
          entries.forEach(entry => {
            if (entry.isIntersecting) {
              entry.target.classList.add('show');
            }
          });
        }, {
          threshold: 0.2
        });

        // Observar todos los elementos con clase scroll-animate
        const elements = document.querySelectorAll('.scroll-animate');
        elements.forEach(el => {
          observer.observe(el);
        });
      }, 100);
      
      // Esperar a que el DOM esté completamente renderizado después de la hidratación
      this.waitForElement();
    }
  }

  private waitForElement() {
    // Usar MutationObserver para detectar cuando el elemento esté disponible
    const checkElement = () => {
      const element = document.getElementById('googleSignInButton');
      if (element) {
        this.initializeGoogleSignIn();
      } else {
        // Verificar cada 100ms
        setTimeout(checkElement, 100);
      }
    };
    
    checkElement();
  }

  private attemptCount = 0;
  private maxAttempts = 10;

  initializeGoogleSignIn() {
    if (this.attemptCount >= this.maxAttempts) {
      return;
    }
    
    this.attemptCount++;
    
    if (typeof google !== 'undefined' && google.accounts?.id) {
      const buttonContainer = document.getElementById('googleSignInButton');
      
      if (!buttonContainer) {
        return;
      }
      
      try {
        google.accounts.id.initialize({
          client_id: environment.googleClientId,
          callback: this.handleGoogleSignIn.bind(this),
          auto_select: false,
          cancel_on_tap_outside: true
        });
        
        google.accounts.id.renderButton(
          buttonContainer,
          { 
            theme: 'outline', 
            size: 'large',
            width: 400,
            text: 'signin_with',
            locale: 'es'
          }
        );
      } catch (error) {
      }
    } else {
      setTimeout(() => this.initializeGoogleSignIn(), 500);
    }
  }

  handleGoogleSignIn(response: any) {
    // Decodificar el JWT token de Google
    const payload = this.decodeJWT(response.credential);
    
    // Enviar datos de Google al backend para verificar si existe el usuario
    this.http.post(`${environment.apiUrl}/api/login-google.php`, {
      googleToken: response.credential,
      email: payload.email,
      nombre: payload.name,
      picture: payload.picture,
      checkOnly: true  // Solo verificar, no crear usuario aún
    })
    .subscribe({
      next: (res: any) => {
        console.log('Respuesta del backend:', res);
        if (res.success && res.data && res.data.exists === true) {
          // Usuario ya existe - login directo
          this.message = 'Login con Google exitoso';
          console.log('Usuario encontrado:', res.data.usuario);
          if (isPlatformBrowser(this.platformId)) {
            localStorage.setItem('usuario', JSON.stringify(res.data.usuario));
            console.log('Usuario guardado en localStorage');
            // Dar tiempo para que se guarde en localStorage antes de redirigir
            setTimeout(() => {
              console.log('Redirigiendo a rol:', res.data.usuario.rol);
              this.redirectByRole(res.data.usuario.rol);
            }, 100);
          }
        } else if (res.success && res.data && res.data.exists === false) {
          // Usuario nuevo - redirigir a completar registro
          console.log('Usuario NO existe, redirigiendo a completar-registro');
          if (isPlatformBrowser(this.platformId)) {
            sessionStorage.setItem('google_temp_data', JSON.stringify({
              email: payload.email,
              nombre: payload.name,
              picture: payload.picture,
              googleToken: response.credential
            }));
            console.log('Datos temporales guardados en sessionStorage');
            // Forzar navegación en el navegador
            setTimeout(() => {
              console.log('Navegando a completar-registro');
              this.router.navigate(['/completar-registro']);
            }, 100);
          }
        } else {
          this.message = 'Error inesperado del servidor';
        }
      },
      error: (error) => {
        this.message = 'Error de conexión con Google. Por favor, intente nuevamente.';
      }
    });
  }

  decodeJWT(token: string): any {
    try {
      const base64Url = token.split('.')[1];
      const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
      const jsonPayload = decodeURIComponent(atob(base64).split('').map(function(c) {
        return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
      }).join(''));
      return JSON.parse(jsonPayload);
    } catch (error) {
      return null;
    }
  }

  redirectByRole(rol: string) {
    if (rol === 'moderador') {
      this.router.navigate(['/panel-moderador']);
    } else if (rol === 'administrador') {
      this.router.navigate(['/panel-admin']);
    } else if (rol === 'jefe_equipo') {
      this.router.navigate(['/panel-jefe-equipo']);
    } else if (rol === 'registrado' || rol === 'trabajador') {
      this.router.navigate(['/panel-registrado']);
    } else {
      this.message = 'Rol de usuario desconocido';
    }
  }

  toggleMode() {
    this.isLogin = !this.isLogin;
    this.message = '';
  }

  onSubmit() {
    if (!this.email || !this.password) {
      this.message = 'Por favor, completa todos los campos';
      return;
    }
    
    this.http.post(`${environment.apiUrl}/api/login.php`, { email: this.email, password: this.password })
      .subscribe({
        next: (response: any) => {
          if (response.success) {
            this.message = 'Login exitoso';
            // Guardar datos del usuario en localStorage
            if (isPlatformBrowser(this.platformId)) {
              localStorage.setItem('usuario', JSON.stringify(response.data));
            }
            // Redirigir basado en el rol del usuario
            this.redirectByRole(response.data.rol);
          } else {
            this.message = 'Error: ' + response.error;
          }
        },
        error: (error) => {
          this.message = 'Error de conexión. Por favor, intente nuevamente.';
        }
      });
  }

  onRegister() {
    // Guardar email y password para login automático
    const emailGuardado = this.email;
    const passwordGuardado = this.password;

    this.http.post(`${environment.apiUrl}/api/RegistroUsuario.php`, {
      dni: this.dni,
      nombre: this.nombre,
      telefono: this.telefono,
      email: this.email,
      password: this.password,
      rol: this.rol
    })
      .subscribe({
        next: (response: any) => {
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
          if (error.error && error.error.error) {
            this.message = error.error.error;
          } else {
            this.message = 'Error de conexión. Por favor, intente nuevamente.';
          }
        }
      });
  }
}
