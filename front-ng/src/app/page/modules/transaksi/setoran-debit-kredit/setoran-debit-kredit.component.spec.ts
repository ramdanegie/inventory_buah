import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { SetoranDebitKreditComponent } from './setoran-debit-kredit.component';

describe('SetoranDebitKreditComponent', () => {
  let component: SetoranDebitKreditComponent;
  let fixture: ComponentFixture<SetoranDebitKreditComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ SetoranDebitKreditComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(SetoranDebitKreditComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
