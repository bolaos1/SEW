"use strict";
class Circuito {
    #inputArchivo;
    #contenedorMensaje;
    #contenedorInfo;

    constructor() {
        this.#inputArchivo = document.querySelector("input[accept='.html']");

        const h2 = document.querySelector("h2");
        this.#contenedorMensaje = document.createElement("p");

        h2.insertAdjacentElement("afterend", this.#contenedorMensaje);

        this.#contenedorInfo = document.createElement("section");

        if (!this.#inputArchivo) {
            document.body.appendChild(this.#contenedorInfo);
        }
            
        
        this.#comprobarApiFile();
    }

    #comprobarApiFile() {
        const soportaFileAPI =
            "File" in window &&
            "FileReader" in window &&
            "FileList" in window &&
            "Blob" in window;

        if (!soportaFileAPI) {
            this.#mostrarMensaje(
                "Este navegador no soporta la API File de HTML5. " +
                "No se puede leer el archivo InfoCircuito.html."
            );

            if (this.#inputArchivo) {
                this.#inputArchivo.disabled = true;
            }
            return;
        }

        this.#cargarEventos();
    }

    #cargarEventos() {
        if (!this.#inputArchivo) {
            this.#mostrarMensaje(
                "No se ha encontrado el control para seleccionar el archivo."
            );
            return;
        }

        this.#inputArchivo.addEventListener(
            "change",
            this.#leerArchivoHTML.bind(this)
        );
    }

    #leerArchivoHTML(evento) {
        const listaArchivos = evento.target.files;
        if (!listaArchivos || listaArchivos.length === 0) {
            this.#mostrarMensaje("No se ha seleccionado ningún archivo.");
            return;
        }

        const archivo = listaArchivos[0];
        if (!archivo.name.toLowerCase().endsWith(".html")) {
            this.#mostrarMensaje(
                "Por favor, selecciona un archivo HTML (InfoCircuito.html)."
            );
            return;
        }

        const lector = new FileReader();

        lector.onload = (e) => {
            const contenido = e.target.result;
            this.#procesarHTMLCircuito(contenido);
        };

        lector.onerror = () => {
            this.#mostrarMensaje(
                "Se ha producido un error al leer el archivo."
            );
        };

        lector.readAsText(archivo, "UTF-8");
    }

    #procesarHTMLCircuito(contenido) {
        this.#contenedorInfo.innerHTML = "";

        const parser = new DOMParser();
        const doc = parser.parseFromString(contenido, "text/html");
        const cuerpo = doc.body;

        if (!cuerpo) {
            this.#mostrarMensaje(
                "No se ha podido procesar el contenido del archivo HTML."
            );
            return;
        }


        const imagenes = cuerpo.querySelectorAll("img");
        imagenes.forEach((img) => {
            const initialSRC = img.getAttribute("src");
            if (!initialSRC) return;

            const nombre = initialSRC.split("/").pop();
            const nuevaSRC = "multimedia/" + nombre;

            img.setAttribute("src", nuevaSRC);
        });

        const pictureSources = cuerpo.querySelectorAll("picture source");
        pictureSources.forEach((source) => {
            const initialSRCSet = source.getAttribute("srcset");
            if (!initialSRCSet) return;

            const partes = initialSRCSet.split(",");
            const nuevasPartes = partes.map((parte) => {
                const trimmed = parte.trim();
                const [url, descriptor] = trimmed.split(/\s+/, 2);
                const nombre = url.split("/").pop();
                const nuevaURL = "multimedia/" + nombre;
                return descriptor ? `${nuevaURL} ${descriptor}` : nuevaURL;
            });

            source.setAttribute("srcset", nuevasPartes.join(", "));
        });
        const videos = cuerpo.querySelectorAll("video");
        videos.forEach((video) => {
            const source = video.querySelector("source");
            if (!source) return;

            const initialSRC = source.getAttribute("src");
            if (!initialSRC) return;

            const nombre = initialSRC.split("/").pop();
            const nuevaSRC = "multimedia/" + nombre;

            source.setAttribute("src", nuevaSRC);
            video.load();
        });




        const h2 = document.createElement("h2");
        h2.textContent =
            "Elementos recuperados durante el procesamiento del HTML";
        this.#contenedorInfo.append(h2);

        const hijos = Array.from(cuerpo.children);
        hijos.forEach((nodo) => {
            this.#contenedorInfo.appendChild(nodo.cloneNode(true));
        });
    }

    #mostrarMensaje(texto) {
        if (this.#contenedorMensaje) {
            this.#contenedorMensaje.textContent = texto;
        } else {
            console.log(texto);
        }
    }
}

"use strict";

class CargadorSVG {
    #inputArchivoSVG;
    #contenedorSVG;
    #contenedorMensaje;

    constructor() {
        this.#inputArchivoSVG = document.querySelector("input[accept='.svg']");
        this.#contenedorMensaje = document.createElement("p");
        this.#contenedorSVG = document.createElement("section");

        if (this.#inputArchivoSVG) {
            
            this.#contenedorMensaje.insertAdjacentElement(
                "afterend",
                this.#contenedorSVG
            );

            this.#cargarEventos();
        } else {
            document.body.appendChild(this.#contenedorMensaje);
            document.body.appendChild(this.#contenedorSVG);
            this.#mostrarMensaje(
                "No se ha encontrado el control para seleccionar el SVG."
            );
        }
    }

    #cargarEventos() {
        this.#inputArchivoSVG.addEventListener(
            "change",
            this.#leerArchivoSVG.bind(this)
        );
    }

    #mostrarMensaje(texto) {
        if (this.#contenedorMensaje) {
            this.#contenedorMensaje.textContent = texto;
        } else {
            console.log(texto);
        }
    }

    #leerArchivoSVG(evento) {
        const archivos = evento.target.files;
        if (!archivos || archivos.length === 0) {
            this.#mostrarMensaje("No se ha seleccionado ningún archivo SVG.");
            return;
        }

        const archivo = archivos[0];
        const nombre = archivo.name.toLowerCase();
        const esSVGPorNombre = nombre.endsWith(".svg");
        const esSVGPorTipo = archivo.type === "image/svg+xml";

        if (!esSVGPorNombre && !esSVGPorTipo) {
            this.#mostrarMensaje("El archivo seleccionado no es un SVG.");
            return;
        }

        const lector = new FileReader();

        lector.onload = (e) => {
            const contenido = e.target.result;
            this.insertarSVG(contenido);
        };

        lector.onerror = () => {
            this.#mostrarMensaje(
                "Se ha producido un error al leer el archivo SVG."
            );
        };

        lector.readAsText(archivo, "UTF-8");
    }

    insertarSVG(contenido) {
        const parser = new DOMParser();
        const doc = parser.parseFromString(contenido, "image/svg+xml");

        const errorNode = doc.querySelector("parsererror");
        if (errorNode) {
            this.#mostrarMensaje(
                "El archivo SVG contiene errores y no se ha podido mostrar."
            );
            console.error(errorNode.textContent);
            return;
        }

        const svg = doc.documentElement;

        if (svg && svg.tagName.toLowerCase() === "svg") {
            const versionActual = svg.getAttribute("version");
            if (versionActual !== "1.1") {
                svg.setAttribute("version", "1.1");
            }
        }
       
        this.#contenedorSVG.innerHTML = "";
        const h2 = document.createElement("h2");
        h2.textContent =
            "Elementos recuperados durante el procesamiento del SVG";
        this.#contenedorSVG.appendChild(h2);
        this.#contenedorSVG.appendChild(svg);
        this.#mostrarMensaje("");
    }
}

class CargadorKML {
    #inputArchivoKML;
    #contenedorMensaje;
    #mapa;
    #origen;
    #tramoCoordenadas; 

    constructor() {
        this.#inputArchivoKML = document.querySelector("input[accept='.kml']");
        this.#contenedorMensaje = document.createElement("p");
        this.#mapa = null;
        this.#origen = null;
        this.#tramoCoordenadas = [];

        if (this.#inputArchivoKML) {
            this.#inputArchivoKML.insertAdjacentElement(
                "afterend",
                this.#contenedorMensaje
            );
            this.#inputArchivoKML.addEventListener(
                "change",
                this.leerArchivoKML.bind(this)
            );
        } else {
            document.body.appendChild(this.#contenedorMensaje);
            this.#mostrarMensaje(
                "No se ha encontrado el control para seleccionar el archivo KML."
            );
        }
    }

    establecerMapa(mapa) {
        this.#mapa = mapa;
        this.#intentarDibujarCircuito();
    }

    #mostrarMensaje(texto) {
        if (this.#contenedorMensaje) {
            this.#contenedorMensaje.textContent = texto;
        } else {
            console.log(texto);
        }
    }

    
    leerArchivoKML(evento) {
        const archivos = evento.target.files;
        if (!archivos || archivos.length === 0) {
            this.#mostrarMensaje("No se ha seleccionado ningún archivo KML.");
            return;
        }

        const archivo = archivos[0];
        const nombre = archivo.name.toLowerCase();
        const esKMLPorNombre = nombre.endsWith(".kml");

        if (!esKMLPorNombre) {
            this.#mostrarMensaje("El archivo seleccionado no es un KML.");
            return;
        }

        const lector = new FileReader();

        lector.onload = (e) => {
            const contenido = e.target.result;
            this.#procesarKML(contenido);
        };

        lector.onerror = () => {
            this.#mostrarMensaje(
                "Se ha producido un error al leer el archivo KML."
            );
        };

        lector.readAsText(archivo, "UTF-8");
    }

    #procesarKML(contenido) {
        const parser = new DOMParser();
        const doc = parser.parseFromString(contenido, "application/xml");

        const errorNode = doc.querySelector("parsererror");
        if (errorNode) {
            this.#mostrarMensaje(
                "El archivo KML contiene errores y no se ha podido procesar."
            );
            console.error(errorNode.textContent);
            return;
        }

        
        let origenLatLng = null;
        const puntosOrigen = doc.getElementsByTagNameNS("*", "Point");
        if (puntosOrigen.length > 0) {
            const coordsNode =
                puntosOrigen[0].getElementsByTagNameNS("*", "coordinates")[0];
            if (coordsNode && coordsNode.textContent) {
                const texto = coordsNode.textContent.trim();
                const partes = texto.split(",");
                if (partes.length >= 2) {
                    const lon = parseFloat(partes[0]);
                    const lat = parseFloat(partes[1]);
                    if (!isNaN(lat) && !isNaN(lon)) {
                        origenLatLng = { lat: lat, lng: lon };
                    }
                }
            }
        }

    
        const lineStrings = doc.getElementsByTagNameNS("*", "LineString");
        const puntos = [];

        Array.from(lineStrings).forEach((ls) => {
            const coordsNode =
                ls.getElementsByTagNameNS("*", "coordinates")[0];
            if (!coordsNode || !coordsNode.textContent) return;

            const texto = coordsNode.textContent.trim();
            const pares = texto.split(/\s+/);
            pares.forEach((par) => {
                const partes = par.split(",");
                if (partes.length >= 2) {
                    const lon = parseFloat(partes[0]);
                    const lat = parseFloat(partes[1]);
                    if (!isNaN(lat) && !isNaN(lon)) {
                        puntos.push({ lat: lat, lng: lon });
                    }
                }
            });
        });

    
        if (!origenLatLng && puntos.length > 0) {
            origenLatLng = puntos[0];
        }

        if (!origenLatLng || puntos.length === 0) {
            this.#mostrarMensaje(
                "No se han podido obtener las coordenadas del circuito desde el KML."
            );
            
            return;
        }

        this.#origen = origenLatLng;
        this.#tramoCoordenadas = puntos;
        this.#mostrarMensaje("");

        console.log(
            "LineString encontrados:",
            lineStrings.length,
            "puntos:",
            puntos.length
        );

        this.#intentarDibujarCircuito();
    }

    #intentarDibujarCircuito() {
        if (
            !this.#mapa ||
            !this.#origen ||
            this.#tramoCoordenadas.length === 0
        ) {
            return;
        }
        this.insertarCapaKML();
    }


    insertarCapaKML() {
        new google.maps.Marker({
            position: this.#origen,
            map: this.#mapa,
            title: "Origen del circuito",
        });

        const polilinea = new google.maps.Polyline({
            path: this.#tramoCoordenadas,
            map: this.#mapa,
            strokeColor: "#000000",
            strokeWeight: 5,
        });

        const bounds = new google.maps.LatLngBounds();
        this.#tramoCoordenadas.forEach((p) => bounds.extend(p));
        this.#mapa.fitBounds(bounds);
    }
}

let cargadorKML = null;
const circuito = new Circuito();
const cargadorSVG = new CargadorSVG();
cargadorKML = new CargadorKML();

function initMap() {
    let contenedorMapa = document.querySelector("body > div");

    if (!contenedorMapa) {
        contenedorMapa = document.createElement("div");
        document.body.appendChild(contenedorMapa);
    }

    const mapa = new google.maps.Map(contenedorMapa, {
        center: { lat: 0, lng: 0 },
        zoom: 3,
    });

    cargadorKML.establecerMapa(mapa);
}


window.initMap = initMap;

