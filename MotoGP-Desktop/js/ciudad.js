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
    const parrafo = document.createElement("p")
  parrafo.textContent = `${this.coordenadas.lat}, ${this.coordenadas.lng}`;
    document.body.insertBefore(parrafo,document.body.children[-1]);
  }

}
