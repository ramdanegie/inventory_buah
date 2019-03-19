import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { DaftarDetailPenerimaanBarangComponent } from './daftar-detail-penerimaan-barang.component';

describe('DaftarDetailPenerimaanBarangComponent', () => {
  let component: DaftarDetailPenerimaanBarangComponent;
  let fixture: ComponentFixture<DaftarDetailPenerimaanBarangComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ DaftarDetailPenerimaanBarangComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(DaftarDetailPenerimaanBarangComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
