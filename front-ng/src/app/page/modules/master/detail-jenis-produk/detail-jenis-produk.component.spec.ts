import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { DetailJenisProdukComponent } from './detail-jenis-produk.component';

describe('DetailJenisProdukComponent', () => {
  let component: DetailJenisProdukComponent;
  let fixture: ComponentFixture<DetailJenisProdukComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ DetailJenisProdukComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(DetailJenisProdukComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
