import { Component, Inject, forwardRef } from '@angular/core';
import { AppComponent } from './app.component';
import { Authentication, AuthGuard, InfoService } from './helper';
import { Router } from '@angular/router';

@Component({
    selector: 'app-topbar',
    templateUrl: './app.topbar.component.html'
})
export class AppTopBarComponent {

    constructor(@Inject(forwardRef(() => AppComponent)) public app: AppComponent,
        private auth: Authentication,
        private authGuard: AuthGuard,
        private info: InfoService,
        private router: Router) { }

    logout(event: Event) {
        this.auth.logout(this.authGuard, this.info, this.router);
        event.preventDefault();
    }
}
