import { Injectable } from '@angular/core';
import { Router, NavigationStart } from '@angular/router';
import { Observable } from 'rxjs';
import { Subject } from 'rxjs/Subject';
import { InfoMsg } from '../../';

@Injectable()
export class InfoService {
    private subject = new Subject<InfoMsg>();
    private hidden = new Subject<any>();

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

    hide() {
        this.hidden.next();
    }

    hidenMessage(): Observable<InfoMsg> {
        return this.hidden.asObservable();
    }

    getMessage(): Observable<InfoMsg> {
        return this.subject.asObservable();
    }
}