import { Component, inject, OnInit, PLATFORM_ID } from '@angular/core';
import { CommonModule, isPlatformBrowser } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { environment } from '../../environments/environment';

interface Cliente {
  id: number;
  nombre: string;
  email: string;
  empresa: string;
  telefono: string;
  direccion: string;
  cif_nif: string;
}

interface FormularioPresupuesto {
  nombreProyecto: string;
  descripcionProyecto: string;
  clienteSeleccionadoId: number | null;
  categoriaPrincipal: string;
  tiempoEstimado: string;
  presupuestoAproximado: string;
  tecnologiasSeleccionadas: string[];
  fechaInicio: string;
  plazoEntrega: string;
  prioridad: string;
  notasAdicionales: string;
  metodologia: string;
}

@Component({
  selector: 'app-presupuesto-wizard',
  imports: [CommonModule, FormsModule],
  templateUrl: './presupuesto-wizard.html'
})
export class PresupuestoWizard implements OnInit {
  private http = inject(HttpClient);
  private router = inject(Router);
  private platformId = inject(PLATFORM_ID);

  pasoActual: number = 1;
  totalPasos: number = 6;
  
  formulario: FormularioPresupuesto = {
    nombreProyecto: '',
    descripcionProyecto: '',
    clienteSeleccionadoId: null,
    categoriaPrincipal: '',
    tiempoEstimado: '',
    presupuestoAproximado: '',
    tecnologiasSeleccionadas: [],
    fechaInicio: '',
    plazoEntrega: '',
    prioridad: 'media',
    notasAdicionales: '',
    metodologia: ''
  };
  
  categorias: string[] = [
    'Desarrollo Web',
    'Desarrollo Móvil',
    'Base de Datos',
    'UI/UX Design',
    'Testing',
    'DevOps',
    'Infraestructura',
    'Consultoría',
    'Mantenimiento',
    'Otros'
  ];
  
  tecnologiasDisponibles: { [categoria: string]: string[] } = {
    'Desarrollo Web': ['React', 'Angular', 'Vue.js', 'Node.js', 'PHP Laravel', 'Python Django', 'Next.js', 'Express', 'TypeScript', 'JavaScript'],
    'Desarrollo Móvil': ['React Native', 'Flutter', 'Swift', 'Kotlin', 'Ionic', 'Xamarin'],
    'Base de Datos': ['MySQL', 'PostgreSQL', 'MongoDB', 'Redis', 'Firebase', 'SQL Server'],
    'UI/UX Design': ['Figma', 'Adobe XD', 'Sketch', 'InVision', 'Photoshop', 'Illustrator'],
    'Testing': ['Jest', 'Cypress', 'Selenium', 'Playwright', 'JUnit', 'PyTest'],
    'DevOps': ['Docker', 'Kubernetes', 'Jenkins', 'GitHub Actions', 'GitLab CI', 'AWS', 'Azure'],
    'Infraestructura': ['AWS', 'Azure', 'Google Cloud', 'DigitalOcean', 'Heroku', 'Nginx', 'Apache'],
    'Consultoría': ['Análisis de Requisitos', 'Arquitectura de Software', 'Code Review', 'Auditoría de Seguridad'],
    'Mantenimiento': ['Monitoreo', 'Actualizaciones', 'Backups', 'Soporte Técnico', 'Corrección de Bugs'],
    'Otros': ['APIs REST', 'GraphQL', 'OAuth', 'JWT', 'Stripe', 'PayPal', 'SEO']
  };
  
  tecnologiasDisponiblesFiltradas: string[] = [];
  erroresPaso: string[] = [];
  message: string = '';
  loading: boolean = false;
  usuarioDni: string = '';
  usuarioNombre: string = '';
  usuarioRol: string = '';
  
  metodologiasDisponibles: string[] = [
    'Scrum',
    'Kanban',
    'Waterfall (Cascada)',
    'Agile',
    'Lean',
    'XP (Extreme Programming)',
    'DevOps',
    'Six Sigma'
  ];
  
  clientes: Cliente[] = [];
  clienteSeleccionadoId: number | null = null;
  clienteSeleccionado: Cliente | null = null;
  cargandoClientes: boolean = true;

  ngOnInit() {
    if (!isPlatformBrowser(this.platformId)) {
      return;
    }

    const usuarioData = localStorage.getItem('usuario');
    if (!usuarioData) {
      this.router.navigate(['/login']);
      return;
    }

    const usuario = JSON.parse(usuarioData);
    this.usuarioDni = usuario.dni;
    this.usuarioNombre = usuario.nombre || 'Usuario';
    this.usuarioRol = usuario.rol || 'Usuario';
    
    // Cargar clientes
    this.cargarClientes();
  }

  cargarClientes() {
    this.cargandoClientes = true;
    const headers = {
      'X-User-DNI': this.usuarioDni
    };
    
    this.http.get<any>(`${environment.apiUrl}/api/clientes.php`, { headers })
      .subscribe({
        next: (response) => {
          if (response.clientes && Array.isArray(response.clientes)) {
            this.clientes = response.clientes;
          } else{
            this.clientes = [];
          }
          this.cargandoClientes = false;
        },
        error: (err) => {
          this.clientes = [];
          this.cargandoClientes = false;
        }
      });
  }

  seleccionarCliente(clienteId: number | string | null) {
    if (clienteId) {
      // Convertir a número si es string
      const id = typeof clienteId === 'string' ? parseInt(clienteId, 10) : clienteId;
      
      const cliente = this.clientes.find(c => c.id === id);
      
      if (cliente) {
        this.clienteSeleccionado = cliente;
        this.formulario.clienteSeleccionadoId = id;
      }
    } else {
      this.clienteSeleccionado = null;
      this.formulario.clienteSeleccionadoId = null;
    }
  }

  filtrarTecnologias() {
    if (this.formulario.categoriaPrincipal) {
      this.tecnologiasDisponiblesFiltradas = this.tecnologiasDisponibles[this.formulario.categoriaPrincipal] || [];
    } else {
      this.tecnologiasDisponiblesFiltradas = [];
    }
  }

  toggleTecnologia(tecnologia: string) {
    const index = this.formulario.tecnologiasSeleccionadas.indexOf(tecnologia);
    if (index > -1) {
      this.formulario.tecnologiasSeleccionadas.splice(index, 1);
    } else {
      this.formulario.tecnologiasSeleccionadas.push(tecnologia);
    }
  }

  isTecnologiaSeleccionada(tecnologia: string): boolean {
    return this.formulario.tecnologiasSeleccionadas.includes(tecnologia);
  }

  validarPaso1(): boolean {
    this.erroresPaso = [];
    
    if (!this.formulario.nombreProyecto.trim()) {
      this.erroresPaso.push('El nombre del proyecto es obligatorio');
    }
    if (!this.formulario.descripcionProyecto.trim()) {
      this.erroresPaso.push('La descripción del proyecto es obligatoria');
    }
    if (!this.formulario.clienteSeleccionadoId) {
      this.erroresPaso.push('Debe seleccionar un cliente');
    }
    
    return this.erroresPaso.length === 0;
  }

  validarPaso2(): boolean {
    this.erroresPaso = [];
    
    if (!this.formulario.categoriaPrincipal) {
      this.erroresPaso.push('Debe seleccionar una categoría principal');
    }
    if (!this.formulario.tiempoEstimado) {
      this.erroresPaso.push('Debe seleccionar un tiempo estimado');
    }
    if (!this.formulario.presupuestoAproximado) {
      this.erroresPaso.push('Debe seleccionar un presupuesto aproximado');
    }
    
    return this.erroresPaso.length === 0;
  }

  validarPaso3(): boolean {
    this.erroresPaso = [];
    
    if (this.formulario.tecnologiasSeleccionadas.length === 0) {
      this.erroresPaso.push('Debe seleccionar al menos una tecnología');
    }
    
    return this.erroresPaso.length === 0;
  }

  validarPaso4(): boolean {
    this.erroresPaso = [];
    
    if (!this.formulario.fechaInicio) {
      this.erroresPaso.push('Debe seleccionar una fecha de inicio');
    }
    if (!this.formulario.plazoEntrega) {
      this.erroresPaso.push('Debe seleccionar un plazo de entrega');
    }
    
    return this.erroresPaso.length === 0;
  }

  validarPaso5(): boolean {
    this.erroresPaso = [];
    
    if (!this.formulario.metodologia) {
      this.erroresPaso.push('Debe seleccionar una metodología');
    }
    
    return this.erroresPaso.length === 0;
  }

  siguientePaso() {
    let valido = true;
    switch(this.pasoActual) {
      case 1: valido = this.validarPaso1(); break;
      case 2: valido = this.validarPaso2(); this.filtrarTecnologias(); break;
      case 3: valido = this.validarPaso3(); break;
      case 4: valido = this.validarPaso4(); break;
      case 5: valido = this.validarPaso5(); break;
    }
    if (valido && this.pasoActual < this.totalPasos) {
      this.pasoActual++;
      this.erroresPaso = [];
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }
  }

  pasoAnterior() {
    if (this.pasoActual > 1) {
      this.pasoActual--;
      this.erroresPaso = [];
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }
  }

  obtenerProgreso(): number {
    return (this.pasoActual / this.totalPasos) * 100;
  }

  getFechaMinima(): string {
    const hoy = new Date();
    const año = hoy.getFullYear();
    const mes = String(hoy.getMonth() + 1).padStart(2, '0');
    const dia = String(hoy.getDate()).padStart(2, '0');
    return `${año}-${mes}-${dia}`;
  }

  enviarPresupuesto() {
    // Validar que hay un cliente seleccionado
    if (!this.clienteSeleccionado) {
      this.message = 'Debe seleccionar un cliente';
      return;
    }

    // Preparar datos completos del wizard para crear proyecto Y presupuesto
    const datosCompletos = {
      // Datos del proyecto
      nombre: this.formulario.nombreProyecto.trim(),
      descripcion: this.formulario.descripcionProyecto.trim(),
      cliente_id: this.formulario.clienteSeleccionadoId,
      tecnologias: this.formulario.tecnologiasSeleccionadas,
      fecha_inicio: this.formulario.fechaInicio,
      notas: this.formulario.notasAdicionales.trim(),
      
      // Datos adicionales del wizard para el presupuesto
      categoriaPrincipal: this.formulario.categoriaPrincipal,
      tiempoEstimado: this.formulario.tiempoEstimado,
      presupuestoAproximado: this.formulario.presupuestoAproximado,
      plazoEntrega: this.formulario.plazoEntrega,
      prioridad: this.formulario.prioridad,
      metodologia: this.formulario.metodologia
    };

    this.message = 'Guardando proyecto y presupuesto...';
    this.loading = true;

    this.http.post(`${environment.apiUrl}/api/proyectos.php`, datosCompletos)
      .subscribe({
        next: (response: any) => {
          this.loading = false;
          if (response.success) {
            this.message = `Proyecto y presupuesto creados correctamente`;
            
            setTimeout(() => {
              this.router.navigate(['/mis-proyectos']);
            }, 2000);
          } else {
            this.message = 'Error: ' + (response.error || 'No se pudo crear el proyecto');
          }
        },
        error: (error) => {
          this.loading = false;
          const errorMsg = error.error?.error || error.message || 'Error de conexión';
          this.message = 'Error: ' + errorMsg;
        }
      });
  }

  cancelar() {
    if (confirm('¿Está seguro de que desea cancelar? Se perderán todos los datos.')) {
      this.router.navigate(['/mis-proyectos']);
    }
  }

  cerrarSesion() {
    if (confirm('¿Está seguro de que desea cerrar sesión?')) {
      if (isPlatformBrowser(this.platformId)) {
        localStorage.removeItem('token');
        localStorage.removeItem('usuario');
      }
      this.router.navigate(['/login']);
    }
  }
}
