import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { DaftarPembayaranComponent } from './daftar-pembayaran.component';

describe('DaftarPembayaranComponent', () => {
  let component: DaftarPembayaranComponent;
  let fixture: ComponentFixture<DaftarPembayaranComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ DaftarPembayaranComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(DaftarPembayaranComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
