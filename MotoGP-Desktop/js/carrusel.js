"use strict";

class Carrusel {
    #busqueda;
    #actual;
    #maximo;
    #fotos;
    #temporizador;
    #imagen;

    constructor(busqueda, maximo = 5) {
        this.#busqueda = busqueda;
        this.#actual = 0;
        this.#maximo = maximo;
        this.#fotos = [];
        this.#temporizador = null;
        this.#imagen = null;
    }

    getFotografias() {
        const flickrAPI = "https://api.flickr.com/services/feeds/photos_public.gne?jsoncallback=?";

        $.getJSON(
            flickrAPI,
            {
                tags: this.#busqueda,
                tagmode: "all",
                format: "json"
            }
        )
        .done((datos) => {
            this.procesarJSONFotografias(datos);
            this.mostrarFotografias();
        })
        .fail(() => {
            console.error("Error al obtener las fotos de Flickr");
        });
    }

    procesarJSONFotografias(datosJSON) {
        this.#fotos = [];

        if (!datosJSON || !datosJSON.items) {
            return;
        }

        const numeroFotos = Math.min(this.#maximo, datosJSON.items.length);

        for (let i = 0; i < numeroFotos; i++) {
            const item = datosJSON.items[i];

            const urlM = item.media.m;
            const url640 = urlM.replace("_m.", "_z.");

            const titulo = item.title && item.title.trim() !== ""
                ? item.title
                : "Foto de " + this.#busqueda;

            this.#fotos.push({
                src: url640,
                titulo: titulo
            });
        }
    }

    mostrarFotografias() {
        if (this.#fotos.length === 0) {
            return;
        }
        let $contenedor = $("main");
        if ($contenedor.length === 0) {
            $contenedor = $("body");
        }

        const $section = $("<section></section>");
        const $article = $("<article></article>");
        const $h2Section = $("<h2></h2>").text("Carrusel de imágenes");
        const $h2 = $("<h2></h2>").text(
            "Imágenes del circuito de " + this.#busqueda
        );

        const fotoInicial = this.#fotos[this.#actual];
        const $img = $("<img>")
            .attr("src", fotoInicial.src)
            .attr("alt", fotoInicial.titulo);

       
        $article.append($h2);
        $article.append($img);
        $section.append($h2Section);
        $section.append($article);
        $contenedor.append($section);

        this.#imagen = $img;
        this.#temporizador = setInterval(
            this.cambiarFotografia.bind(this),
            3000
        );
    }

    cambiarFotografia() {
        if (this.#fotos.length === 0) {
            return;
        }
        this.#actual = (this.#actual + 1) % this.#fotos.length;
        const fotoActual = this.#fotos[this.#actual];

        if (this.#imagen && this.#imagen.length) {
            this.#imagen
                .attr("src", fotoActual.src)
                .attr("alt", fotoActual.titulo);
        } else {
            const $img = $("article h2 + img").first();
            $img
                .attr("src", fotoActual.src)
                .attr("alt", fotoActual.titulo);
            this.#imagen = $img;
        }
    }
}
