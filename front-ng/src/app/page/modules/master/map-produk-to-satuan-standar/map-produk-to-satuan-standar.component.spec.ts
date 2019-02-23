import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { MapProdukToSatuanStandarComponent } from './map-produk-to-satuan-standar.component';

describe('MapProdukToSatuanStandarComponent', () => {
  let component: MapProdukToSatuanStandarComponent;
  let fixture: ComponentFixture<MapProdukToSatuanStandarComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ MapProdukToSatuanStandarComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(MapProdukToSatuanStandarComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
