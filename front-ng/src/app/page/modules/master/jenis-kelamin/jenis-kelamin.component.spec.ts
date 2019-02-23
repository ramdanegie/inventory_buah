import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { JenisKelaminComponent } from './jenis-kelamin.component';

describe('JenisKelaminComponent', () => {
  let component: JenisKelaminComponent;
  let fixture: ComponentFixture<JenisKelaminComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ JenisKelaminComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(JenisKelaminComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
