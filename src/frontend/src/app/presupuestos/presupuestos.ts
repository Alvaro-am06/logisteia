import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { HttpClient } from '@angular/common/http';
import { SidebarComponent } from '../components/sidebar/sidebar.component';
import { environment } from '../../environments/environment';

interface Presupuesto {
  numero_presupuesto: string;
  usuario_dni: string;
  nombre_proyecto: string;
  cliente_nombre: string;
  total: number;
  estado: string;
  fecha_creacion: string;
}

@Component({
  selector: 'app-presupuestos',
  standalone: true,
  imports: [CommonModule, SidebarComponent],
  template: `
    <div class="dashboard-container">
      <app-sidebar></app-sidebar>
      
      <div class="main-content">
        <div class="content-header">
          <h1>Mis Presupuestos</h1>
        </div>

        <div class="presupuestos-container">
          <div *ngIf="loading" class="loading">Cargando presupuestos...</div>
          
          <div *ngIf="error" class="error-message">{{ error }}</div>

          <div *ngIf="!loading && presupuestos.length === 0" class="empty-state">
            <p>No tienes presupuestos registrados.</p>
          </div>

          <table *ngIf="!loading && presupuestos.length > 0" class="presupuestos-table">
            <thead>
              <tr>
                <th>Número</th>
                <th>Proyecto</th>
                <th>Cliente</th>
                <th>Total</th>
                <th>Estado</th>
                <th>Fecha</th>
              </tr>
            </thead>
            <tbody>
              <tr *ngFor="let p of presupuestos">
                <td>{{ p.numero_presupuesto }}</td>
                <td>{{ p.nombre_proyecto }}</td>
                <td>{{ p.cliente_nombre }}</td>
                <td>{{ p.total | currency:'EUR' }}</td>
                <td>
                  <span [class]="'badge badge-' + p.estado">{{ p.estado }}</span>
                </td>
                <td>{{ p.fecha_creacion | date:'dd/MM/yyyy' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .dashboard-container {
      display: flex;
      min-height: 100vh;
    }

    .main-content {
      flex: 1;
      padding: 2rem;
      background: #f5f5f5;
    }

    .content-header h1 {
      color: #333;
      margin-bottom: 2rem;
    }

    .presupuestos-container {
      background: white;
      border-radius: 8px;
      padding: 2rem;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .presupuestos-table {
      width: 100%;
      border-collapse: collapse;
    }

    .presupuestos-table th,
    .presupuestos-table td {
      padding: 1rem;
      text-align: left;
      border-bottom: 1px solid #eee;
    }

    .presupuestos-table th {
      background: #f8f9fa;
      font-weight: 600;
      color: #495057;
    }

    .presupuestos-table tr:hover {
      background: #f8f9fa;
    }

    .badge {
      padding: 0.25rem 0.75rem;
      border-radius: 12px;
      font-size: 0.875rem;
      font-weight: 500;
    }

    .badge-pendiente {
      background: #ffc107;
      color: #000;
    }

    .badge-aprobado {
      background: #28a745;
      color: white;
    }

    .badge-rechazado {
      background: #dc3545;
      color: white;
    }

    .loading,
    .empty-state {
      text-align: center;
      padding: 3rem;
      color: #6c757d;
    }

    .error-message {
      padding: 1rem;
      background: #f8d7da;
      color: #721c24;
      border-radius: 4px;
      margin-bottom: 1rem;
    }
  `]
})
export class PresupuestosComponent implements OnInit {
  private http = inject(HttpClient);
  
  presupuestos: Presupuesto[] = [];
  loading = false;
  error = '';

  ngOnInit() {
    this.cargarPresupuestos();
  }

  cargarPresupuestos() {
    this.loading = true;
    this.error = '';

    // Obtener DNI del usuario desde localStorage
    const usuarioStr = localStorage.getItem('usuario');
    if (!usuarioStr) {
      this.error = 'Usuario no autenticado';
      this.loading = false;
      return;
    }

    const usuario = JSON.parse(usuarioStr);
    
    this.http.get<any>(`${environment.apiUrl}/api/mis-presupuestos-wizard.php?dni=${usuario.dni}`)
      .subscribe({
        next: (response) => {
          this.loading = false;
          if (response && response.success) {
            this.presupuestos = response.data || [];
          } else {
            this.error = response.error || 'Error al cargar presupuestos';
          }
        },
        error: (err) => {
          this.loading = false;
          this.error = 'Error de conexión al cargar presupuestos';
          console.error('Error:', err);
        }
      });
  }
}
