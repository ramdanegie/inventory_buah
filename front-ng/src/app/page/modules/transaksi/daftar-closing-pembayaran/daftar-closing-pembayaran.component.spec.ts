import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { DaftarClosingPembayaranComponent } from './daftar-closing-pembayaran.component';

describe('DaftarClosingPembayaranComponent', () => {
  let component: DaftarClosingPembayaranComponent;
  let fixture: ComponentFixture<DaftarClosingPembayaranComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ DaftarClosingPembayaranComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(DaftarClosingPembayaranComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
