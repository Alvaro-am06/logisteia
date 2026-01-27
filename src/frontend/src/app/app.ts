import { Component, inject } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { HttpClient } from '@angular/common/http';

@Component({
  selector: 'app-root',
  imports: [FormsModule, CommonModule],
  templateUrl: './app.html',
  styleUrl: './app.scss'
})
export class App {
  private http = inject(HttpClient);
  email = '';
  password = '';
  message = '';

onSubmit() {
  this.http.post('http://localhost/logisteia/src/www/api/login.php', { email: this.email, password: this.password })
    .subscribe({
      next: (response: any) => {
        if (response.success) {
          this.message = 'Login exitoso: ' + JSON.stringify(response.data);
        } else {
          this.message = 'Error: ' + response.error;
        }
      },
      error: (error) => {
        this.message = 'Error de conexión: ' + error.message;
      }
    });
  }
}
