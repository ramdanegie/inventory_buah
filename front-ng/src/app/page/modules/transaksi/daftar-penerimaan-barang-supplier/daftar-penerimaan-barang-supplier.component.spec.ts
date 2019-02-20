import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { DaftarPenerimaanBarangSupplierComponent } from './daftar-penerimaan-barang-supplier.component';

describe('DaftarPenerimaanBarangSupplierComponent', () => {
  let component: DaftarPenerimaanBarangSupplierComponent;
  let fixture: ComponentFixture<DaftarPenerimaanBarangSupplierComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ DaftarPenerimaanBarangSupplierComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(DaftarPenerimaanBarangSupplierComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
