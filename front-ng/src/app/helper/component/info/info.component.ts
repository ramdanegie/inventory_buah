import { Component, OnInit, OnDestroy } from '@angular/core';
import { InfoService, InfoMsg } from '../../';
import { Message } from 'primeng/primeng';

import { Subscription } from 'rxjs/Subscription';


@Component({
    selector: 'app-info',
    template: ' <p-messages [value]="msgs"></p-messages>'
})

export class InfoComp implements OnInit, OnDestroy {

    message: InfoMsg;
    msgs: Message[] = [];

    private showS: Subscription;
    private hideS: Subscription;


    constructor(private infoService: InfoService) { }

    ngOnInit() {
        this.showS = this.infoService.getMessage().subscribe(message => { this.showInfo(message); });
        this.hideS = this.infoService.hidenMessage().subscribe(() => this.hide());
        // console.log('ngOnInit info');
    }

    ngOnDestroy() {
        this.showS.unsubscribe();
        this.hideS.unsubscribe();
    }

    showInfo(message) {
        this.msgs = [];
        this.msgs.push({ severity: message.info, summary: message.summary, detail: message.detail });
    }

    hide() {
        this.msgs = [];
    }
}