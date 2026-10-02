import { CommonModule } from '@angular/common';
import { Component, EventEmitter, OnInit, Output, inject, ChangeDetectionStrategy } from '@angular/core';
import { LucideDynamicIcon, provideLucideIcons, LucidePlus as Plus, LucideArrowLeft as ArrowLeft, LucideDog as Dog, LucideCat as Cat } from '@lucide/angular';
import { Router } from '@angular/router';
import { Mascota } from '../../core/models/pet-user.model';
import { PetUserService } from '../../core/services/pet-user.service';



@Component({
  selector: 'app-mascotas',
  standalone: true,
  imports: [
    CommonModule,
    LucideDynamicIcon,
  ],
  providers: [
    provideLucideIcons(Plus, ArrowLeft, Dog, Cat)
  ],
  changeDetection: ChangeDetectionStrategy.Eager,
  templateUrl: './mascotas.component.html',
})
export class MascotasComponent implements OnInit{
  @Output() navigate = new EventEmitter<{ page: string; data?: any }>();
  constructor(private router: Router) {}
  private petService = inject(PetUserService);
  mascotas: Mascota[] = [];

  ngOnInit() {
    this.petService.getPets().subscribe({
      next: (data: Mascota[]) => {
        this.mascotas = data;
      },
      error: (err) => console.error('Error al cargar mascotas', err)
    });
  }

  // Icons
  readonly Plus = Plus;
  readonly ArrowLeft = ArrowLeft;
  readonly Dog = Dog;
  readonly Cat = Cat;



  onNavigate(page: string, mascota?: any) {
    if (page === 'ficha-mascota' && mascota) {
      // Navega a /mascotas/ficha/ID_DE_LA_MASCOTA
      this.router.navigate(['/mascotas/ficha', mascota.id]); 
    } else {
      this.router.navigate([`/${page}`]);
    }
  }

  isDog(m: Mascota): boolean {
    return (m.petType.name || '').toLowerCase() === 'perro';
  }
}
