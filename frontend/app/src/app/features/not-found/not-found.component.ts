import { Component } from '@angular/core';
import { Location } from '@angular/common';
import { RouterModule } from '@angular/router';
import { LucideAngularModule } from 'lucide-angular';

@Component({
  selector: 'app-not-found',
  templateUrl: './not-found.component.html',
  standalone: true,
  imports: [RouterModule, LucideAngularModule],
})
export class NotFoundComponent {
  constructor(private location: Location) {}

  goBack() {
    this.location.back();
  }
}
