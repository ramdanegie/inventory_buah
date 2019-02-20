import { Message } from 'primeng/primeng';

export class AlertHandler {

    public msgs: Message[] = [];

    show(message: any) {
        this.msgs = [];
        this.msgs.push({ severity: message.severity, summary: message.summary, detail: message.detail });
        return this.msgs;
    }

    hide() {
        this.msgs = [];
    }

}