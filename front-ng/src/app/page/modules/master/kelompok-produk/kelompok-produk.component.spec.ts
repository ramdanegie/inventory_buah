import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { KelompokProdukComponent } from './kelompok-produk.component';

describe('KelompokProdukComponent', () => {
  let component: KelompokProdukComponent;
  let fixture: ComponentFixture<KelompokProdukComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ KelompokProdukComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(KelompokProdukComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
