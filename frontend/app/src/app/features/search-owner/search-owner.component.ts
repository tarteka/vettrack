import { CommonModule } from '@angular/common';
import {
  Component,
  ElementRef,
  EventEmitter,
  HostListener,
  Input,
  OnChanges,
  Output,
  SimpleChanges,
} from '@angular/core';
import { FormsModule } from '@angular/forms';
import { LucideAngularModule } from 'lucide-angular';
import { Search } from 'lucide-angular';

export interface Owner {
  id: number;
  nombre: string;
  dni: string;
  mascotas?: { id: number; nombre: string; especie: string }[];
}

@Component({
  selector: 'app-search-owner',
  standalone: true,
  imports: [CommonModule, FormsModule, LucideAngularModule],
  templateUrl: './search-owner.component.html',
})
export class SearchOwnerComponent implements OnChanges {
  @Input({ required: true }) owners: Owner[] = [];
  @Input() placeholder = 'Buscar propietario...';
  @Input() selectedOwner: Owner | null = null;
  @Input() disabled = false;

  @Output() selectOwner = new EventEmitter<Owner | null>();

  readonly icons = { Search };

  searchTerm = '';
  showResults = false;
  filteredOwners: Owner[] = [];
  isEditing = false;

  constructor(private host: ElementRef<HTMLElement>) {}

  ngOnChanges(changes: SimpleChanges): void {
    // Sincronizar con selectedOwner cuando cambie desde fuera (y no estamos editando)
    if (changes['selectedOwner'] && !this.isEditing) {
      this.searchTerm = this.selectedOwner?.nombre ?? '';
      this.filterOwners();
    }
    if (changes['owners']) {
      this.filterOwners();
    }
  }

  onFocus(): void {
    if (this.disabled) return;
    if (this.searchTerm.length > 0) this.showResults = true;
  }

  onInputChange(value: string): void {
    if (this.disabled) return;
    this.searchTerm = value;
    this.isEditing = true;

    this.filterOwners();
    this.showResults = value.length > 0;

    // Si había owner seleccionado y el usuario cambia el texto -> ya no hay selección
    if (this.selectedOwner && value !== this.selectedOwner.nombre) {
      this.selectOwner.emit(null);
    }
  }

  handleSelect(owner: Owner): void {
    if (this.disabled) return;
    this.searchTerm = owner.nombre;
    this.showResults = false;
    this.isEditing = false;
    this.filteredOwners = [];
    this.selectOwner.emit(owner);
  }

  private filterOwners(): void {
    const term = this.searchTerm.trim().toLowerCase();

    if (!term) {
      this.filteredOwners = [];
      this.showResults = false;
      return;
    }

    this.filteredOwners = (this.owners ?? []).filter((o) => {
      const nombre = (o.nombre || '').toLowerCase();
      const dni = (o.dni || '').toLowerCase();
      return (
        nombre.includes(term) ||
        dni.includes(term)
      );
    });
  }

  // Cerrar dropdown al hacer click fuera
  @HostListener('document:mousedown', ['$event'])
  onDocumentMouseDown(event: MouseEvent): void {
    const target = event.target as Node | null;
    if (!target) return;

    if (!this.host.nativeElement.contains(target)) {
      this.showResults = false;
    }
  }
}
