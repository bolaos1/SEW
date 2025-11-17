"use strict";

$(function () {
  const ciudad = new Ciudad({
    nombre: "Balaton",
    pais: "Hungría",
    gentilicio: "balatoni",
  });

  ciudad.rellenaDatos(20000, { lat: 46.959722, lng: 17.885 });

  const $main = $("main");
  const $section = $main.find("section").first();
  const $infoCiudad = $("<article></article>");
  $infoCiudad.append($("<h3></h3>").text("Información de la ciudad"));
  $infoCiudad.append($("<p></p>").text(`Ciudad: ${ciudad.getNombre()}`));
  $infoCiudad.append($("<p></p>").text(`País: ${ciudad.getPais()}`));

  const $listaLocalidad = $("<ul></ul>");
  ciudad.getLocalidad().forEach((dato) => {
    $listaLocalidad.append(
      $("<li></li>").text(`${dato.etiqueta}: ${dato.valor}`)
    );
  });
  $infoCiudad.append($listaLocalidad);

  const coords = ciudad.getCoordenadas();
  $infoCiudad.append(
    $("<p></p>").text(`Coordenadas: ${coords.lat}, ${coords.lng}`)
  );

  $section.append($infoCiudad);

  const $carreraArticle = $("<article></article>");
  $section.append($carreraArticle);

  const $entrenosArticle = $("<article></article>");
  $section.append($entrenosArticle);

  const formatearFecha = (iso) => {
    if (!iso) {
      return "";
    }
    const fecha = new Date(iso);
    return fecha.toLocaleString("es-ES", {
      dateStyle: "short",
      timeStyle: "short",
    });
  };

  const formatearNumero = (valor, sufijo = "") => {
    const numero = Number(valor ?? 0);
    return `${numero.toFixed(2)}${sufijo}`;
  };

  const mostrarError = ($destino, mensaje, titulo) => {
    $destino.empty();
    $destino.append($("<h3></h3>").text(titulo));
    $destino.append($("<p></p>").text(mensaje));
  };

  const renderCarrera = (datos) => {
    if (!datos) {
      mostrarError(
        $carreraArticle,
        "No hay datos meteorológicos disponibles para la carrera.",
        "Tiempo durante la carrera"
      );
      return;
    }

    $carreraArticle.empty();
    $carreraArticle.append(
      $("<h3></h3>").text("Tiempo durante la carrera")
    );

    if (datos.salidaSol || datos.puestaSol) {
      const $infoSolar = $("<p></p>").text(
        `Salida del sol: ${formatearFecha(
          datos.salidaSol
        )} · Puesta del sol: ${formatearFecha(datos.puestaSol)}`
      );
      $carreraArticle.append($infoSolar);
    }

  
    const $bloqueHoras = $("<section></section>");
    $bloqueHoras.append(
      $("<h4></h4>").text("Datos horarios durante la carrera")
    ); 
    datos.horas.forEach((hora) => {
      const descripcion = [
        `Hora: ${formatearFecha(hora.instante)}`,
        `Temperatura: ${formatearNumero(hora.temperatura, " °C")}`,
        `Sensación térmica: ${formatearNumero(hora.sensacion, " °C")}`,
        `Lluvia: ${formatearNumero(hora.lluvia, " mm")}`,
        `Humedad: ${formatearNumero(hora.humedad, " %")}`,
        `Viento: ${formatearNumero(hora.viento, " km/h")}`,
        `Dirección del viento: ${formatearNumero(hora.direccion, "°")}`,
      ].join(" · ");

      $bloqueHoras.append(
        $("<p></p>").text(descripcion)
      );
    });

    $carreraArticle.append($bloqueHoras);
  };

  const renderEntrenos = (entrenos) => {
  if (!entrenos.length) {
    mostrarError(
      $entrenosArticle,
      "No se han podido calcular las medias de los entrenamientos.",
      "Resumen de los entrenamientos"
    );
    return;
  }

  $entrenosArticle.empty();

  const $bloqueEntrenos = $("<section></section>");
  $bloqueEntrenos.append(
    $("<h4></h4>").text("Datos diarios de los entrenamientos")
  );

  entrenos.forEach((dia) => {
    const descripcion = [
      `Temperatura media: ${formatearNumero(dia.temperatura, " °C")}`,
      `Lluvia media: ${formatearNumero(dia.lluvia, " mm")}`,
      `Viento medio: ${formatearNumero(dia.viento, " km/h")}`,
      `Humedad media: ${formatearNumero(dia.humedad, " %")}`,
    ].join(" · ");

    $bloqueEntrenos.append(
      $("<p></p>").text(`${dia.fecha}: ${descripcion}`)
    );
  });

  $entrenosArticle.append($bloqueEntrenos);
};


  ciudad
    .getMeteorologiaCarrera()
    .done(() => {
      const datosCarrera = ciudad.procesarJSONCarrera();
      renderCarrera(datosCarrera);
    })
    .fail(() => {
      mostrarError(
        $carreraArticle,
        "No se ha podido cargar la información de la carrera.",
        "Tiempo durante la carrera"
      );
    });

  ciudad
    .getMeteorologiaEntrenos()
    .done(() => {
      const datosEntrenos = ciudad.procesarJSONEntrenos();
      renderEntrenos(datosEntrenos);
    })
    .fail(() => {
      mostrarError(
        $entrenosArticle,
        "No se ha podido cargar la información de los entrenamientos.",
        "Resumen de los entrenamientos"
      );
    });
});
