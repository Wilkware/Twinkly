# Twinkly (Smarte LED Lichterketten)

[![Version](https://img.shields.io/badge/Symcon-PHP--Modul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Product](https://img.shields.io/badge/Symcon%20Version-5.2-blue.svg)](https://www.symcon.de/produkt/)
[![Version](https://img.shields.io/badge/Modul%20Version-2.1.20210801-orange.svg)](https://github.com/Wilkware/IPSymconTwinkly)
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
* Einstellen der Helligkeit
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

__Konfigurationsseite__:

Einstellungsbereich:

> Gerät ...

Name                       | Beschreibung
-------------------------- | ---------------------------------
Geräte IP                  | IP-Adresse der Lichterkette

> Zeitschaltung ...

Name                       | Beschreibung
-------------------------- | ---------------------------------
Aus/Ein                    | Zeitschaltung/Timer aktivieren bzw. deaktivieren.
Einschalten                | Tägliche Einschaltzeit.
Ausschalten                | Tägliche Ausschaltzeit.
JETZT                      | Trägt die aktuelle Uhrzeit als Start- oder Endezeit ein

> Erweiterte Einstellungen ...

Name                       | Beschreibung
-------------------------- | ---------------------------------
Zusätzlicher Lichtschalter | Zusätzlicher Schalter für einfaches An/Aus

Aktionsbereich:

Aktion            | Beschreibung
----------------- | ------------------------------------------------------------
HELLIGKEIT        | Über die Schaltflächen kann die aktuelle Helligkeit syncronisiert werden (z.B. wenn von App geändert wurde).
GERÄTEINFOS       | Über die Schaltflächen können die verschiedensten gerätespezifischen Einstellungen abgerufen werden.
FIRMWARE          | Über die Schaltflächen kann die Version des Gerätes ausgelesen und angezeigt werden.
NETZWERKSTATUS    | Über die Schaltflächen können die hinterlegten Netzwerkeinstellungen angezeigt werden.
Neuer Gerätenamen | Zeigt den aktuellen (nach dem Öffnen des Formulares) Namen an bzw. kann in einen neuen Namen geändert werden.
ÄNDERN            | Setzt den Gerätenamen (Alias) neu.

### 5. Statusvariablen und Profile

Die Statusvariablen werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

Name              | Typ       | Beschreibung
------------------| --------- | ----------------
Modus             | Integer   | LED-Betriebsmodus
Brightness        | Integer   | Helligkeitswert
(Switch)          | Integer   | Schalter (An/Aus)

Folgendes Profil wird angelegt:

Name                 | Typ       | Beschreibung
-------------------- | --------- | ----------------
Twinkly.Mode         | Integer   | LED-Betriebsmodus (0=Aus, 1=An, 2=Demo, 3=Echtzeit)
Twinkly.Switch       | Integer   | LED-Betriebsmodus (0=Aus, 1=An)

> Aus(off) - schaltet Licht aus  
> An(movie) - spielt vordefinierten oder hochgeladenen Effekt ab  
> Demo(demo) - startet eine vordefinierte Sequenz von Effekten, die nach wenigen Sekunden geändert werden  
> Echtzeit(rt) - Effekt in Echtzeit erhalten  

### 6. WebFront

Die pro Twinkly Gerät erzeugten Variablen _Modus_, _Helligkeit_ und _Schalter_ können direkt ins Webfront verlingt werden.

### 7. PHP-Befehlsreferenz

```php
void TWICKLY_Brightness(int $InstanzID);
```

Liest die aktuell am Gerät hinterlegten Helligkeitswert aus.  
Die Funktion liefert als Rückgabewert einen String (Helligkeit: xy%).

__Beispiel__: `TWICKLY_Brightness(12345);` Ausgabe "Helligkeit: 100%".

```php
void TWICKLY_Gestalt(int $InstanzID);
```

Liest alle Geräteinformationen aus.  
Die Funktion liefert als Rückgabewert einen (sprintf) formatierten String.

__Beispiel__: `TWICKLY_Gestalt(12345);`

> Product name: Twinkly  
> Hardware Version: 100  
> Bytes per LED: 3  
> Hardware ID: ab01cd  
> Flash Size: 64  
> LED Type: 14  
> Product Code: TWS250STP-B  
> Firmware Family: F  
> Device Name: Weihnachtsbaum  
> Uptime: 239317051  
> MAC: 12:34:56:ba:dc:fe  
> UUID: 12345678-bacd-1234-abcd-1234567890ab  
> Max supported LED: 1020  
> Number of LED: 250  
> LED Profile: RGB  
> Frame Rate: 23,810000  
> Movie Capacity: 992  
> Copyright: LEDWORKS 2018  

```php
void TWICKLY_Version(int $InstanzID);
```

Liest die installierte Firmwareversion des Gerätes aus.  
Die Funktion liefert als Rückgabewert einen String (Firmware: x.yy.zz).

__Beispiel__: `TWICKLY_Version(12345);` Ausgabe "Firmware: 2.4.16".

```php
void TWICKLY_Network(int $InstanzID);
```

Liest die eingestellten Netzwerkinformationen aus.
Die Funktion liefert als Rückgabewert einen (sprintf) formatierten String.

__Beispiel__: `TWICKLY_Network(12345);`

> Network mode: 1 (Station)  
> Station:  
> SSID: ssid  
> IP: 192.168.1.9  
> Gateway: 192.168.1.1  
> Mask: 255.255.255.0  
> RSSI: -54 db  
> Access Point:  
> SSID: Twinkly_DE99EF  
> IP: 192.168.4.1  
> Channel: 1  
> Encryption: NONE  
> SSID Hidden: false  
> Max connections: 4  

```php
void TWICKLY_DeviceName(int $InstanzID, string $NewName);
```

Setzt den Gerätenamen (Alias) neu.  
Die Funktion liefert als Rückgabewert einen String (Erfolgsmeldung).

__Beispiel__: `TWICKLY_DeviceName(12345, 'Lichterkette');` Ausgabe "Der Name wurde erfolgreich geändert!".

### 8. Versionshistorie

v2.1.20210801

* _NEU_: Konfigurationsformular überarbeitet und vereinheitlicht
* _NEU_: Zeitschaltung (Timer) hinzugefügt
* _NEU_: Abruf der Netzwerkinformation hinzugefügt
* _NEU_: Änderung des Gerätenamens (Alias) hinzugefügt
* _NEU_: Twinkly-API erweitert
* _FIX_: Übersetzungen nachgezogen
* _FIX_: Interne Bibliotheken überarbeitet und vereinheitlicht
* _FIX_: Debug Meldungen überarbeitet
* _FIX_: Dokumentation überarbeitet

v2.0.20201016

* _NEU_: Steuerung bzw. Einstellung der Helligkeit
* _NEU_: Vereinfachte Modusschaltung als normaler Lichtschalter
* _NEU_: Fehlerbehandlung wenn keine Verbindung zum Gerät hergestellt werden kann
* _FIX_: Bugfix Discovery Modul
* _FIX_: Debugausgabe überarbeitet
* _FIX_: API erweitert und überarbeitet

v1.0.20200501

* _NEU_: Initialversion

v1.1.20200510

* _FIX_: Bugfix Discovery Modul

v1.0.20200501

* _NEU_: Initialversion

## Entwickler

Seit nunmehr über 10 Jahren fasziniert mich das Thema Haussteuerung. In den letzten Jahren betätige ich mich auch intensiv in der IP-Symcon Community und steuere dort verschiedenste Skript und Module bei. Ihr findet mich dort unter dem Namen @pitti ;-)

[![GitHub](https://img.shields.io/badge/GitHub-@wilkware-blueviolet.svg?logo=github)](https://wilkware.github.io/)

## Spenden

Die Software ist für die nicht kommzerielle Nutzung kostenlos, über eine Spende bei Gefallen des Moduls würde ich mich freuen.

[![PayPal](https://img.shields.io/badge/PayPal-spenden-blue.svg?logo=paypal)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8816166)

## Lizenz

[![Licence](https://licensebuttons.net/i/l/by-nc-sa/transparent/00/00/00/88x31-e.png)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
