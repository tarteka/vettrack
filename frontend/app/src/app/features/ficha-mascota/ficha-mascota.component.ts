import { CommonModule } from '@angular/common';
import { Component, EventEmitter, Input, OnChanges, Output, SimpleChanges, OnInit, inject, signal, ChangeDetectionStrategy } from '@angular/core';
import {
  LucideDynamicIcon,
  LucideArrowLeft as ArrowLeft,
  LucideCalendar as Calendar,
  LucideFileText as FileText,
  LucidePill as Pill,
  LucideUser as User,
  LucidePhone as Phone,
  LucideMail as Mail,
  LucideDog as Dog,
  LucideCat as Cat
} from '@lucide/angular';
import { PetUserService } from '../../core/services/pet-user.service';
import { Mascota } from '../../core/models/pet-user.model';
import { MedicalRecordService, MedicalRecord } from '../../core/services/medical-record.service';
import { Router, ActivatedRoute } from '@angular/router';

type TabKey = 'historial' | 'tratamientos' | 'citas';

interface HistoriaClinicaItem {
  id: number;
  type: string;
  description: string;
  diagnosis?: string | null;
  notes?: string | null;
  procedures?: string | null;
  date: string;
  veterinarian: string;
}

interface TratamientoItem {
  id: number;
  nombre: string;
  medicamento: string;
  dosis: string;
  frecuencia: string;
  fechaInicio: string;
  fechaFin: string;
  indicaciones: string;
}


@Component({
  selector: 'app-ficha-mascota',
  standalone: true,
  imports: [
    CommonModule,
    LucideDynamicIcon
  ],
  changeDetection: ChangeDetectionStrategy.Eager,
  templateUrl: './ficha-mascota.component.html'
})
export class FichaMascotaComponent implements OnInit {
  @Input() data?: any;
  @Output() navigate = new EventEmitter<string>();
  mascotaId: string | null = null;
  private petService = inject(PetUserService);
  private recordService = inject(MedicalRecordService);
  private route = inject(ActivatedRoute);
  private router = inject(Router);

  mascota = signal<Mascota | null>(null);
  historialClinico = signal<HistoriaClinicaItem[]>([]);
  activeTab = signal<TabKey>('historial');

  // Icons for template
  readonly ArrowLeft = ArrowLeft;
  readonly Calendar = Calendar;
  readonly FileText = FileText;
  readonly Pill = Pill;
  readonly User = User;
  readonly Phone = Phone;
  readonly Mail = Mail;
  readonly Dog = Dog;
  readonly Cat = Cat;

  tratamientosActivos = signal<TratamientoItem[]>([]);

  proximasCitas = [
    { id: 1, fecha: '28 Oct 2025', hora: '10:00', tipo: 'Revisión anual' },
    { id: 2, fecha: '15 Nov 2025', hora: '11:30', tipo: 'Vacunación' }
  ];

  ngOnInit() {
    const id = this.route.snapshot.paramMap.get('id');
    if (id) {
      this.petService.getPetsById(id).subscribe({
        next: (data) => this.mascota.set(data),
        error: (err) => {
          console.error('Error al cargar la mascota', err);
          void this.router.navigate(['/mascotas']);
        }
      });
      this.loadMedicalRecords(Number(id));
    }
  }

  loadMedicalRecords(petId: number) {
    this.recordService.getMedicalRecords(petId).subscribe({
      next: (records) => {
        this.historialClinico.set(records.map((record) => this.mapRecordToHistoria(record)));
        this.tratamientosActivos.set(this.mapRecordsToTratamientos(records));
      },
      error: (err) => console.error('Error cargando historial:', err)
    });
  }

  onNavigateBack() {
    void this.router.navigate(['/mascotas']);
  }

  setTab(tab: TabKey) {
    this.activeTab.set(tab);
  }

  isDog(): boolean {
    const m = this.mascota();
    return (m?.petType.name || '').toLowerCase() === 'perro';
  }

  private mapRecordToHistoria(record: MedicalRecord): HistoriaClinicaItem {
    const { observaciones } = this.parseRecordNotes(record.notes);
    return {
      id: record.id,
      type: record.type,
      description: record.description,
      diagnosis: record.diagnosis || null,
      notes: observaciones || null,
      procedures: record.procedures || null,
      date: record.date,
      veterinarian: record.veterinarian
    };
  }

  private mapRecordsToTratamientos(records: MedicalRecord[]): TratamientoItem[] {
    const tratamientos: TratamientoItem[] = [];
    records.forEach((record) => {
      const { tratamiento } = this.parseRecordNotes(record.notes);
      const source = (tratamiento || '').trim();
      if (!source) return;
      const blocks = source.split(/\n\s*\n/).map((block) => block.trim()).filter(Boolean);
      blocks.forEach((block, index) => {
        const parsed = this.parseTratamientoBlock(block);
        tratamientos.push({
          id: record.id * 100 + index,
          nombre: parsed.nombre || 'Tratamiento',
          medicamento: parsed.medicamento || parsed.nombre || 'No especificado',
          dosis: parsed.dosis || 'Pendiente',
          frecuencia: parsed.frecuencia || 'Pendiente',
          fechaInicio: parsed.fechaInicio || record.date,
          fechaFin: parsed.fechaFin || '',
          indicaciones: parsed.indicaciones || ''
        });
      });
    });
    return tratamientos;
  }

  private parseTratamientoBlock(block: string): {
    nombre: string;
    medicamento: string;
    dosis: string;
    frecuencia: string;
    fechaInicio: string;
    fechaFin: string;
    indicaciones: string;
  } {
    const lines = block.split('\n').map((line) => line.trim()).filter(Boolean);
    const getValue = (label: string): string => {
      const lower = label.toLowerCase();
      const line = lines.find((l) => l.toLowerCase().startsWith(`${lower}:`));
      if (!line) return '';
      return line.slice(line.indexOf(':') + 1).trim();
    };
    const hasLabels = lines.some((line) => line.includes(':'));
    const indicaciones = getValue('indicaciones') || (hasLabels ? '' : block);
    return {
      nombre: getValue('nombre') || lines[0] || '',
      medicamento: getValue('medicamento'),
      dosis: getValue('dosis'),
      frecuencia: getValue('frecuencia'),
      fechaInicio: getValue('inicio'),
      fechaFin: getValue('fin'),
      indicaciones
    };
  }

  private parseRecordNotes(notes?: string | null): { tratamiento: string; observaciones: string } {
    if (!notes) return { tratamiento: '', observaciones: '' };
    const lines = notes.split('\n').map((line) => line.trim());
    const findIndex = (label: string) =>
      lines.findIndex((l) => l.toLowerCase().startsWith(`${label.toLowerCase()}:`));

    const tratamientoIndex = findIndex('tratamiento');
    const observacionesIndex = findIndex('observaciones');

    let tratamiento = '';
    if (tratamientoIndex !== -1) {
      const firstLine = lines[tratamientoIndex];
      const firstValue = firstLine.slice(firstLine.indexOf(':') + 1).trim();
      const endIndex = observacionesIndex !== -1 ? observacionesIndex : lines.length;
      const extraLines = lines.slice(tratamientoIndex + 1, endIndex).filter((l) => l.length > 0);
      tratamiento = [firstValue, ...extraLines].filter((l) => l.length > 0).join('\n').trim();
    }

    let observaciones = '';
    if (observacionesIndex !== -1) {
      const line = lines[observacionesIndex];
      observaciones = line.slice(line.indexOf(':') + 1).trim();
    }

    return { tratamiento, observaciones };
  }
}


