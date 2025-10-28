# -*- coding: utf-8 -*-
"""
Genera altimetría SVG a partir de circuito.xml (Balaton Park).
Lee con XPath (namespace https://www.uniovi.es).
Escribe altimetria.svg en la misma carpeta del XML.

Autor: tú :)
"""

import xml.etree.ElementTree as ET
from pathlib import Path
import math

XML_PATH = Path(
    r"circuitoEsquema.xml")
SVG_OUT = Path(
    r"altimetria.svg")

NS = {"u": "https://www.uniovi.es"}


class Svg(object):

    def __init__(self, width=800, height=400, viewBox=None, bg=None):
        ET.register_namespace("", "http://www.w3.org/2000/svg")

        self.raiz = ET.Element(
            "svg",
            {
                "xmlns": "http://www.w3.org/2000/svg",
                "version": "2.0",
                "width": str(width),
                "height": str(height),
                "viewBox": viewBox or f"0 0 {width} {height}",
            },
        )
        if bg:
            self.addRect(0, 0, "100%", "100%", fill=bg,
                         strokeWidth=0, stroke="none")

    def addRect(self, x, y, width, height, fill="none", strokeWidth=1, stroke="#000"):
        ET.SubElement(
            self.raiz,
            "rect",
            x=str(x),
            y=str(y),
            width=str(width),
            height=str(height),
            fill=str(fill),
            stroke=str(stroke),
            **({"stroke-width": str(strokeWidth)})
        )

    def addLine(self, x1, y1, x2, y2, stroke="#000", strokeWidth=1, dash=None):
        attrs = {
            "x1": str(x1),
            "y1": str(y1),
            "x2": str(x2),
            "y2": str(y2),
            "stroke": str(stroke),
            "stroke-width": str(strokeWidth),
        }
        if dash:
            attrs["stroke-dasharray"] = dash
        ET.SubElement(self.raiz, "line", **attrs)

    def addPolyline(self, points, stroke="#000", strokeWidth=1, fill="none"):
        ET.SubElement(
            self.raiz,
            "polyline",
            points=str(points),
            fill=str(fill),
            stroke=str(stroke),
            **({"stroke-width": str(strokeWidth)})
        )

    def addPolygon(self, points, fill="#ccc", opacity=1.0, stroke="none"):
        ET.SubElement(
            self.raiz,
            "polygon",
            points=str(points),
            fill=str(fill),
            **({"fill-opacity": str(opacity), "stroke": str(stroke)})
        )

    def addCircle(self, cx, cy, r, fill="none", strokeWidth=1, stroke="#000"):
        ET.SubElement(
            self.raiz,
            "circle",
            cx=str(cx),
            cy=str(cy),
            r=str(r),
            fill=str(fill),
            stroke=str(stroke),
            **({"stroke-width": str(strokeWidth)})
        )

    def addText(self, texto, x, y, fontFamily="Verdana", fontSize=12, extra_style=""):
        attrs = {
            "x": str(x),
            "y": str(y),
            "font-family": fontFamily,
            "font-size": str(fontSize),
        }
        if extra_style:
            attrs["style"] = extra_style
        t = ET.SubElement(self.raiz, "text", **attrs)
        t.text = str(texto)


    def escribir(self, nombreArchivoSVG):
        arbol = ET.ElementTree(self.raiz)
        try:
            ET.indent(arbol)  # Python 3.9+
        except Exception:
            pass
        arbol.write(nombreArchivoSVG, encoding="utf-8", xml_declaration=True)

    def ver(self):
        print("\nElemento raiz =", self.raiz.tag)
        print("Atributos     =", self.raiz.attrib)
        for hijo in self.raiz.findall(".//*"):
            print(" -", hijo.tag, hijo.attrib, (hijo.text or "").strip())


def cargar_perfil(xml_path: Path):
    root = ET.parse(str(xml_path)).getroot()
    nombre = (root.findtext("u:nombre", default="Circuito",
              namespaces=NS) or "Circuito").strip()
    alt0_txt = root.findtext("u:coordenada/u:altitud",
                             default="", namespaces=NS)
    alt0 = None
    try:
        if alt0_txt:
            alt0 = float(alt0_txt.strip())
    except ValueError:
        alt0 = None

    perfil = []
    dist_acum = 0.0

    segs = root.findall(".//u:segmentos/u:segmento", namespaces=NS)
    if not segs:
        raise SystemExit("No se han encontrado segmentos.")

    if alt0 is not None:
        perfil.append((0.0, alt0))

    last_alt = alt0 if alt0 is not None else None

    for seg in segs:
        d_txt = seg.findtext("u:distancia", default="0", namespaces=NS) or "0"
        a_txt = seg.findtext("u:altitud",  default="", namespaces=NS) or ""
        try:
            d = float(d_txt.strip())
        except ValueError:
            d = 0.0
        try:
            alt = float(a_txt.strip()) if a_txt != "" else None
        except ValueError:
            alt = None

        if alt is None:
            alt = last_alt if last_alt is not None else 0.0

        dist_acum += d
        perfil.append((dist_acum, alt))
        last_alt = alt

    return nombre, perfil


def generar_altimetria_svg(nombre, perfil, out_path: Path):
    W, H = 1000, 380
    ML, MR, MT, MB = 70, 30, 30, 50
    cw, ch = W - ML - MR, H - MT - MB

    xs = [x for x, _ in perfil]
    ys = [y for _, y in perfil]
    x_min, x_max = 0.0, max(xs)
    y_min, y_max = min(ys), max(ys)
    if abs(y_max - y_min) < 1e-6:
        y_min -= 1
        y_max += 1

    def sx(x): return ML + (x - x_min) / (x_max - x_min) * cw
    def sy(y): return MT + ch - (y - y_min) / (y_max - y_min) * ch
    pts = [(sx(x), sy(y)) for x, y in perfil]

    base_y = sy(y_min)
    poly = [(sx(x_min), base_y)] + pts + [(sx(x_max), base_y)]

    svg = Svg(W, H, bg="#ffffff")

    svg.addText(f"Altimetría — {nombre}", W/2, 18,
                fontSize=16, extra_style="text-anchor: middle;")

    svg.addLine(ML, MT + ch, ML + cw, MT + ch,
                stroke="#333", strokeWidth=1.5)  
    svg.addLine(ML, MT, ML, MT + ch, stroke="#333",
                strokeWidth=1.5)            

    paso_x = 500.0 
    n_x = int(math.ceil(x_max / paso_x))
    for i in range(n_x + 1):
        m = i * paso_x
        x = sx(m)
        svg.addLine(x, MT, x, MT + ch, stroke="#e6e6e6", strokeWidth=1)
        svg.addText(f"{m/1000:.1f}", x, MT + ch + 18, fontSize=11,
                    extra_style="text-anchor: middle; fill: #444;")
    svg.addText("Distancia (km)", ML + cw/2, H - 12,
                fontSize=12, extra_style="text-anchor: middle;")

    rango = y_max - y_min
    if rango <= 6:
        paso_y = 1
    elif rango <= 12:
        paso_y = 2
    elif rango <= 25:
        paso_y = 5
    else:
        paso_y = 10
    y_tick = math.floor(y_min / paso_y) * paso_y
    while y_tick <= y_max + 1e-9:
        y = sy(y_tick)
        svg.addLine(ML, y, ML + cw, y, stroke="#efefef", strokeWidth=1)
        svg.addText(f"{y_tick:.0f}", ML - 8, y + 4, fontSize=11,
                    extra_style="text-anchor: end; fill: #444;")
        y_tick += paso_y
    svg.addText("Altitud (m)", 16, MT + ch/2, fontSize=12,
                extra_style="writing-mode: tb; glyph-orientation-vertical: 0;")

    svg.addPolygon(" ".join(f"{x:.2f},{y:.2f}" for x,
                   y in poly), fill="#cfe8ff", opacity=0.85)
    svg.addPolyline(" ".join(f"{x:.2f},{y:.2f}" for x, y in pts),
                    stroke="#0066ff", strokeWidth=2.5, fill="none")

    svg.addText(f"Longitud: {x_max/1000:.3f} km", W - MR,
                36, fontSize=12, extra_style="text-anchor: end;")
    svg.addText(f"Altitud: {y_min:.0f}–{y_max:.0f} m", W -
                MR, 54, fontSize=12, extra_style="text-anchor: end;")

    svg.escribir(str(out_path))
    return out_path


def main():
    if not XML_PATH.exists():
        raise SystemExit(f"No existe el XML:\n{XML_PATH}")
    nombre, perfil = cargar_perfil(XML_PATH)
    out = generar_altimetria_svg(nombre, perfil, SVG_OUT)
    print(f"SVG creado en:\n{out}")


if __name__ == "__main__":
    main()
