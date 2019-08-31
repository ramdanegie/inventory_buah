export class Configuration {
  static get() {
    return {
      apiBackend: 'http://192.168.1.5:8000/service/',
      headerToken: 'token',
      authLogin: ':8000',
    }
  }
}
