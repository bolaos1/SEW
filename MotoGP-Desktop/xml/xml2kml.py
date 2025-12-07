import xml.etree.ElementTree as ET
from pathlib import Path

XML_PATH = Path("circuito.xml")
KML_OUT  = Path("circuito.kml")


class Kml:
    def __init__(self):
        self.kml = ET.Element('kml', xmlns="http://www.opengis.net/kml/2.2")
        self.doc = ET.SubElement(self.kml, 'Document')

    def add_line(self, name, coords, color="#ff0000ff", width="5"):
        pm = ET.SubElement(self.doc, 'Placemark')
        ET.SubElement(pm, 'name').text = name
        ls = ET.SubElement(pm, 'LineString')
        ET.SubElement(ls, 'tessellate').text = "1"
        ET.SubElement(ls, 'altitudeMode').text = "relativeToGround"
        ET.SubElement(ls, 'coordinates').text = " ".join(coords)
        st = ET.SubElement(pm, 'Style')
        ln = ET.SubElement(st, 'LineStyle')
        ET.SubElement(ln, 'color').text = color
        ET.SubElement(ln, 'width').text = width

    def add_point(self, name, lon, lat, alt="0"):
        pm = ET.SubElement(self.doc, 'Placemark')
        ET.SubElement(pm, 'name').text = name
        pt = ET.SubElement(pm, 'Point')
        ET.SubElement(pt, 'coordinates').text = f"{lon},{lat},{alt}"

    def write(self, path: Path):
        tree = ET.ElementTree(self.kml)
        ET.indent(tree)
        tree.write(str(path), encoding="utf-8", xml_declaration=True)


def main():
    if not XML_PATH.exists():
        raise SystemExit(f"No existe el XML:\n{XML_PATH}")

    root = ET.parse(str(XML_PATH)).getroot()

    lat0 = (root.findtext('coordenada/latitud',  default='') or '').strip()
    lon0 = (root.findtext('coordenada/longitud', default='') or '').strip()
    alt0 = (root.findtext('coordenada/altitud',  default='0') or '0').strip()

    coords_s1, coords_s2, coords_s3 = [], [], []
    for seg in root.findall('.//segmentos/segmento'):
        sec = (seg.get('sector') or '1').strip()
        lon = (seg.findtext('longitud', default='') or '').strip()
        lat = (seg.findtext('latitud',  default='') or '').strip()
        alt = (seg.findtext('altitud',  default='0') or '0').strip()
        if lon and lat:
            coord = f"{lon},{lat},{alt}"
            if sec == '1':
                coords_s1.append(coord)
            elif sec == '2':
                coords_s2.append(coord)
            else:
                coords_s3.append(coord)

    k = Kml()

    if lon0 and lat0:
        k.add_point("Punto origen (salida/meta)", lon0, lat0, alt0)

    if coords_s1:
        k.add_line("Sector 1", coords_s1, color="#ff00ffff")
    if coords_s2:
        k.add_line("Sector 2", coords_s2, color="#ff00ff00")
    if coords_s3:
        k.add_line("Sector 3", coords_s3, color="#ffff0000")

    k.write(KML_OUT)
    print(f"KML creado en:\n{KML_OUT}")


if __name__ == "__main__":
    main()
