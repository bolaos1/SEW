class Ciudad {
  #dataHourly;
  #dataDaily;
  constructor({ nombre, pais, gentilicio }) {
    this.nombre = nombre;
    this.pais = pais;
    this.gentilicio = gentilicio;
  }
  rellenaDatos(poblacion, coordenadas) {
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



  getMeteorlogiaCarrera() {
    const baseUrl = "https://archive-api.open-meteo.com/v1/archive";

    $.ajax({
      dataType: "json",
      url: baseUrl,
      method: "GET",
      data: {
        latitude: 47.0083,
        longitude: 18.2017,
        start_date: "2025-08-24",
        end_date: "2025-08-24",
        hourly: [
          "temperature_2m",
          "apparent_temperature",
          "rain",
          "relative_humidity_2m",
          "windspeed_10m",
          "wind_direction_10m"
        ].join(","),
        daily: "sunrise,sunset",
        timezone: "Europe/Budapest"
      },
      success: function (data) {
        this.#dataHourly = data.hourly;
        this.#dataDaily = data.daily;
 
      },
      error: function (error) {
        console.error("Error cargando los datos:", error);
      }
    });
  }

  procesarJSONCarrera(){
    
  }

}
