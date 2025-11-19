import { Component, OnInit } from '@angular/core';
import { UsuariosService } from './usuarios.service';

@Component({
  selector: 'app-usuarios-list',
  templateUrl: './usuarios-list.component.html',
  styleUrls: ['./usuarios-list.component.scss']
})
export class UsuariosListComponent implements OnInit {
  usuarios: any[] = [];
  constructor(private usuariosService: UsuariosService) {}
  ngOnInit() {
    this.usuariosService.getUsuarios().subscribe(data => this.usuarios = data);
  }
}
