export interface Mascota {
  id: number;
  name: string;
  age: number;
  gender: string;
  microchip: string;
  breed: string;
  weight: string;
  lastAppointment?: string;
  petType: {
    id: number;
    name: string;
    description: string;
  };
  client: {
    id: number;
    fullName: string;
    phone: string;
    email: string;
    dni: string;
  };
}
