import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { KelompokUserComponent } from './kelompok-user.component';

describe('KelompokUserComponent', () => {
  let component: KelompokUserComponent;
  let fixture: ComponentFixture<KelompokUserComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ KelompokUserComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(KelompokUserComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
