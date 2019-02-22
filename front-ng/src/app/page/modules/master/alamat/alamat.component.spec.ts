import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AlamatComponent } from './alamat.component';

describe('AlamatComponent', () => {
  let component: AlamatComponent;
  let fixture: ComponentFixture<AlamatComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AlamatComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AlamatComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
