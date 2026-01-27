import { Component, OnInit, inject, PLATFORM_ID } from '@angular/core';
import { CommonModule, isPlatformBrowser } from '@angular/common';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
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
        <div class="content-wrapper">
          <div class="header-section">
            <h1 class="page-title">Mis Presupuestos</h1>
            <p class="subtitle">Gestiona y visualiza todos tus presupuestos</p>
          </div>

          <div class="presupuestos-card">
            <div *ngIf="loading" class="loading-state">
              <div class="spinner"></div>
              <p>Cargando presupuestos...</p>
            </div>
          
            <div *ngIf="error" class="error-message">
              <span class="error-icon">⚠️</span>
              {{ error }}
            </div>

            <div *ngIf="!loading && presupuestos.length === 0" class="empty-state">
              <div class="empty-icon">📋</div>
              <h3>No hay presupuestos</h3>
              <p>Aún no tienes presupuestos registrados en el sistema.</p>
            </div>

            <div *ngIf="!loading && presupuestos.length > 0" class="table-container">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Número</th>
                    <th>Proyecto</th>
                    <th>Cliente</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th class="actions-column">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <tr *ngFor="let p of presupuestos">
                    <td><strong>{{ p.numero_presupuesto }}</strong></td>
                    <td>{{ p.nombre_proyecto }}</td>
                    <td>{{ p.cliente_nombre }}</td>
                    <td class="amount">{{ p.total | currency:'EUR' }}</td>
                    <td>
                      <span [class]="'status-badge status-' + p.estado">{{ p.estado }}</span>
                    </td>
                    <td>{{ p.fecha_creacion | date:'dd/MM/yyyy' }}</td>
                    <td class="actions-cell">
                      <button class="btn btn-view" (click)="verPresupuesto(p)" title="Ver detalles">
                        👁️ Ver
                      </button>
                      <button class="btn btn-delete" (click)="eliminarPresupuesto(p)" title="Eliminar">
                        🗑️ Eliminar
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .dashboard-container {
      display: flex;
      min-height: 100vh;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .main-content {
      flex: 1;
      padding: 2rem;
      overflow-y: auto;
    }

    .content-wrapper {
      max-width: 1400px;
      margin: 0 auto;
    }

    .header-section {
      margin-bottom: 2rem;
    }

    .page-title {
      font-size: 2.5rem;
      font-weight: 700;
      color: white;
      margin: 0 0 0.5rem 0;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
    }

    .subtitle {
      color: rgba(255,255,255,0.9);
      font-size: 1.1rem;
      margin: 0;
    }

    .presupuestos-card {
      background: white;
      border-radius: 16px;
      padding: 2rem;
      box-shadow: 0 10px 40px rgba(0,0,0,0.1);
      min-height: 400px;
    }

    .loading-state {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 4rem;
      color: #6c757d;
    }

    .spinner {
      width: 50px;
      height: 50px;
      border: 4px solid #f3f3f3;
      border-top: 4px solid #667eea;
      border-radius: 50%;
      animation: spin 1s linear infinite;
      margin-bottom: 1rem;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    .error-message {
      padding: 1.5rem;
      background: #fee;
      color: #c33;
      border-radius: 8px;
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    .error-icon {
      font-size: 1.5rem;
    }

    .empty-state {
      text-align: center;
      padding: 4rem 2rem;
      color: #6c757d;
    }

    .empty-icon {
      font-size: 4rem;
      margin-bottom: 1rem;
    }

    .empty-state h3 {
      color: #333;
      margin: 1rem 0;
    }

    .table-container {
      overflow-x: auto;
    }

    .data-table {
      width: 100%;
      border-collapse: collapse;
    }

    .data-table th {
      background: #f8f9fa;
      padding: 1rem;
      text-align: left;
      font-weight: 600;
      color: #495057;
      border-bottom: 2px solid #dee2e6;
      white-space: nowrap;
    }

    .data-table td {
      padding: 1rem;
      border-bottom: 1px solid #dee2e6;
    }

    .data-table tr:hover {
      background: #f8f9fa;
    }

    .amount {
      font-weight: 600;
      color: #28a745;
    }

    .status-badge {
      display: inline-block;
      padding: 0.35rem 0.85rem;
      border-radius: 20px;
      font-size: 0.875rem;
      font-weight: 600;
      text-transform: capitalize;
    }

    .status-pendiente {
      background: #fff3cd;
      color: #856404;
    }

    .status-aprobado {
      background: #d4edda;
      color: #155724;
    }

    .status-rechazado {
      background: #f8d7da;
      color: #721c24;
    }

    .actions-column {
      text-align: center;
      width: 200px;
    }

    .actions-cell {
      display: flex;
      gap: 0.5rem;
      justify-content: center;
    }

    .btn {
      padding: 0.5rem 1rem;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 0.875rem;
      font-weight: 500;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
    }

    .btn-view {
      background: #007bff;
      color: white;
    }

    .btn-view:hover {
      background: #0056b3;
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0,123,255,0.3);
    }

    .btn-delete {
      background: #dc3545;
      color: white;
    }

    .btn-delete:hover {
      background: #c82333;
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(220,53,69,0.3);
    }

    @media (max-width: 768px) {
      .page-title {
        font-size: 2rem;
      }
      
      .actions-cell {
        flex-direction: column;
      }
      
      .data-table {
        font-size: 0.875rem;
      }
    }
  `]
})
export class PresupuestosComponent implements OnInit {
  private http = inject(HttpClient);
  private router = inject(Router);
  private platformId = inject(PLATFORM_ID);
  
  presupuestos: Presupuesto[] = [];
  loading = false;
  error = '';
  usuarioDni = '';
  usuarioRol = '';

  ngOnInit() {
    if (isPlatformBrowser(this.platformId)) {
      const usuarioStr = localStorage.getItem('usuario');
      if (usuarioStr) {
        const usuario = JSON.parse(usuarioStr);
        this.usuarioDni = usuario.dni;
        this.usuarioRol = usuario.rol;
        
        // Solo jefes de equipo pueden ver presupuestos
        if (this.usuarioRol === 'jefe_equipo') {
          this.cargarPresupuestos();
        } else {
          this.error = 'No tienes permisos para ver presupuestos';
        }
      }
    }
  }

  cargarPresupuestos() {
    this.loading = true;
    this.error = '';

    this.http.get<any>(`${environment.apiUrl}/api/mis-presupuestos-wizard.php?dni=${this.usuarioDni}`)
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

  verPresupuesto(presupuesto: Presupuesto) {
    // Navegar a detalle de presupuesto
    window.open(`${environment.apiUrl}/api/exportar-presupuesto-pdf.php?numero=${presupuesto.numero_presupuesto}`, '_blank');
  }

  eliminarPresupuesto(presupuesto: Presupuesto) {
    if (!confirm(`¿Estás seguro de eliminar el presupuesto ${presupuesto.numero_presupuesto}?`)) {
      return;
    }

    this.http.post(`${environment.apiUrl}/api/eliminar-presupuesto.php`, {
      numero_presupuesto: presupuesto.numero_presupuesto
    }).subscribe({
      next: (response: any) => {
        if (response.success) {
          alert('Presupuesto eliminado correctamente');
          this.cargarPresupuestos();
        } else {
          alert('Error al eliminar: ' + (response.error || 'Error desconocido'));
        }
      },
      error: (err) => {
        alert('Error de conexión al eliminar presupuesto');
        console.error('Error:', err);
      }
    });
  }
}
