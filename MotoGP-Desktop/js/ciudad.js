class Ciudad {
  constructor({ nombre, pais, gentilicio}) {
    this.nombre = nombre;
    this.pais = pais;
    this.gentilicio = gentilicio;
  }
  rellenaDatos(poblacion,coordenadas){
    this.poblacion = poblacion;
    this.coordenadas = coordenadas;
  }
  getNombre() {
    return `${this.nombre}`;
  }

  getPais() {
    return `${this.pais}`;
  }

  getLocalidad() {
    return `<ul>
      <li>Poblacion: ${this.poblacion}</li>
      <li>Gentilicio: ${this.gentilicio}</li>
    </ul>`;
  }

  
  drawPoint() {
    document.write(`<p>${this.coordenadas.lat}, ${this.coordenadas.lng}</p>`);
  }

}
