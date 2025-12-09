import { Component, signal, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { HttpClient, HttpErrorResponse } from '@angular/common/http';

@Component({
  selector: 'app-root',
  imports: [CommonModule, FormsModule],
  templateUrl: './app.html',
  styleUrl: './app.scss',
})
export class App {
  protected readonly title = signal('angular-app');
  email = '';
  password = '';
  message = '';

  private http = inject(HttpClient);

  onSubmit() {
    this.http.post('/api/login', { email: this.email, password: this.password }).subscribe({
      next: (response: unknown) => {
        const res = response as { success?: boolean; error?: string; data?: unknown };
        if (res.success) {
          this.message = 'Login exitoso: ' + JSON.stringify(res.data);
        } else {
          this.message = 'Error: ' + (res.error || 'Desconocido');
        }
      },
      error: (err: HttpErrorResponse) => {
        this.message = 'Error de conexión: ' + (err?.message || err?.statusText || JSON.stringify(err));
      }
    });
  }
}
