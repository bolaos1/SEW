class Cronometro { 
    constructor() {
        this.reset();
        this.inicio = null;
        this.corriendo = null;
    }

    arrancar() {
        if (this.corriendo !== null) {
            return;
        }
        if(this.inicio !== null){
            return;
        }
        
        try {
            this.inicio = Temporal.Now.instant();
        } catch (error) {
            this.inicio = new Date();
        }

        this.actualizar();
        this.corriendo = setInterval(this.actualizar.bind(this), 100);
    }

    actualizar() {
        if (!this.inicio) {
            return;
        }

        try {
            const ahora = Temporal.Now.instant();
            this.tiempo = ahora.epochMilliseconds - this.inicio.epochMilliseconds;
        } catch (error) {
            const ahora = new Date();
            this.tiempo = ahora.getTime() - this.inicio.getTime();
        }

        this.mostrar();
    }

    mostrar() {
        const totalDecimas = Math.floor(this.tiempo / 100);

        const minutos  = Math.floor(totalDecimas / 600);        
        const segundos = Math.floor((totalDecimas % 600) / 10); 
        const decimas  = totalDecimas % 10;

        const mm = String(minutos).padStart(2, "0");
        const ss = String(segundos).padStart(2, "0");

        const texto = `${mm}:${ss}.${decimas}`;
        const p = document.querySelector("main p");
        if (p) {
            p.textContent = texto;
        }
    }

    parar() {
        if (this.corriendo !== null) {
            clearInterval(this.corriendo);
            this.corriendo = null;
        }
    }

    reset() {
        this.tiempo = 0;
    }

    reiniciar() {
        this.parar();
        this.reset();
        this.inicio = null;
        this.mostrar(); 
    }
}
