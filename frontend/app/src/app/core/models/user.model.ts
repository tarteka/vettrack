export interface UserResponse {
  success: string;
  code: number;
  data: User[];
}

export interface User {
  id: number;
  email: string;
  firstName: string;
  lastName: string;
  dni: string;
  phone: string;
  city: string;
  zipCode: string;
  role: 'admin' | 'vet' | 'client';
  isActive: boolean;
  createdAt: string;
}
