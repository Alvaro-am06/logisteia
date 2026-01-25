import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private apiUrl = '/api/login.php'; // Usará el proxy
  private http = inject(HttpClient);

  login(email: string, password: string): Observable<unknown> {
    return this.http.post(this.apiUrl, { email, password });
  }
}