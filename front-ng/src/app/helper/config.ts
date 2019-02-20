export class Configuration {
  static get() {
    return {
      apiBackend: 'http://localhost:8000/service/',
      headerToken: 'token',
      authLogin: ':8000',
    }
  }
}
