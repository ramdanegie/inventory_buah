import {Component,OnInit} from '@angular/core';
import {SelectItem, ScheduleModule, TabViewModule, RatingModule} from 'primeng/primeng';
import { Router } from '@angular/router';
// import { GMapModule} from 'primeng/components/gmap/gmap';
import * as $ from 'jquery';

@Component({
    templateUrl: './not-found.component.html'
})
export class NotFoundComponent implements OnInit {

    constructor(private router: Router) { 
    }
    
    ngOnInit() {

    }
}