<?php
require_once '../cronometro.php';

class Test {

    private $preguntas = [];
    private $respuestas = [];
    private $indiceActual = 0;
    private $cronometro;
    private $iniciada = false;
    private $finalizada = false;
    private $fase = 'datos_usuario';
    private $horaInicio = null;
    private $horaFin = null;
    private $datosUsuario = [
        'profesion' => '',
        'edad' => 0,
        'genero' => '',
        'pericia_informatica' => '',
        'dispositivo' => 'ordenador'
    ];
    private $comentariosUsuario = '';
    private $propuestasMejora = '';
    private $valoracion = 0;

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

    public function getFase(): string {
        return $this->fase;
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
            $this->horaInicio = date('Y-m-d H:i:s');
            $this->fase = 'preguntas';
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

    public function finalizarPreguntas(): void {
        if (!$this->finalizada) {
            $this->finalizada = true;
            $this->cronometro->parar();
            $this->horaFin = date('Y-m-d H:i:s');
            $this->fase = 'comentarios_usuario';
        }
    }


    public function getRespuestas(): array {
        return $this->respuestas;
    }

    public function getDuracionSegundos(): float {
        return $this->cronometro->getTiempo();
    }

    public function setDatosUsuario(
        string $profesion,
        int $edad,
        string $genero,
        string $periciaInformatica,
        string $dispositivo
    ): void {
        $this->datosUsuario = [
            'profesion' => $profesion,
            'edad' => $edad,
            'genero' => $genero,
            'pericia_informatica' => $periciaInformatica,
            'dispositivo' => $dispositivo
        ];
    }

    public function getDatosUsuario(): array {
        return $this->datosUsuario;
    }

    public function setComentariosUsuario(
        string $comentariosUsuario,
        string $propuestasMejora,
        int $valoracion
    ): void {
        $this->comentariosUsuario = $comentariosUsuario;
        $this->propuestasMejora = $propuestasMejora;
        $this->valoracion = $valoracion;
    }

    public function getComentariosUsuario(): string {
        return $this->comentariosUsuario;
    }

    public function getPropuestasMejora(): string {
        return $this->propuestasMejora;
    }

    public function getValoracion(): int {
        return $this->valoracion;
    }

    public function setFase(string $fase): void {
        $this->fase = $fase;
    }

    public function getHoraInicio(): ?string {
        return $this->horaInicio;
    }

    public function getHoraFin(): ?string {
        return $this->horaFin;
    }
}
