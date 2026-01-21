import { Routes } from '@angular/router';
import { ClientesComponent } from './clientes.component'; 
import { FichaClientesComponent } from '../ficha-clientes/ficha-clientes.component';


export const CLIENTES_ROUTES: Routes = [
    {
    path: '',
    component: ClientesComponent
    },
    {
    path: 'ficha/:id',
    component: FichaClientesComponent
    }
];