import { Injectable } from '@angular/core';

@Injectable()
export class CacheService {
  maps = [];
  constructor() { }
  get(name) {
    var temp = window.localStorage.getItem('cacheHelper');
    if (temp !== undefined && temp !== null && this.maps.length === 0)
      this.maps = JSON.parse(temp);

    for (var key in this.maps) {
      if (this.maps.hasOwnProperty(key)) {
        var element = this.maps[key];
        if (element.name === name)
          return element.value;
      }
    }
    return undefined;
  }
  
  set (name, value) {
    var item = undefined;
    for (var key in this.maps) {
      if (this.maps.hasOwnProperty(key)) {
        var element = this.maps[key];
        if (element.name === name) {
          item = element;
          this.maps[key] = {
            name: name,
            value: value
          };
          break;
        }
      }
    }
    if (item === undefined)
    this.maps.push({
        name: name,
        value: value
      });
    else item = {
      name: name,
      value: value
    };
    window.localStorage.setItem('cacheHelper', JSON.stringify(this.maps));
  }
}
