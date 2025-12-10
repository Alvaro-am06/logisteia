import { Component, inject } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { HttpClient } from '@angular/common/http';
import { Router, RouterOutlet } from '@angular/router';

@Component({
  selector: 'app-root',
  imports: [FormsModule, CommonModule, RouterOutlet],
  templateUrl: './app.html',
  styleUrl: './app.css'
})
export class App {
  private http = inject(HttpClient);
  private router = inject(Router);
  email = '';
  password = '';
  message = '';

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
            this.router.navigate(['/panel-admin']);
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
}
