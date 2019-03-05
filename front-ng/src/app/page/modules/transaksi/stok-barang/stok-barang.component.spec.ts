import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { StokBarangComponent } from './stok-barang.component';

describe('StokBarangComponent', () => {
  let component: StokBarangComponent;
  let fixture: ComponentFixture<StokBarangComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ StokBarangComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(StokBarangComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
