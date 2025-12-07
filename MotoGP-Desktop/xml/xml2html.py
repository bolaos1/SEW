import xml.etree.ElementTree as ET
from pathlib import Path
from html import escape


XML_IN = Path(r"circuitoEsquema.xml")
HTML_OUT = Path(r"InfoCircuito.html")

NS = {'u': 'https://www.uniovi.es'}


class Html:
    def __init__(self, titulo):
        self.parts = []
        self.parts.append("<!DOCTYPE html>")
        self.parts.append('<html lang="es">')
        self.parts.append("<head>")
        self.parts.append('<meta charset="utf-8">')
        self.parts.append('<meta name="author" content="David Fernando Bolanos Lopez">')
        self.parts.append('<meta name="description" content="Información del circuito">')
        self.parts.append('<meta name="keywords" content="moto,motogp,piloto">')
        self.parts.append('<meta name="viewport" content="width=device-width, initial-scale=1">')
        self.parts.append(f"<title>{escape(titulo)}</title>")
        self.parts.append('<link rel="stylesheet" href="../estilo/estilo.css">')
        self.parts.append('<link rel="stylesheet" href="../estilo/layout.css">')
        self.parts.append('<link rel="icon" href="multimedia/icon.ico" type="image/png" sizes="32x32">')
        self.parts.append("</head>")
        self.parts.append("<body>")

    def header(self, titulo, active):
        menu = [
            ("../index.html",          "Inicio",          "Índice de MotoGP-Desktop", "inicio"),
            ("../piloto.html",         "Piloto",          "Información del piloto",   "piloto"),
            ("../circuito.html",       "Circuito",        "Circuito a recorrer",      "circuito"),
            ("../meteorologia.html",   "Meteorología",    "Clima durante carrera",    "meteorologia"),
            ("../clasificaciones.php","Clasificaciones", "Clasificación actualizada","clasificaciones"),
            ("../juegos.html",         "Juegos",          "Juegos desarrollados",     "juegos"),
            ("../ayuda.html",          "Ayuda",           "Ayuda",                     "ayuda"),
        ]
        items = []
        for href, text, title, key in menu:
            cls = ' class="active"' if key == active else ""
            items.append(f'<a href="{escape(href)}" title="{escape(title)}"{cls}>{escape(text)}</a>')
        items_html = "\n      ".join(items)

        self.parts.append(f"""<header>
  <h1><a href="index.html">{escape(titulo)}</a></h1>
  <nav>
      {items_html}
  </nav>
</header>""")

    def section(self, title, inner_html):
        self.parts.append("<section>")
        self.parts.append(f"<h3>{escape(title)}</h3>")
        self.parts.append(inner_html)
        self.parts.append("</section>")

    def a_href(self, href, text=None, title=None):
        text = text or href
        title_attr = f' title="{escape(title)}"' if title else ""
        return f'<a href="{escape(href)}"{title_attr}>{escape(text)}</a>'

    def end(self):
        self.parts.append("</body>")
        self.parts.append("</html>")
        return "\n".join(self.parts)


def generar_html(xml_path: Path, out_path: Path):
    def parse_duration(s: str) -> str:
        s = s.strip()
        if not s.startswith("PT"):
            return s
        s = s[2:]
        m_str, s_str = s.split("M")
        s_str = s_str[:-1]
        mm = int(m_str)
        ss = int(s_str)
        return f"{mm}:{ss:02d}"

    if not xml_path.exists():
        raise SystemExit(f"No existe el XML:\n{xml_path}")

    tree = ET.parse(str(xml_path))
    root = tree.getroot()

    nombre = root.findtext('u:nombre', default='Circuito', namespaces=NS) or 'Circuito'
    pais = root.findtext('u:pais', default='', namespaces=NS) or ''
    loc = root.findtext('u:localidad', default='', namespaces=NS) or ''
    dist = root.findtext('u:distancia', default='', namespaces=NS) or ''
    ancho = root.findtext('u:ancho', default='', namespaces=NS) or ''
    fecha = (root.findtext('u:fecha', default='', namespaces=NS) or '').strip()
    hora = root.findtext('u:hora', default='', namespaces=NS) or ''
    vueltas = root.findtext('u:vueltas', default='', namespaces=NS) or ''
    sponsor = root.findtext('u:patrocinadorPrincipal', default='', namespaces=NS) or ''

    refs = []
    for e in root.findall('u:referencias/u:enlace', namespaces=NS):
        href = (e.text or '').strip()
        alt = e.get('alt') or ''
        if href:
            refs.append((href, alt))

    vencedor = root.findtext('u:resultado/u:vencedor', default='', namespaces=NS) or ''
    duracion = root.findtext('u:resultado/u:tiempo', default='', namespaces=NS) or ''

    tabla_clas = []
    for p in root.findall('u:clasificacion/u:piloto', namespaces=NS):
        pos = p.get('clasificacion') or ''
        nombre_p = (p.text or '').strip()
        tabla_clas.append((pos, nombre_p))

    html = Html(titulo=f"MotoGP-Desktop — {nombre}")
    

    if vencedor or tabla_clas:
        inner = ""
        if vencedor:
            inner += f"<p>Vencedor: {escape(vencedor)}</p>"
            if duracion:
                inner += f'<p> Tiempo: {parse_duration(duracion)}</p>'
        if tabla_clas:
            inner += '<p>Clasificacion final</p>'
            inner += "<ol>"
            for pos, nombre_p in tabla_clas:
                inner += f"<li>{escape(nombre_p)}</li>"
            inner += "</ol>"

        html.section("Resultados", inner)

    bloque_dl = "<dl>"
    if loc:
        bloque_dl += f"<dt>Localidad</dt><dd>{escape(loc)}</dd>"
    if pais:
        bloque_dl += f"<dt>País</dt><dd>{escape(pais)}</dd>"
    if dist:
        bloque_dl += f"<dt>Distancia</dt><dd>{escape(dist)}</dd>"
    if ancho:
        bloque_dl += f"<dt>Ancho</dt><dd>{escape(ancho)}</dd>"
    if vueltas:
        bloque_dl += f"<dt>Vueltas</dt><dd>{escape(vueltas)}</dd>"
    if fecha:
        bloque_dl += f"<dt>Fecha</dt><dd>{escape(fecha)}</dd>"
    if hora:
        bloque_dl += f"<dt>Hora</dt><dd>{escape(hora)}</dd>"
    if sponsor:
        bloque_dl += f"<dt>Patrocinador principal</dt><dd>{escape(sponsor)}</dd>"
    bloque_dl += "</dl>"

    html.section("Datos del circuito", bloque_dl)

    imgs = []
    for e in root.findall('u:imagenes/u:img', namespaces=NS):
        src = (e.text or '').strip()     
        alt = e.get('alt') or ''       
        if src:
            imgs.append((src, alt))

    if imgs:
        pics = []
        for src, alt in imgs:
            base, ext = src.rsplit(".", 1)
            small = f"{base}Small.{ext}"
            mid = f"{base}Mid.{ext}"
            large = f"{base}Max.{ext}"

            pics.append(
                "<picture>\n"
                f'  <source media="(max-width: 465px)" srcset="{escape(small)}" />\n'
                f'  <source media="(max-width:799px)" srcset="{escape(mid)}" />\n'
                f'  <img src="{escape(large)}" alt="{escape(alt)}" />\n'
                "</picture>"
            )

        html.section("Imágenes", "".join(pics))

    vids = []
    for e in root.findall('u:videos/u:video', namespaces=NS):
        src = (e.text or '').strip()
        tipo = e.get('tipo') or 'video/mp4'
        titulo_v = e.get('titulo') or ''
        if src:
            vids.append((src, tipo, titulo_v))

    if vids:
        vhtml = "<video controls>" + "".join(
            f'<source  src="{escape(src)}" type="{escape(tipo)}" />'
            for src, tipo, titulo_v in vids
        ) + "</video>"
        html.section("Vídeos", vhtml)

    if refs:
        lst = "<ul>" + "".join(
            (f"<li>{escape(alt)}: {html.a_href(h)}</li>" if alt else f"<li>{html.a_href(h)}</li>")
            for h, alt in refs
        ) + "</ul>"
        html.section("Referencias", lst)

    out_path.write_text(html.end(), encoding="utf-8")
    return out_path


if __name__ == "__main__":
    salida = generar_html(XML_IN, HTML_OUT)
    print(f"Generado: {salida}")
