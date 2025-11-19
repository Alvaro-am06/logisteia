import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { UsuariosService } from './usuarios.service';

@Component({
  selector: 'app-usuarios-list',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './usuarios-list.component.html',
  styleUrls: ['./usuarios-list.component.scss']
})
export class UsuariosListComponent implements OnInit {
  usuarios: Usuario[] = [];
  usuariosService = inject(UsuariosService);
  ngOnInit() {
    this.usuariosService.getUsuarios().subscribe(data => this.usuarios = data);
  }
}

interface Usuario {
  dni: string;
  nombre: string;
  email: string;
  rol: string;
}
