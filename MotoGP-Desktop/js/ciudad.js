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

  
  drawPoint(contenedor) {
    const parrafo = document.createElement("p");
    parrafo.textContent = `Coordenadas: ${this.coordenadas.lat}, ${this.coordenadas.lng}`;
    (contenedor || document.body).appendChild(parrafo);
  }

}
