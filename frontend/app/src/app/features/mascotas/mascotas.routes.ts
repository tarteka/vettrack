import { Routes } from '@angular/router';
import { MascotasComponent } from './mascotas.component';
import { FichaMascotaComponent } from '../ficha-mascota/ficha-mascota.component';

export const MASCOTAS_ROUTES: Routes = [
    {
        path: '',
        component: MascotasComponent
    },
    {
        path: 'ficha/:id', // Ruta: /mascotas/ficha/:id
        component: FichaMascotaComponent
    }
];