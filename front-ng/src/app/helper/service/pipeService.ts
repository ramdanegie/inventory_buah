import { Pipe } from '@angular/core';
import { DomSanitizer } from '@angular/platform-browser';

@Pipe({ name: "thousandSeparator" })
export class ThousandSeparator {
  transform(value) {
    return 'Rp. ' + value.toString().replace(/\d{1,3}(?=(\d{3})+(?!\d))/g, "$&.");
  }
}

@Pipe({ name: "timeFormatter" })
export class TimeFormatter {
  transform(value) {
    return value.toString() + ' WIB';
  }
}

@Pipe({ name: 'capitalize' })
export class CapitalizePipe {
  transform(value: any) {
    if (value) {
      return value.toString().toUpperCase();
    }
    return value;
  }
}

@Pipe({ name: 'safeHtml' })
export class SafeHtmlPipe {
  constructor(private sanitized: DomSanitizer) { }
  transform(value) {
    return this.sanitized.bypassSecurityTrustHtml(value);
  }
}