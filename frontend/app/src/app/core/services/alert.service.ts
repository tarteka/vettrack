// Servicio profesional para centralizar todas las alertas SweetAlert2
import { Injectable } from '@angular/core';
import Swal, { SweetAlertResult } from 'sweetalert2';

@Injectable({
  providedIn: 'root'
})
export class AlertService {

  // Alerta de éxito para actualizaciones (tu caso principal)
  success(title: string = '¡Éxito!', text: string = 'Operación completada'): Promise<SweetAlertResult> {
    return Swal.fire({
      title,
      text,
      icon: 'success',
      timer: 3000,  // Se cierra automáticamente
      showConfirmButton: false,
      toast: true,  // Estilo toast en esquina
      position: 'top-end'
    });
  }

  // Confirmación para acciones críticas
  async confirm(title: string, text: string): Promise<boolean> {
    const result = await Swal.fire({
      title,
      text,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Confirmar',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#28a745',
      cancelButtonColor: '#6c757d'
    });
    return result.isConfirmed;
  }

  //Confirmación para suspender tratamiento con input de motivo
  async confirmSuspension(treatment: any): Promise<string | null> {
    const { value: motivo } = await Swal.fire({
      title: 'Confirmación',
      text: '¿Seguro que quieres suspender este tratamiento? NO se podrá reactivar posteriormente.',
      icon: 'warning',
      input: 'textarea',
      inputLabel: 'Motivo de cancelación',
      inputPlaceholder: 'Escribe aquí el motivo...',
      showCancelButton: true,
      confirmButtonText: 'Confirmar',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#28a745',
      cancelButtonColor: '#6c757d',
      inputValidator: (value) => {
        if (!value || value.trim() === '') {
          return 'Debes escribir un motivo de cancelación';
        }
        return null;
      }
    });
      return motivo ?? null; // null si canceló
  }


  // Error con opción de retry
  error(title: string, text: string): void {
    void Swal.fire({
      title,
      text,
      icon: 'error',
      confirmButtonText: 'Entendido'
    });
  }

  loading(text = 'Cargando...') {
    Swal.fire({
      text,
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading()
    });
  }

  close() {
    Swal.close();
  }

}
