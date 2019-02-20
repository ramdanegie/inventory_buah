import { Component, OnInit, OnDestroy } from '@angular/core';
import { MatProgressBarModule } from '@angular/material';
import { Subscription } from 'rxjs/Subscription';

import { LoaderService, LoaderState } from '../../';

// @Component({
//     selector: 'angular-loader',
//     templateUrl: 'loader.component.html',
//     styleUrls: ['loader.component.css']
// })

@Component({
    selector: 'app-loader',
    template: '<p-progressBar mode="indeterminate" *ngIf="show" ></p-progressBar>'  
})

export class LoaderComp implements OnInit, OnDestroy {

    show = false;

    private subscription: Subscription;

    constructor(private loaderService: LoaderService) { }

    ngOnInit() { 
        this.subscription = this.loaderService.getState().subscribe(state => {
            this.show = state.show;
        });
    }

    ngOnDestroy() {
        this.subscription.unsubscribe();
    }
}