# Twinkly (Smarte LED Lichterketten)

[![Version](https://img.shields.io/badge/Symcon-PHP--Modul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Product](https://img.shields.io/badge/Symcon%20Version-5.2%20%3E-blue.svg)](https://www.symcon.de/produkt/)
[![Version](https://img.shields.io/badge/Modul%20Version-1.0.20200501-orange.svg)](https://github.com/Wilkware/IPSymconTwinkly)
[![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
[![Actions](https://github.com/Wilkware/IPSymconTwinkly/workflows/Check%20Style/badge.svg)](https://github.com/Wilkware/IPSymconTwinkly/actions)

Ermöglicht die Kommunikation mit den Smart LED Lichterketten *Twinkly*.

## Inhaltverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Installation](#3-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)
8. [Versionshistorie](#8-versionshistorie)

### 1. Funktionsumfang

* Suchen und Erstellen von Twinkly Geräten (Discovery Modul)
* Schalten des LED-Betriebsmodus
* Auslesen aller Geräteinformationen
* Auslesen der Firmware Version

### 2. Voraussetzungen

* IP-Symcon ab Version 5.2

### 3. Installation

* Über den Modul Store das Modul *Twinkly* installieren.
* Alternativ Über das Modul-Control folgende URL hinzufügen.  
`https://github.com/Wilkware/IPSymconTwinkly` oder `git://github.com/Wilkware/IPSymconTwinkly.git`

### 4. Einrichten der Instanzen in IP-Symcon

#### Twinkly Discovery

Die Gerätesuche ist über die Glocke oben rechts in der Konsole aufrufbar. Dort über "SYSTEM AUSWÄHLEN" kann das  
'_Twinkly Discovery_'-Modul ausgewählt und installiert werden.

#### Twinkly Device

Unter "Instanz hinzufügen" ist das 'Twinkly Device'-Modul (Alias: _Smart LED Lichterkette_) unter dem Hersteller 'Ledworks' aufgeführt.

_Konfigurationsseite_:

Wird das Gerät direkt über die Gerätesuche(Discovery) erstellt, sind keine weiteren Konfigurationen notwendig. Die IP-Adresse des Gerätes wird automatisch hinterlegt.
Werden Twinkly Geräte manuell angelegt, ist die entsprechende IP-Adresse einzutragen.

Name               | Beschreibung
------------------ | ---------------------------------
Geräte IP          | IP-Adresse der Lichterkette

Über die Schaltflächen "FIRMWARE" kann die Version des Gerätes ausgelesen und angezeigt werden.
Gleiches gilt für die Gerätedaten über die Schaltfläche "GERÄTEINFOS".

### 5. Statusvariablen und Profile

Die Statusvariablen werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

Name              | Typ       | Beschreibung
------------------| --------- | ----------------
Modus             | Integer   | LED-Betriebsmodus

Folgendes Profil wird angelegt:

Name                 | Typ       | Beschreibung
-------------------- | --------- | ----------------
Twinkly.Mode         | Integer   | LED-Betriebsmodus (0=Aus, 1=An, 2=Demo, 3=Echtzeit)

> Aus(off) - schaltet Licht aus  
> An(movie) - spielt vordefinierten oder hochgeladenen Effekt ab  
> Demo(demo) - startet eine vordefinierte Sequenz von Effekten, die nach wenigen Sekunden geändert werden  
> Echtzeit(rt) - Effekt in Echtzeit erhalten  

### 6. WebFront

Die pro Twinkly Gerät erzeugte _Modus_-Variable kann direkt ins Webfront verlingt werden.

### 7. PHP-Befehlsreferenz

### 8. Versionshistorie

v1.0.20200501

* _NEU_: Initialversion

## Entwickler

* Heiko Wilknitz ([@wilkware](https://github.com/wilkware))

## Spenden

Die Software ist für die nicht kommzerielle Nutzung kostenlos, Schenkungen als Unterstützung für den Entwickler bitte hier:  

[![License](https://img.shields.io/badge/Einfach%20spenden%20mit-PayPal-blue.svg)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8816166)

## Lizenz

[![Licence](https://licensebuttons.net/i/l/by-nc-sa/transparent/00/00/00/88x31-e.png)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
