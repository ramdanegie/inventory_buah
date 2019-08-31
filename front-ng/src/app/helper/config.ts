export class Configuration {
  static get() {
    if (window.location.hostname.indexOf('110.137') > -1) {
      return {
        apiBackend: 'http://110.137.212.85:8000/service/',
        headerToken: 'token',
        authLogin: ':8000',
      }
    } if (window.location.hostname.indexOf('localhost') > -1) {
      return {
        apiBackend: 'http://localhost:8000/service/',
        headerToken: 'token',
        authLogin: ':8000',
      }
    }else{
      return {
        apiBackend: 'http://192.168.1.5:8000/service/',
        headerToken: 'token',
        authLogin: ':8000',
      }
    }
  }
}
