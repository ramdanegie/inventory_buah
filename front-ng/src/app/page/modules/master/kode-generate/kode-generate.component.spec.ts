import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { KodeGenerateComponent } from './kode-generate.component';

describe('KodeGenerateComponent', () => {
  let component: KodeGenerateComponent;
  let fixture: ComponentFixture<KodeGenerateComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ KodeGenerateComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(KodeGenerateComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
