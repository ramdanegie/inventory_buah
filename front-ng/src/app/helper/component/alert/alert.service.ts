import { Injectable } from '@angular/core';
import { Router, NavigationStart } from '@angular/router';
import { Observable } from 'rxjs';
import { Subject } from 'rxjs/Subject';
import { AlertMsg } from '../../';

@Injectable()
export class AlertService {
    private subject = new Subject<AlertMsg>();
    private keepAfterNavigationChange = false;

    private show(info: string, summary: string, detail: string, keepAfterNavigationChange = false) {
        this.keepAfterNavigationChange = keepAfterNavigationChange;
        this.subject.next({ info: info, summary: summary, detail: detail });
    }

    info(title: string, message: string, keepAfterNavigationChange = false) {
        this.show('info', title, message, keepAfterNavigationChange);
    }

    success(title: string, message: string, keepAfterNavigationChange = false) {
        this.show('success', title, message, keepAfterNavigationChange);
    }

    warn(title: string, message: string, keepAfterNavigationChange = false) {
        this.show('warn', title, message, keepAfterNavigationChange);
    }

    error(title: string, message: string, keepAfterNavigationChange = false) {
        this.show('error', title, message, keepAfterNavigationChange);
    }

    getMessage(): Observable<AlertMsg> {
        return this.subject.asObservable();
    }
}