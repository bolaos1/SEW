"use strict";

$(function () {
  const carrusel = new Carrusel("Balaton Park");
  carrusel.getFotografias();

  const $main = $("main");
  const apiKey ="Z94zExsvk1FOStLyVQ0jIjiVu0diJvZXmDW1x5HM"

  const noticias = new Noticias("MotoGP", apiKey);
  noticias
    .buscar()
    .then((datos) => {
      noticias.procesarInformacion(datos);
      noticias.mostrarNoticias($main);
    })
    .catch(() => {
      noticias.mostrarError(
        $main,
        "No se han podido cargar las noticias en este momento."
      );
    });
});