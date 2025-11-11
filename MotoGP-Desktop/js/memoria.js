class Memoria {
    constructor() {
        this.tablero_bloqueado = false;
        this.primera_carta = null;
        this.segunda_carta = null;
        cronometro = new Cronometro();
        cronometro.arrancar();
    }
    barajarCartas() {
        let tablero = document.querySelector("main");
        if (!tablero) return;

        let cartas = Array.from(tablero.querySelectorAll("article"));

        for (let i = cartas.length - 1; i > 0; i--) {
            let j = Math.floor(Math.random() * (i + 1));
            [cartas[i], cartas[j]] = [cartas[j], cartas[i]];
        }

        cartas.forEach(carta => tablero.appendChild(carta));
    }

    reiniciarAtributos() {
        this.tablero_bloqueado = false;
        this.primera_carta = null;
        this.segunda_carta = null;
    }

    voltearCarta(carta) {
        let estado = carta.dataset.estado;
        if (this.tablero_bloqueado || estado === "volteada" || estado === "revelada") {
            return;
        }

        carta.dataset.estado = "volteada";
        if (!this.primera_carta) {
            this.primera_carta = carta;
            return;
        }

        this.segunda_carta = carta;
        this.comprobarPareja();
    }

    comprobarPareja() {
        if (!this.primera_carta || !this.segunda_carta){
            return;
        } 
        let img1 = this.primera_carta.querySelector("img");
        let img2 = this.segunda_carta.querySelector("img");
        let esPareja = img1.src === img2.src;
        esPareja ? this.deshabilitarCartas() : this.cubrirCartas();
    }

    cubrirCartas() {
        this.tablero_bloqueado = true;

        setTimeout(() => {
            if (this.primera_carta && this.primera_carta.dataset.estado === "volteada") {
                this.primera_carta.dataset.estado = "none";
            }
            if (this.segunda_carta && this.segunda_carta.dataset.estado === "volteada") {
                this.segunda_carta.dataset.estado = "none";
            }
            this.reiniciarAtributos();
        }, 1000); 
    }

    deshabilitarCartas() {
        this.primera_carta.dataset.estado = "revelada";
        this.segunda_carta.dataset.estado = "revelada";

        this.reiniciarAtributos();
        this.comprobarJuego();
    }
    comprobarJuego() {
        let cartas = document.querySelectorAll("main article");
        let quedaSinRevelar = Array.from(cartas)
            .some(carta => carta.dataset.estado !== "revelada");
        if(!quedaSinRevelar){
            cronometro.parar();
        }
       
    }
}
