import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { PenerimaanBarangFixComponent } from './penerimaan-barang-fix.component';

describe('PenerimaanBarangFixComponent', () => {
  let component: PenerimaanBarangFixComponent;
  let fixture: ComponentFixture<PenerimaanBarangFixComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ PenerimaanBarangFixComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(PenerimaanBarangFixComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
