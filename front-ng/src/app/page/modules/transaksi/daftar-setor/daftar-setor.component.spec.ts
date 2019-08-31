import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { DaftarSetorComponent } from './daftar-setor.component';

describe('DaftarSetorComponent', () => {
  let component: DaftarSetorComponent;
  let fixture: ComponentFixture<DaftarSetorComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ DaftarSetorComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(DaftarSetorComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
