import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { AuthService, LoginRequest } from '../../services/auth.service';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.css']
})
export class LoginComponent {
  private authService = inject(AuthService);
  private router = inject(Router);

  loginData: LoginRequest = {
    email: '',
    password: ''
  };

  loading = false;
  error = '';

  constructor() {
    // Si ya está logueado, redirigir al dashboard
    if (this.authService.isLoggedIn()) {
      this.router.navigate(['/dashboard']);
    }
  }

  onSubmit() {
    if (!this.loginData.email || !this.loginData.password) {
      this.error = 'Por favor complete todos los campos';
      return;
    }

    this.loading = true;
    this.error = '';

    this.authService.login(this.loginData).subscribe({
      next: (response) => {
        this.loading = false;
        if (response.success && response.data) {
          // Guardar sesión y redirigir
          this.authService.setSession('logged_in');
          this.router.navigate(['/dashboard']);
        } else {
          this.error = response.error || 'Error en el login';
        }
      },
      error: (err) => {
        this.loading = false;
        this.error = 'Error de conexión con el servidor';
        console.error('Error de login:', err);
      }
    });
  }
}