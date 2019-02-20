import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { PenerimaanBarangSupplierComponent } from './penerimaan-barang-supplier.component';

describe('PenerimaanBarangSupplierComponent', () => {
  let component: PenerimaanBarangSupplierComponent;
  let fixture: ComponentFixture<PenerimaanBarangSupplierComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ PenerimaanBarangSupplierComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(PenerimaanBarangSupplierComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
