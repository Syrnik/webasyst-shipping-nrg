export default class WebasystErrorParser {
  static parse(data) {
    if (typeof data === 'string') return [data];

    if (Array.isArray(data)) {
      if (!data.length) return this.parse("Пустой массив ошибок");
      if (data.length === 1) return this.parse(data[0]);
      if (data.length === 2 && (typeof data[0] === 'string')) {
        if (Array.isArray(data[1])) {
          if (!data[1].length) return this.parse([data[0]])
          if (typeof data[1][0] === 'string') return this.parse(`${data[0]} (${data[1][0]})`);
        }
        if (!data[1]) return this.parse([data[0]]);
        if ((typeof data[1] === "string") || (typeof data[1] === 'number')) return this.parse(`${data[0]} (${data[1]})`);
        return this.parse(data[0]);
      }

      return data.map(err => this.parse(err)[0]);
    }

    // jQuery/XHR HTTP error?
    if (typeof data === 'object') {
      if (data.status && data.status === 'fail') {
        if (data.error) return this.parse(data.error);
        if (data.errors) return this.parse(data.errors);
        return this.parse("Ошибка исполнения контроллера или экшена");
      }
      if (data.status && data.statusText) return this.parse([data.statusText, data.status]);
      console.error(data);
      return ["Объект"];
    }

    console.error(data);
    return this.parse("Неизвестная ошибка")
  }
}
