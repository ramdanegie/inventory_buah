import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ReturPenjualanComponent } from './retur-penjualan.component';

describe('ReturPenjualanComponent', () => {
  let component: ReturPenjualanComponent;
  let fixture: ComponentFixture<ReturPenjualanComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ReturPenjualanComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ReturPenjualanComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
