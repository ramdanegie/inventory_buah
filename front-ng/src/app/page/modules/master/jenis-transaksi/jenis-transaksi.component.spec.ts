import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { JenisTransaksiComponent } from './jenis-transaksi.component';

describe('JenisTransaksiComponent', () => {
  let component: JenisTransaksiComponent;
  let fixture: ComponentFixture<JenisTransaksiComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ JenisTransaksiComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(JenisTransaksiComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
