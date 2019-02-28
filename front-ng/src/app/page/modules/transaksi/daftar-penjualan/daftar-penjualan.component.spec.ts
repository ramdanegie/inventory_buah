import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { DaftarPenjualanComponent } from './daftar-penjualan.component';

describe('DaftarPenjualanComponent', () => {
  let component: DaftarPenjualanComponent;
  let fixture: ComponentFixture<DaftarPenjualanComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ DaftarPenjualanComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(DaftarPenjualanComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
