<?php
session_start();

class Clasificaciones {
    private string $documento;
    private ?string $datos = null;
    private ?SimpleXMLElement $xml = null;

    private string $mensajeEstado = '';
    private string $htmlClasificacion = '';

    public function __construct() {
        $this->documento = __DIR__ . '/xml/circuitoEsquema.xml';
    }

    public function cargarXML(): void {
        $this->datos = @file_get_contents($this->documento);

        if ($this->datos === false) {
            $this->mensajeEstado = '<h3>Error en el archivo XML recibido</h3>';
            return;
        }
        $this->datos = preg_replace(
            '/<\?xml\s+version="1\.1"\s+encoding="UTF-8"\s*\?>/i',
            '<?xml version="1.0" encoding="UTF-8"?>',
            $this->datos,
            1
        );

        libxml_use_internal_errors(true);
        $this->xml = simplexml_load_string($this->datos);

        if ($this->xml === false) {
            $this->mensajeEstado = '<h3>Error al parsear el XML.</h3>';
            libxml_clear_errors();
            return;
        }

        $this->mensajeEstado = '<h3>XML recibido correctamente</h3>';
        $this->xml->registerXPathNamespace('c', 'https://www.uniovi.es');
        $this->generarHtmlClasificacion();
    }


    private function formatearDuracionISO8601(string $duracion): string {
        $pattern = '/^PT(?:(\d+)H)?(?:(\d+)M)?(?:(\d+)S)?$/';

        if (!preg_match($pattern, $duracion, $matches)) {
            return $duracion;
        }

        $horas    = isset($matches[1]) && $matches[1] !== '' ? (int)$matches[1] : 0;
        $minutos  = isset($matches[2]) && $matches[2] !== '' ? (int)$matches[2] : 0;
        $segundos = isset($matches[3]) && $matches[3] !== '' ? (int)$matches[3] : 0;

        $partes = [];

        if ($horas > 0) {
            $partes[] = $horas . ' ' . ($horas === 1 ? 'hora' : 'horas');
        }
        if ($minutos > 0) {
            $partes[] = $minutos . ' ' . ($minutos === 1 ? 'minuto' : 'minutos');
        }
        if ($segundos > 0) {
            $partes[] = $segundos . ' ' . ($segundos === 1 ? 'segundo' : 'segundos');
        }

        if (empty($partes)) {
            return '0 segundos';
        }

        if (count($partes) === 1) {
            return $partes[0];
        }

        $ultima = array_pop($partes);
        return implode(', ', $partes) . ' y ' . $ultima;
    }

    private function generarHtmlClasificacion(): void {
        if ($this->xml === null) {
            $this->htmlClasificacion = '<p>No se ha podido cargar el XML.</p>';
            return;
        }

        $html = '<h2>Datos de clasificación</h2>';

        $vencedorNodes = $this->xml->xpath('/c:circuito/c:vencedor');
        if ($vencedorNodes && isset($vencedorNodes[0])) {
            $vencedor = $vencedorNodes[0];
            $nombreVencedor = (string)$vencedor;
            $duracionRaw = (string)$vencedor['duracion'];
            $duracionBonita = $this->formatearDuracionISO8601($duracionRaw);

            $html .= '<p><strong>Vencedor:</strong> ' .
                     htmlspecialchars($nombreVencedor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') .
                     '</p>';
            $html .= '<p><strong>Duración:</strong> ' .
                     htmlspecialchars($duracionBonita, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') .
                     '</p>';
        }

        $pilotos = $this->xml->xpath('/c:circuito/c:clasificacion/c:piloto');

        if ($pilotos && count($pilotos) > 0) {
            $html .= '<h3>Clasificación completa</h3>';

            $elementos = [];
            foreach ($pilotos as $piloto) {
                $posicion = (string)$piloto['clasificacion'];
                $nombre = (string)$piloto;
                $elementos[] = htmlspecialchars($posicion . '. ' . $nombre, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            }

            $html .= '<p>' . implode(' · ', $elementos) . '</p>';
        } else {
            $html .= '<p>No se ha encontrado la clasificación en el XML.</p>';
        }

        $this->htmlClasificacion = $html;
    }

    public function getMensajeEstado(): string {
        return $this->mensajeEstado;
    }

    public function getHtmlClasificacion(): string {
        return $this->htmlClasificacion;
    }
}
$clasificaciones = new Clasificaciones();
$clasificaciones->cargarXML();

$mensajeEstado     = $clasificaciones->getMensajeEstado();
$htmlClasificacion = $clasificaciones->getHtmlClasificacion();

echo "<!DOCTYPE html>
<html lang='es'>

<head>
    <meta charset='UTF-8'>
    <title>MotoGP-Desktop: Clasificaciones</title>
    <meta name='author' content='David Fernando Bolanos Lopez' />
    <meta name='description' content='Clasificaciones de los distintos pilotos' />
    <meta name='keywords' content='moto,motogp,piloto' />
    <meta name='viewport' content='width=device-width, initial-scale=1.0' />
    <link rel='stylesheet' href='estilo/estilo.css'/>
    <link rel='stylesheet' href='estilo/layout.css'/>
    <link rel='icon' href='multimedia/icon.ico' type='image/png' sizes='32x32'>
</head>

<body>
    <header>
        <h1><a href='index.html'>MotoGP Desktop</a></h1>
        <nav>
            <a href='index.html' title='Indice de MotoGP-Desktop'>Inicio</a>
            <a href='piloto.html' title='Informacion del piloto'>Piloto</a>
            <a href='circuito.html' title='Cirucuito a recorrer'>Circuito</a>
            <a href='meteorologia.html' title='Clima durante carrera'>Meteorologia</a>
            <a class='active' href='clasificaciones.php' title='Clasificacion actualizada'>Clasificaciones</a>
            <a href='juegos.html' title='Juegos desarrollados'>Juegos</a>   
            <a href='ayuda.html' title='Ayuda'>Ayuda</a>
        </nav>
    </header>

    <p>Estas en: <a href='index.html'>Inicio</a> > <strong>Clasificaciones</strong></p>
    <h2>Clasificaciones de los pilotos</h2>

    <section>
        $htmlClasificacion
    </section>
</body>

</html>";
?>
