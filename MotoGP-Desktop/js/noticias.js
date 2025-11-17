class Noticias {
  #busqueda;
  #url;
  #apiKey;
  #noticias;

  constructor(busqueda, apiKey, url = "https://api.thenewsapi.com/v1/news/all") {
    this.#busqueda = busqueda;
    this.#apiKey = apiKey;
    this.#url = url;
    this.#noticias = [];
  }

  buscar() {
    const endpoint = new URL(this.#url);
    endpoint.searchParams.set("api_token", this.#apiKey);
    endpoint.searchParams.set("language", "es");
    endpoint.searchParams.set("search", this.#busqueda);
    endpoint.searchParams.set("limit", "6");

    return fetch(endpoint.toString()).then((respuesta) => {
      if (!respuesta.ok) {
        throw new Error("No se pudo obtener la información de TheNewsApi");
      }
      return respuesta.json();
    });
  }

  procesarInformacion(datos) {
    if (!datos || !Array.isArray(datos.data)) {
      this.#noticias = [];
      return this.#noticias;
    }

    this.#noticias = datos.data.map((entrada) => ({
      titulo: entrada.title ?? "Noticia sin titular",
      entradilla:
        entrada.description ??
        entrada.snippet ??
        "No se proporcionó un resumen para esta noticia.",
      enlace: entrada.url ?? "",
      fuente: entrada.source ?? "Fuente desconocida",
    }));

    return this.#noticias;
  }

  mostrarNoticias($destino) {
    if (!$destino || $destino.length === 0) {
      return;
    }

    const $section = $("<section></section>");
    $section.append($("<h2></h2>").text("Noticias sobre MotoGP"));

    if (this.#noticias.length === 0) {
      $section.append($("<p></p>").text("No se han encontrado noticias."));
      $destino.append($section);
      return;
    }

    this.#noticias.forEach((noticia) => {
      const $article = $("<article></article>");
      $article.append($("<h3></h3>").text(noticia.titulo));
      $article.append($("<p></p>").text(noticia.entradilla));

      const $detalle = $("<p></p>");
      if (noticia.enlace) {
        const $enlace = $("<a></a>")
          .attr("href", noticia.enlace)
          .attr("target", "_blank")
          .attr("rel", "noopener noreferrer")
          .text("Leer más");
        $detalle.append($enlace);
        $detalle.append(" · ");
      }
      $detalle.append($("<span></span>").text(`Fuente: ${noticia.fuente}`));
      $article.append($detalle);
      $section.append($article);
    });

    $destino.append($section);
  }

  mostrarError($destino, mensaje) {
    if (!$destino || $destino.length === 0) {
      return;
    }

    const $section = $("<section></section>");
    $section.append($("<h2></h2>").text("Noticias sobre MotoGP"));
    $section.append($("<p></p>").text(mensaje));
    $destino.append($section);
  }
}