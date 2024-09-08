# Twinkly Discovery

[![Version](https://img.shields.io/badge/Symcon-PHP--Modul-red.svg?style=flat-square)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Product](https://img.shields.io/badge/Symcon%20Version-6.4-blue.svg?style=flat-square)](https://www.symcon.de/produkt/)
[![Version](https://img.shields.io/badge/Modul%20Version-3.2.20240222-orange.svg?style=flat-square)](https://github.com/Wilkware/Twinkly)
[![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg?style=flat-square)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
[![Actions](https://img.shields.io/github/actions/workflow/status/wilkware/Twinkly/style.yml?branch=main&label=CheckStyle&style=flat-square)](https://github.com/Wilkware/Twinkly/actions)

Ermöglicht die Gerätesuche für alle im Netzwerk befindlichen _Twinkly_ Geräte.

## Inhaltverzeichnis

1. [Funktionsumfang](#user-content-1-funktionsumfang)
2. [Voraussetzungen](#user-content-2-voraussetzungen)
3. [Installation](#user-content-3-installation)
4. [Einrichten der Instanzen in IP-Symcon](#user-content-4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#user-content-5-statusvariablen-und-profile)
6. [Visualisierung](#user-content-6-visualisierung)
7. [PHP-Befehlsreferenz](#user-content-7-php-befehlsreferenz)
8. [Versionshistorie](#user-content-8-versionshistorie)

### 1. Funktionsumfang

* Suchen und Erstellen von Twinkly Geräten

### 2. Voraussetzungen

* IP-Symcon ab Version 6.4

### 3. Installation

* Über den Modul Store die Bibliothek _Twinkly_ installieren.
* Alternativ Über das Modul-Control folgende URL hinzufügen.  
`https://github.com/Wilkware/Twinkly` oder `git://github.com/Wilkware/Twinkly.git`

### 4. Einrichten der Instanzen in IP-Symcon

* Die Gerätesuche ist über die Glocke oben rechts in der Konsole aufrufbar. Dort über "SYSTEM AUSWÄHLEN" kann das  
'_Twinkly Discovery_'-Modul ausgewählt und installiert werden.

* Alternativ unter 'Instanz hinzufügen' kann das _'Twinkly Discovery'_-Modul mithilfe des Schnellfilters gefunden werden.  
Weitere Informationen zum Hinzufügen von Instanzen in der [Dokumentation der Instanzen](https://www.symcon.de/service/dokumentation/konzepte/instanzen/#Instanz_hinzufügen).

__Konfigurationsseite__:

Innerhalb der Geräteliste werden alle im Netzwerk verfügbaren Geräte aufgeführt.
Man kann pro Gerät eine Instanzen anlegen und auch wieder löschen.
Legt man eine entsprechende Zielkategorie fest, werden neu zu erstellende Instanzen unterhalb dieser Kategorie angelegt.

_Einstellungsbereich:_

Name                          | Beschreibung
----------------------------- | ---------------------------------
Erstelle neue Instanzen unter | Kategorie unter welcher neue Instanzen erzeugt werden (keine Auswahl im Root)

_Aktionsbereich:_

Name                    | Beschreibung
----------------------- | ---------------------------------
Geräte                  | Liste zum Verwalten der entsprechenden Geräte-Instanzen

### 5. Statusvariablen und Profile

Es werden keine zusätzlichen Statusvariablen oder Profile benötigt.

### 6. Visualisierung

Es ist keine weitere Steuerung oder gesonderte Darstellung integriert.

### 7. PHP-Befehlsreferenz

Das Modul bietet keine direkten Funktionsaufrufe.

### 8. Versionshistorie

v3.2.20240222

* _NEU_: Anpassungen für IPS 7.x
* _FIX_: Übersetzungen nachgezogen
* _FIX_: Interne Bibliotheken überarbeitet

v3.1.20231127

* _NEU_: Kompatibilität auf IPS 6.4 hoch gesetzt
* _FIX_: Interne Bibliotheken überarbeitet
* _FIX_: PHP Syle Check korrigiert
* _FIX_: Dokumentation überarbeitet

v3.0.20221201

* _FIX_: Doppelte gefundene Geräte gefixt
* _FIX_: Interne Bibliotheken überarbeitet und vereinheitlicht
* _FIX_: Dokumentation überarbeitet

v2.0.20201016

* _FIX_: Bugfix Discovery Modul
* _FIX_: Debugausgabe überarbeitet

v1.1.20200510

* _FIX_: Bugfix Discovery Modul

v1.0.20200501

* _NEU_: Initialversion

## Entwickler

Seit nunmehr über 10 Jahren fasziniert mich das Thema Haussteuerung. In den letzten Jahren betätige ich mich auch intensiv in der IP-Symcon Community und steuere dort verschiedenste Skript und Module bei. Ihr findet mich dort unter dem Namen @pitti ;-)

[![GitHub](https://img.shields.io/badge/GitHub-@wilkware-181717.svg?style=for-the-badge&logo=github)](https://wilkware.github.io/)

## Spenden

Die Software ist für die nicht kommerzielle Nutzung kostenlos, über eine Spende bei Gefallen des Moduls würde ich mich freuen.

[![PayPal](https://img.shields.io/badge/PayPal-spenden-00457C.svg?style=for-the-badge&logo=paypal)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8816166)

## Lizenz

Namensnennung - Nicht-kommerziell - Weitergabe unter gleichen Bedingungen 4.0 International

[![Licence](https://img.shields.io/badge/License-CC_BY--NC--SA_4.0-EF9421.svg?style=for-the-badge&logo=creativecommons)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
