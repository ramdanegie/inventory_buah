import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { TransaksiPenjualanComponent } from './transaksi-penjualan.component';

describe('TransaksiPenjualanComponent', () => {
  let component: TransaksiPenjualanComponent;
  let fixture: ComponentFixture<TransaksiPenjualanComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ TransaksiPenjualanComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(TransaksiPenjualanComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
