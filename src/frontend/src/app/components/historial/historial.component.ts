import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { HistorialService, HistorialItem } from '../../services/historial.service';

@Component({
  selector: 'app-historial',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './historial.component.html',
  styleUrls: ['./historial.component.css']
})
export class HistorialComponent implements OnInit {
  private historialService = inject(HistorialService);

  historial: HistorialItem[] = [];
  loading = true;
  error = '';

  ngOnInit() {
    this.loadHistorial();
  }

  loadHistorial() {
    this.loading = true;
    this.error = '';

    this.historialService.getHistorial().subscribe({
      next: (response) => {
        this.loading = false;
        if (response.success && response.data) {
          this.historial = response.data;
        } else {
          this.error = response.error || 'Error al cargar historial';
        }
      },
      error: (err) => {
        this.loading = false;
        this.error = 'Error de conexión con el servidor';
      }
    });
  }

  getAccionClass(accion: string): string {
    switch (accion?.toLowerCase()) {
      case 'activar':
        return 'accion-activar';
      case 'suspender':
        return 'accion-suspender';
      case 'eliminar':
        return 'accion-eliminar';
      default:
        return 'accion-desconocida';
    }
  }
}