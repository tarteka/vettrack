export interface ServiceCategory {
  id: number;
  name: string;
  description: string;
}

export interface Servicio {
  id: number;
  name: string;
  description: string;
  unitPrice: number;
  price?: number;
  taxRate: number;
  isActive?: boolean;
  category: ServiceCategory // Nombre para mostrar en la tabla
}
