import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { SatuanStandarComponent } from './satuan-standar.component';

describe('SatuanStandarComponent', () => {
  let component: SatuanStandarComponent;
  let fixture: ComponentFixture<SatuanStandarComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ SatuanStandarComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(SatuanStandarComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
