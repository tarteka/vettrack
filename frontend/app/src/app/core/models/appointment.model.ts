export interface Appointment {
  id: number;
  appointmentType: string;
  appointmentStatus: 'confirmada' | 'pendiente' | 'completada' | 'cancelada';
  petName: string;
  date: string; // "2026-01-31"
  startTime: string; // "09:30"
  veterinarian: string;
}