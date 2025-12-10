<?php
require_once '../cronometro.php';

class Test {

    private $preguntas = [];
    private $respuestas = [];
    private $indiceActual = 0;
    private $cronometro;
    private $iniciada = false;
    private $finalizada = false;

    public function __construct() {
        $this->preguntas = [
            "Pregunta 1: ¿Cómo se llama el piloto de Moto GP en que está enfocada esta página?",
            "Pregunta 2: ¿Qué día fue la carrera de Moto GP en el circuito de Balaton Park?",
            "Pregunta 3: ¿Cuántas vueltas tuvo esta carrera?",
            "Pregunta 4: ¿Quién ganó la carrera?",
            "Pregunta 5: ¿Con qué tiempo finalizó el ganador?",
            "Pregunta 6: ¿Qué conceptos se tienen en cuenta cuando hablabamos del piloto?",
            "Pregunta 7: ¿Qué concepto se menciona en el audio?",
            "Pregunta 8: ¿En qué país se encuentra el circuito Balaton Park?",
            "Pregunta 9: ¿Cuál es la altura máxima registrada sobre el circuito?",
            "Pregunta 10: ¿Quién fue el patrocinador principal de la MotoGP celebrada en el Balaton Park?"
        ];

        $this->respuestas = array_fill(0, count($this->preguntas), "");

        $this->cronometro = new Cronometro(); 
    }


    public function estaIniciada(): bool {
        return $this->iniciada;
    }

    public function estaFinalizada(): bool {
        return $this->finalizada;
    }

    public function getPreguntaActual(): string {
        return $this->preguntas[$this->indiceActual];
    }

    public function getNumeroPreguntaActual(): int {
        return $this->indiceActual + 1;
    }

    public function getTotalPreguntas(): int {
        return count($this->preguntas);
    }

    public function esUltimaPregunta(): bool {
        return $this->indiceActual === count($this->preguntas) - 1;
    }

    public function iniciar(): void {
        if (!$this->iniciada) {
            $this->iniciada = true;
            $this->cronometro->arrancar(); 
        }
    }

    public function guardarRespuestaActual(string $respuesta): void {
        $this->respuestas[$this->indiceActual] = trim($respuesta);
    }

    public function respuestaActualVacia(): bool {
        return $this->respuestas[$this->indiceActual] === "";
    }

    public function siguientePregunta(): void {
        if ($this->indiceActual < count($this->preguntas) - 1) {
            $this->indiceActual++;
        }
    }

    public function terminar(): void {
        if (!$this->finalizada) {
            $this->finalizada = true;
            $this->cronometro->parar();
        }
    }


    public function getRespuestas(): array {
        return $this->respuestas;
    }

    public function getDuracionSegundos(): float {    
        return $this->cronometro->getTiempo(); 
    }
}
