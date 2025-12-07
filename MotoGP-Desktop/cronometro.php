<?php
class Cronometro {
    private $inicio;
    private $tiempo;

    public function __construct() {
        $this->inicio = null;
        $this->tiempo = 0.0;
    }

    public function arrancar() {
        $this->inicio = microtime(true);
        $this->tiempo = 0.0;
    }

    public function parar() {
        if ($this->inicio !== null) {
            $this->tiempo = microtime(true) - $this->inicio;
            $this->inicio = null;
        }
    }

    public function getTiempo() {
        if ($this->inicio !== null) {
            // Si sigue corriendo, devolvemos el tiempo hasta ahora
            return microtime(true) - $this->inicio;
        }
        return $this->tiempo;
    }

    public function reiniciar() {
        $this->inicio = null;
        $this->tiempo = 0.0;
    }

    public function estaArrancado() {
        return $this->inicio !== null;
    }


    public function mostrar() {
        $totalDecimas = (int) round($this->getTiempo() * 10);
        $decimas      = $totalDecimas % 10;
        $totalSeg     = intdiv($totalDecimas, 10);
        $segundos     = $totalSeg % 60;
        $minutos      = intdiv($totalSeg, 60);

        return sprintf('%02d:%02d.%d', $minutos, $segundos, $decimas);
    }
}
?>
