import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core';

import { RouterModule, Router, ActivatedRoute } from '@angular/router';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { LucideAngularModule } from 'lucide-angular';
import { ClientService, ClientDetail } from '../../core/services/client.service';

interface Cliente {
  id: number;
  nombre: string;
  dni: string;
  telefono: string;
  email: string;
  direccion: string;
  ciudad: string;
  codigoPostal: string;
  fechaRegistro: string;
  totalFacturado: number;
  facturasPendientes: number;
}

interface Mascota {
  id: number;
  nombre: string;
  especie: string;
  raza: string;
  edad: string;
  sexo: string;
  chip: string;
  ultimaCita: string;
}

@Component({
  selector: 'app-ficha-cliente',
  standalone: true,
  imports: [RouterModule, FormsModule, ReactiveFormsModule, LucideAngularModule],
  templateUrl: './ficha-clientes.component.html'
})
export class FichaClientesComponent implements OnInit {
  @Input() clienteId: number = 1;

  @Output() navigate = new EventEmitter<{ page: string; data?: any }>();

  constructor(
    private router: Router,
    private route: ActivatedRoute,
    private clientService: ClientService
  ) {}

  private dniStorageKey = 'clientes_dni_map';

  cliente: Cliente = {
    id: 0,
    nombre: '',
    dni: '',
    telefono: '',
    email: '',
    direccion: '',
    ciudad: '',
    codigoPostal: '',
    fechaRegistro: '',
    totalFacturado: 0,
    facturasPendientes: 0
  };

  mascotas: Mascota[] = [];

  ngOnInit(): void {
    const idParam = this.route.snapshot.paramMap.get('id');
    const id = idParam ? Number(idParam) : this.clienteId;
    if (id) {
      this.clienteId = id;
      this.loadCliente(id);
    }
  }

  go(page: string, data?: any) {
    if (page === 'clientes') {
      // Navegacion directa a la lista de clientes
      this.router.navigate(['/clients']);
    } else if (page === 'calendario') {
      this.router.navigate(['/calendario']);
    } else if (page === 'crear-factura') {
      this.router.navigate(['/crear-factura']);
    } else if (page === 'pacientes') {
      this.router.navigate(['/pacientes']);
    } else if (page === 'detalle-mascota' && data?.mascotaId) {
      this.router.navigate(['/pacientes/ficha', data.mascotaId]);
    } else {
      // Opcional: Mantener el emit por si un componente padre aun lo necesita
      this.navigate.emit({ page, data });
    }
  }

  getInitial(name: string): string {
    return name?.trim()?.charAt(0)?.toUpperCase() || '?';
  }

  isDog(especie: string): boolean {
    return (especie || '').toLowerCase() === 'perro';
  }

  private loadCliente(id: number): void {
    this.clientService.getClientById(id).subscribe({
      next: (cliente) => this.applyCliente(cliente),
      error: (err) => console.error('Error al cargar ficha cliente', err)
    });
  }

  private applyCliente(cliente: ClientDetail): void {
    const nombre = `${cliente.firstName || ''} ${cliente.lastName || ''}`.trim();
    this.cliente = {
      id: cliente.id,
      nombre,
      dni: cliente.dni || '',
      telefono: cliente.phone || '',
      email: cliente.email,
      direccion: cliente.address || '',
      ciudad: cliente.city || '',
      codigoPostal: cliente.zipCode || '',
      fechaRegistro: cliente.createdAt || '',
      totalFacturado: 0,
      facturasPendientes: 0
    };

    this.mascotas = (cliente.pets || []).map((pet: any) => ({
      id: pet.id || 0,
      nombre: pet.name || '',
      especie: pet.petType || '',
      raza: pet.breed || 'Desconocida',
      edad: pet.age ? `${pet.age} años` : '',
      sexo: this.normalizeGender(pet.gender),
      chip: pet.chip || '',
      ultimaCita: pet.lastAppointment || 'Ninguna'
    }));
  }

  private normalizeGender(value?: string): string {
    if (!value) return '';
    const normalized = value.toLowerCase();
    if (normalized === 'macho') return 'Macho';
    if (normalized === 'hembra') return 'Hembra';
    return value;
  }
}
