% Produkt Import mit CSV
% support@bepado.com
% 15. Mai 2015

# Einleitung

Shopware Connect ist das neue innovative Marktplatz-Projekt der shopware AG, das
Shopbetreibern tolle Möglichkeiten bietet, sich untereinander zu vernetzen und
gemeinsam zu wachsen. Dazu müssen natürlich die technischen Voraussetzungen
gegeben sein. Eine Option ist der Datenaustausch über eine CSV-Anbindung.

# Produkte nach Shopware Connect exportieren

Die CSV-Import/Export-Schnittstelle erlaubt es Ihnen Ihre eigenen Produkte nach
Shopware Connect zu exportieren. Dabei wird das standardisierte
[CSV-Format](https://de.wikipedia.org/wiki/CSV_%28Dateiformat%29) verwendet,
wobei die unterstützten Daten-Felder an das Format des
[Google-Merchant-Upload-Feed](https://support.google.com/merchants/answer/188494)
angelehnt sind. Im Folgenden erfahren Sie, wie Sie Ihren Shop per
CSV-Import/Export mit Shopware Connect verbinden.

## Account konfigurieren

Um den CSV-Import/Export durchzuführen, muss Ihr Shopware Connect-Account zunächst dafür
eingerichtet werden.  Zur Einrichtung loggen Sie sich mit Ihren Zugangsdaten
auf https://bepado.com ein und wählen aus der linken Spalte den Punkt
"Einstellungen" und dann den Reiter "Synchronisation".

![Button: Create FTP Account](docs/graphics/01_create_button.png)

In der rechten Spalte finden Sie nun den Button zur Einrichtung Ihres Accounts
für den CSV-Import/Export.

> Hinweis: Der Account kann nur für den CSV-Import/Export eingerichtet werden,
> wenn vorher kein anderer Shop mit dem Account zur Synchronisation verknüpft
> war.

Auf der folgenden Seite müssen Sie die Einrichtung erneut bestätigen, da dieser
Schritt nicht ohne Eingriff des Support-Teams rückgängig gemacht werden kann.

![Button: Confirm](docs/graphics/02_confirm.png)

> Hinweis: Das FTP-Passwort wird Ihnen nur nach der initialen Einrichtung
> angezeigt, da Shopware Connect kein Passwort im Klartext speichert. Merken Sie sich
> dieses Passwort, da nur der Support es für Sie ändern kann.

Nun löst Shopware Connect im Hintergrund die Einrichtung Ihres FTP-Accounts aus und
verknüpft Ihren Account mit dem CSV-Import/Export-System.  Dieser Vorgang kann
einige Minuten dauern.

![Button: Confirm](docs/graphics/03_credentials.png)

Erst danach können Sie sich per FTP auf dem System einloggen. Verwenden Sie
dazu den angezeigten Server mit Ihrem FTP-Benutzernamen und FTP-Passwort.  Für
einen manuellen Login auf dem FTP-Server verwenden Sie eine spezielle
FTP-Client-Software, beispielsweise [FileZilla](http://filezilla.de/). Der
produktive Import/Export mit Shopware Connect sollte allerdings automatisiert ablaufen,
damit sichergestellt ist, dass Shopware Connect immer über die aktuellsten Daten verfügt.

Auf der Einstellungsseite zur Synchronisation finden Sie auch einen Link zu den
„Importberichten“ (siehe oben). Haben Sie eine Import-Datei auf den FTP-Server
hochgeladen, dauert es eine Weile, bis Shopware Connect diese verarbeitet hat.
Anschließend stellt das System einen Bericht über eventuell aufgetretene Fehler
und Probleme bereit. Dies ist insbesondere zum Einstieg in den
CSV-Import/Export hilfreich.

## FTP-Verzeichnis-Strukturen

In Ihrem FTP-Zugang finden Sie die folgenden Ordner:

*   **products/**

    In diesen Ordner laden Sie eine CSV-Datei mit
    Produkt-Importen für Shopware Connect hoch. Diese muss den
    Namen import.csv tragen und den Anforderungen
    genügen, die weiter unten in diesem Dokument
    beschrieben sind.

    Der Shopware Connect Importer holt diese Datei in regelmäßigen
    Abständen ab und importiert sie ins System.
    Dieser Vorgang kann etwas dauern.

    Bei Aktualisierungen Ihres Produkt-Bestandes
    überschreiben Sie einfach die vorher hochgeladene
    import.csv.

*   **orders/**

    In diesem Verzeichnis werden Bestellungen in Form von
    CSV-Dateien abgelegt, die über Shopware Connect für Ihren Shop
    eingehen. Jede Bestellung wird in einer eigenen Datei mit
    fortlaufender Nummer abgelegt.

## Abholung der CSV-Datei von URL

Alternativ zum FTP kann die CSV Datei auch von einer definierten URL abgerufen
werden. Diese Funktion wird aktuell entweder über REST API oder vom Support für
Sie freigeschaltet. Bitte wenden Sie sich an bepado@shopware.com und
geben Sie die URL und die Shop-ID an, die Sie im Bereich „Synchronisation“
finden.

Die REST API funktioniert wie folgt:

    POST https://sn.connect.shopware.com/sdk/provider/import-url
    X-Shopware-Connect-ShopId: <ShopId>
    X-Shopware-Connect-Key: <hmac-key>

    {
        "url": "<import csv url>"
    }

Die Details zur HMAC-Message Authentifzierung finden Sie in der REST API Dokumentation.

## CSV-Format Produkt import.csv

Die import.csv-Datei folgt weitestgehend der Spezifikation des
Google-Merchant-Upload-Feed (4) .  Die Datei muss UTF-8 kodiert vorliegen.
Andere Kodierungen werden derzeit nicht akzeptiert.  Hinweis: Wir arbeiten
bereits daran, ihre Daten auch in anderen Kodierungen zu akzeptieren und für
die Verwendung innerhalb von Shopware Connect beim Import automatisch umzuwandeln.

Die Felder der CSV-Datei müssen durch ein Tabulator-Zeichen getrennt sein. Zur
Kapselung von Text-Feldern sollten doppelte Anführungszeichen verwendet werden.
Als erste Zeile der CSV-Datei muss ein Header geliefert werden, welcher die
Feld-Identifikatoren wie im Folgenden beschrieben enthält. Sie ordnen damit
jeder CSV-Spalte ein bestimmtes Feld zu. Bitte beachten Sie, dass die
Identifikatoren klein geschrieben sein müssen und keine Leerzeichen enthalten
dürfen.

> Hinweis: Shopware Connect unterstützt derzeit nicht alle Felder des
> Google-Merchant-Upload-Feed. Dies kann sich jedoch in Zukunft ändern. Sollte
> Ihr System einen Standard-Export für diesen Datei-Typ bereitstellen, können
> Sie ruhig alle Felder in der CSV-Datei übergeben. Noch nicht unterstützte
> Felder werden vom Import einfach ignoriert.

Der Shopware Connect-Importer führt bei jeder Aktualisierung einen Komplett-Import durch.
Sie können also ein Produkt aus Shopware Connect löschen, indem Sie die entsprechende
Zeile beim nächsten Update nicht mehr in Ihrer CSV-Datei ausliefern.

Die folgenden CSV-Felder werden derzeit von Shopware Connect unterstützt. Detailinformationen
zu Feldern finden Sie weiter untern.

| Feld          | Beschreibung                                                          |
|--------------:|-----------------------------------------------------------------------|
| id            | Die eindeutige ID-Nummer des Produktes in Ihrem Shop-System.          |
| gtin          | Die EAN des Produktes (optional).                                     |
| item_group_id | Gruppen-Id für Varianten Artikel (optional)                           |
| link          | Link zum Produktes in Ihrem Shop (inkl. “http://” oder “https://”).   |
| title         | Der Titel des Produktes                                               |
| description   | Die Beschreibung des Produktes                                        |
| brand         | Der Name des Herstellers des Produktes                                |
| tax           | Die Mehrwertsteuer auf das Produkt als Fließkommazahl (z.B. 0.19)     |
| price         | Empfohlener Preis des Produktes für Endkunden (inkl. MwSt.)           |
| purchase_price | Einkaufspreis für Handlespartner bei Verkauf des Produktes (Netto).  |
| shipping      | Produktspezifische Versandkosten im Google Format                     |
| availability  | Bestand des Produktes als Ganzzahl, "in stock" oder "out of stock"    |
| image_link    | Link zum Hauptbild des Produktes (incl. "http://" oder "https://").   |
| additional_image_link | Link zu einem weiteren Bild des Produktes.                    |
| google_product_category | Kategorie aus der englischen Google-Taxonomie für Produkte. |
| shipping_weight | Das Gewicht des Produktes in KG, z.B. "10 kg".                      |
| unit_pricing_measure | Enthaltene Einheiten in diesem Produkt, z.B. "1 kg" oder "10l".|
| unit_pricing_base_measure | Basis-Einheiten in diesem Produkt, z.B. "1 kg" oder "10l".|
| connect\_[name] | Bepado Marktplatz Attribute, siehe Marktplatzattribute Kapitel.       |
| variant\_[name] | Varianten Attribute, siehe Varianten Kapitel weiter unten.            |
| tags          | Product tags, see Chapter tags below                                |

Details zu Markplatz-Attributen, Varianten und Übersetzungen finden Sie weiter unten.

### Details zu Feldern

#### tax

An dieser Stelle weicht Shopware Connect vom Standard des Google-Merchant-Upload-Feed ab.
Dieser schreibt vor, dass nur in den USA das Feld “tax” zu verwenden ist.
Shopware Connect benötigt diese Information aber zwingend.

#### Preise

Alle Preise müssen mit Punkt (.) als Trennzeichen zwischen Euro und Centbetrag exportiert werden.
An einer Erkennung von Komma (,) wird aktuell noch gearbeitet.

#### purchase_price

Wenn nicht gesetzt wird Einkaufspreis=Endkundenpreis gesetzt.

#### shipping

Wenn Sie die Versandkosten auf Produktebene (nicht auf globale Ebene) definieren möchten können
Sie hier die Versandkosten für jedes Land im Google-Format angeben. Beispiele:

- DE::Post:5.99 EUR
- DE::DHL:4.95 EUR,AT::DHL:10.00 EUR
- DE:53*:Standard [3D]:4.95 EUR

Die Versandregeln folgen dieser Definition:

* Es können mehrere Versandkostenregeln definiert werden. Diese werden per Komma separiert.
* Eine Versandkostenregel enthält vier Informationen, die per Doppelpunkt getrennt werden:
** Zielland
** Postleitzahlbereich (optional)
** Name
** Preis
* Der Name kann optional auch die Versanddauer enthalten. Diese wird in eckigen Klammern definiert, z.B. [3D] für 3 Tage
* Der Preis muss mit Punkt getrennt sein.

Zielland, Name und Preis sind Pflichtangaben.

#### Bilder

Die Links zu Bildern müssen immer mit http:// oder https:// beginnen und
müssen von Shopware Connect direkt von Ihrem Server abrufbar sein.

Das Feld "additional_image_link" ist optional. Bitte verwenden Sie es nur für
andere Bilder als das Hauptbild und geben Sie nicht zweimal das gleiche Bild
an.

#### Einheiten

Als Einheit muss eine der von Shopware Connect unterstützten Einheiten verwendet werden:

* `b` (Byte)
* `kb` (Kilobyte)
* `mb` (Megabyte)
* `gb` (Gigabyte)
* `tb` (Terabyte)
* `g` (Gramm)
* `kg` (Kilogramm)
* `mg` (Milligramm)
* `oz` (Unze)
* `lb` (Pfund)
* `t` (Tonne)
* `l` (Liter)
* `ft^3` (Kubikfuß)
* `in^3` (Kubikzoll)
* `m^3` (Kubikmeter)
* `yd^3` (Kubikyard)
* `fl oz` (Flüssigunze)
* `gal` (Gallonen)
* `ml` (Milliliter)
* `qt` (Quart)
* `m` (Meter)
* `cm` (Zentimeter)
* `ft` (Fuß)
* `in` (Zoll)
* `km` (Kilometer)
* `mm` (Millimeter)
* `yd` (Yard)
* `piece` (Stück)
* `bottle` (Flasche)
* `crate` (Kiste)
* `can` (Dose)
* `capsule` (Kapsel)
* `box` (Karton(s))
* `glass` (Glas)
* `kit` (Kit(s))
* `pack` (Packung(en))
* `package` (Paket(e))
* `pair` (Paar)
* `roll` (Rolle)
* `set` (Set(s))
* `sheet` (Blatt)
* `ticket` (Ticket(s))
* `unit` (VKE)
* `second` (Sekunde)
* `day` (Tag)
* `hour` (Stunde)
* `minute` (Minute)
* `month` (Monat(e))
* `night` (Nacht)
* `week` (Woche)
* `year` (Jahr(e))
* `m^2` (Quadratmeter)
* `cm^2` (Quadratzentimeter)
* `ft^2` (Quadratfuß)
* `in^2` (Quadratzoll)
* `mm^2` (Quadratmillimeter)
* `yd^2` (Quadratyard)

#### Marktplatz Attribute

Jeder Shopware Connect Marktplatz kann eine Liste von zusätzlichen Attributen definieren,
die von den Shops übertragen werden können. Welche Attribute in Ihrem Marktplatz
existieren müssen Sie von Ihrem Marktplatz Administrator erfragen.

Bepado Attribute können einfach per CSV übertragen werden, indem ihr Name mit `connect_`
vorangestellt wird. Existiert beispielsweise zwei Marktplatz Attribute "cpu" und "ram" für die
Leistungsfähigkeit eines Elektronikgerätes können Sie diesen folgendermaßen übertragen:

| connect_cpu | connect_ram |
|-------------|-------------|
| 1.2Ghz      | 2GB         |

#### Varianten

Varianten können übertragen werden in dem das Feld `item_group_id` für ein Produkt mit
einem gemeinsem Identifier gesetzt wird den alle Varianten gemeinsam haben.

Es gibt einige aus dem Google Product Feed vordefinierte Variantenattribute:

- `color` - Farbe des Produktes
- `size` - Größe des Produktes
- `material` - Material
- `pattern`

Weitere Attribute können übertragen werden in dem neue Felder mit dem Prefix
`variant_` in der CSV Datei übertragen werden, z.B. Speichermenge für smartphones:

| title               | variant_memory | color   |
|---------------------|----------------|---------|
| Iphone Weiss/4GB    | 4GB            | Weiss   |
| Iphone Schwarz/16GB | 16GB           | Schwarz |

Es können beliebige Attribute übertragen werden.

#### Tags

Tags können mit dem Feld `tags` übertragen werden und werden dafür verwendet
Produkte in verschiedene Streams aufzuteilen. Jedes Produkt kann bis zu 10
Tags verwenden, jedes Tag kann 64 Zeichen lang sein. Tags werden mit Komma (,) separiert.
Beispiele:

- Sports,Men,Shorts
- dress,fashion,vogue

#### Übersetzungen

Sie können einige Produktfelder mit Übersetzungen in weitere Sprachen an Shopware Connect übertragen.

Dafür müssen Sie einfach das Kürzel der Sprache an den Feldtitel anhängen, beispielsweise:

| title               | title_en          |
|---------------------|-------------------|
| Iphone Weiss/4GB    | Iphone White/4GB  |
| Iphone Schwarz/16GB | Iphone Black/16GB |

Folgende Felder sind aktuell übersetzbar:

- title
- description

Sowie die hartkodierten Varianten Attribute:

- `size` mit `size_en`
- `color` mit `color_en`
- `material` mit `material_en`
- `pattern` mit `pattern_en`

Und dynamische Varianten-Attribute:

- `variant_memory` mit `transvariant_en_memory`

# Bestellungen von Shopware Connect importieren

Shopware Connect bietet zwei Möglichkeiten Bestellungen von Ihren Handelspartnern zu importieren:

1. CSV per FTP herunterladen.
2. Webhook (XML über HTTP)

## CSV per FTP herunterladen

Bei diesem Prozess laden Sie aus dem Ordner **orders/** alle CSV-Dateien regelmässig herunter.
Sie können dann die Dateien mit bereits importierten Dateien abgleichen und neue Dateien als
neue Bestellungen behandeln.

Jede Bestellung wird in einer eigenen Datei gespeichert. Die Dateien werden
laufend durchnummeriert. Zum Beispiel:

* order_00000001.csv
* order_00000002.csv
* ...

Die erste Zeile des CSV ist der Header, der beschreibt, welche Felder in der
Datei enthalten sind (bitte beachten Sie, dass sich die Reihenfolge der Felder
im Laufe der Zeit ändern kann und evtl. neue Felder hinzu kommen).

Die Felder der Order-Dateien sind mit einem Semikolon getrennt. Text-Felder
können in doppelte Anführungszeichen eingeschlossen sein. Jede Zeile enthält
alle Felder, also auch die Kundendaten, die aber in jeder Zeile gleich sind.

## Webhook (XML über HTTP)

Die [REST-API Dokumentation](https://github.com/ShopwareAG/bepado-sdk/blob/master/docs/rest_api.pdf)
enthält Informationen über "Event-Hooks". Um Bestellungen über REST anzunehmen
müssen Sie den ``order_created`` Event anbinden.
