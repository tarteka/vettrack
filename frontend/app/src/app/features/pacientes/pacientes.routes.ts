import { Routes } from '@angular/router';
import { PacientesComponent } from './pacientes.component';
import { FichaPacienteComponent } from '../ficha-paciente/ficha-paciente.component';
import { HistoriaClinicaComponent } from '../historia-clinica/historia-clinica.component';

export const PACIENTES_ROUTES: Routes = [
    {
        path: '',
        component: PacientesComponent
    },
    {
        path: 'ficha/:id', // Ruta: /pacientes/ficha/:id
        component: FichaPacienteComponent
    },
    {
        path: 'historia/:id', // Ruta: /pacientes/historia/:id
        component: HistoriaClinicaComponent
    }
];
