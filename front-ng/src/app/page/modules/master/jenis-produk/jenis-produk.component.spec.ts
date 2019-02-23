import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { JenisProdukComponent } from './jenis-produk.component';

describe('JenisProdukComponent', () => {
  let component: JenisProdukComponent;
  let fixture: ComponentFixture<JenisProdukComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ JenisProdukComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(JenisProdukComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
