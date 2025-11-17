"use strict";

class Ciudad {
  #datosCarrera;
  #datosCarreraDiarios;
  #datosEntrenos;

  constructor({ nombre, pais, gentilicio }) {
    this.nombre = nombre;
    this.pais = pais;
    this.gentilicio = gentilicio;
    this.poblacion = 0;
    this.coordenadas = { lat: 0, lng: 0 };
    this.#datosCarrera = null;
    this.#datosCarreraDiarios = null;
    this.#datosEntrenos = null;
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
    return [
      {
        etiqueta: "Población",
        valor: this.poblacion.toLocaleString("es-ES"),
      },
      {
        etiqueta: "Gentilicio",
        valor: this.gentilicio,
      },
    ];
  }

  getCoordenadas() {
    return { ...this.coordenadas };
  }

  drawPoint(contenedor) {
    const destino = contenedor || document.body;
    const parrafo = document.createElement("p");
    parrafo.textContent = `Coordenadas: ${this.coordenadas.lat}, ${this.coordenadas.lng}`;
    destino.appendChild(parrafo);
  }

  getMeteorologiaCarrera() {
    const baseUrl = "https://archive-api.open-meteo.com/v1/archive";
    const parametros = {
      latitude: this.coordenadas.lat,
      longitude: this.coordenadas.lng,
      start_date: "2025-08-24",
      end_date: "2025-08-24",
      hourly: [
        "temperature_2m",
        "apparent_temperature",
        "rain",
        "relative_humidity_2m",
        "windspeed_10m",
        "wind_direction_10m",
      ].join(","),
      daily: "sunrise,sunset",
      timezone: "Europe/Budapest",
    };

    return $.ajax({
      dataType: "json",
      url: baseUrl,
      method: "GET",
      data: parametros,
    }).done((data) => {
      this.#datosCarrera = data?.hourly ?? null;
      this.#datosCarreraDiarios = data?.daily ?? null;
    });
  }

  procesarJSONCarrera() {
    if (!this.#datosCarrera || !Array.isArray(this.#datosCarrera.time)) {
      return null;
    }

    const horas = this.#datosCarrera.time.map((instante, indice) => ({
      instante,
      temperatura: this.#datosCarrera.temperature_2m?.[indice] ?? 0,
      sensacion: this.#datosCarrera.apparent_temperature?.[indice] ?? 0,
      lluvia: this.#datosCarrera.rain?.[indice] ?? 0,
      humedad: this.#datosCarrera.relative_humidity_2m?.[indice] ?? 0,
      viento: this.#datosCarrera.windspeed_10m?.[indice] ?? 0,
      direccion: this.#datosCarrera.wind_direction_10m?.[indice] ?? 0,
    }));

    return {
      fecha: horas.length ? horas[0].instante.split("T")[0] : "",
      salidaSol: this.#datosCarreraDiarios?.sunrise?.[0] ?? "",
      puestaSol: this.#datosCarreraDiarios?.sunset?.[0] ?? "",
      horas,
    };
  }

  getMeteorologiaEntrenos() {
    const baseUrl = "https://archive-api.open-meteo.com/v1/archive";
    const parametros = {
      latitude: this.coordenadas.lat,
      longitude: this.coordenadas.lng,
      start_date: "2025-08-21",
      end_date: "2025-08-23",
      hourly: [
        "temperature_2m",
        "rain",
        "windspeed_10m",
        "relative_humidity_2m",
      ].join(","),
      timezone: "Europe/Budapest",
    };

    return $.ajax({
      dataType: "json",
      url: baseUrl,
      method: "GET",
      data: parametros,
    }).done((data) => {
      this.#datosEntrenos = data?.hourly ?? null;
    });
  }

  procesarJSONEntrenos() {
    if (!this.#datosEntrenos || !Array.isArray(this.#datosEntrenos.time)) {
      return [];
    }

    const acumulados = {};
    this.#datosEntrenos.time.forEach((instante, indice) => {
      const fecha = instante.split("T")[0];
      if (!acumulados[fecha]) {
        acumulados[fecha] = {
          total: 0,
          temperatura: 0,
          lluvia: 0,
          viento: 0,
          humedad: 0,
        };
      }
      const dia = acumulados[fecha];
      dia.total += 1;
      dia.temperatura += this.#datosEntrenos.temperature_2m?.[indice] ?? 0;
      dia.lluvia += this.#datosEntrenos.rain?.[indice] ?? 0;
      dia.viento += this.#datosEntrenos.windspeed_10m?.[indice] ?? 0;
      dia.humedad += this.#datosEntrenos.relative_humidity_2m?.[indice] ?? 0;
    });

    return Object.entries(acumulados).map(([fecha, valores]) => ({
      fecha,
      temperatura: valores.temperatura / valores.total,
      lluvia: valores.lluvia / valores.total,
      viento: valores.viento / valores.total,
      humedad: valores.humedad / valores.total,
    }));
  }
}