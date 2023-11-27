# Twinkly

[![Version](https://img.shields.io/badge/Symcon-PHP--Modul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Product](https://img.shields.io/badge/Symcon%20Version-6.4-blue.svg)](https://www.symcon.de/produkt/)
[![Version](https://img.shields.io/badge/Modul%20Version-3.1.20231127-orange.svg)](https://github.com/Wilkware/IPSymconTwinkly)
[![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
[![Actions](https://github.com/Wilkware/IPSymconTwinkly/workflows/Check%20Style/badge.svg)](https://github.com/Wilkware/IPSymconTwinkly/actions)

Ermöglicht die Kommunikation mit den Smart LED Lichterketten _Twinkly_.

## Inhaltverzeichnis

1. [Funktionsumfang](#user-content-1-funktionsumfang)
2. [Voraussetzungen](#user-content-2-voraussetzungen)
3. [Installation](#user-content-3-installation)
4. [Einrichten der Instanzen in IP-Symcon](#user-content-4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#user-content-5-statusvariablen-und-profile)
6. [WebFront](#user-content-6-webfront)
7. [PHP-Befehlsreferenz](#user-content-7-php-befehlsreferenz)
8. [Versionshistorie](#user-content-8-versionshistorie)

### 1. Funktionsumfang

* Suchen und Erstellen von Twinkly Geräten (Discovery Modul)
* Schalten des LED-Betriebsmodus (+ extended mode)
* Einstellen der Farbe, Helligkeit, Sättigung, Effekt und Film
* Auslesen aller Geräte- und Netwerkinformationen
* Auslesen der Firmware Version
* Änderung des Gerätenamens

### 2. Voraussetzungen

* IP-Symcon ab Version 6.4

### 3. Installation

* Über den Modul Store das Modul _Twinkly_ installieren.
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
IP-Adresse                 | Netzwerkadresse der Lichterkette

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
AKtivieren des erweiterten Modus | Drüber können zusätzliche (_ungetestete_) LED Betriebsmodi freigeschalten werden (Musicreactive, Playlist & Realtime)!

Aktionsbereich:

Aktion            | Beschreibung
----------------- | ------------------------------------------------------------
HELLIGKEIT        | Über die Schaltflächen kann die aktuelle Helligkeit syncronisiert werden (z.B. wenn von App geändert wurde).
SÄTTIGUNG         | Über die Schaltflächen kann die aktuelle Sättigung  syncronisiert werden (z.B. wenn von App geändert wurde).
FARBE             | Über die Schaltflächen kann die aktuelle Frabe syncronisiert werden (z.B. wenn von App geändert wurde).
EFFEKT            | Über die Schaltflächen kann der aktuelle Effekt syncronisiert werden.
FILM(E)           | Über die Schaltflächen kann der aktuelle Film und alle hinterlegten Filme syncronisiert werden.
GERÄTEINFOS       | Über die Schaltflächen können die verschiedensten gerätespezifischen Einstellungen abgerufen werden.
FIRMWARE          | Über die Schaltflächen kann die Version des Gerätes ausgelesen und angezeigt werden.
NETZWERKSTATUS    | Über die Schaltflächen können die hinterlegten Netzwerkeinstellungen angezeigt werden.
Neuer Gerätenamen | Zeigt den aktuellen (nach dem Öffnen des Formulares) Namen an bzw. kann in einen neuen Namen geändert werden.
ÄNDERN            | Setzt den Gerätenamen (Alias) neu.

### 5. Statusvariablen und Profile

Die Statusvariablen werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

Name              | Typ       | Beschreibung
------------------| --------- | ----------------
Schalter          | Integer   | Schalter (An/Aus)
Modus             | Integer   | LED-Betriebsmodus
Farbe             | Integer   | Farbwert
Effekt            | Integer   | Effektauswahl (1..5)
Film              | Integer   | Filmauswahl (abhänig von in der APP hinterlegten Filmen/Effekten)
Brightness        | Integer   | Helligkeitswert (0..100%)
Sättigung         | Integer   | Sättigungswert (0..100%)

Folgendes Profil wird angelegt:

Name                 | Typ       | Beschreibung
-------------------- | --------- | ----------------
Twinkly.Switch       | Integer   | LED-Betriebsmodus (0=Aus, 1=An)
Twinkly.Mode         | Integer   | LED-Betriebsmodus (0=Color, 1=Effekt, 2=Film, 3=Demo)
Twinkly.ModeEx       | Integer   | Extended LED-Betriebsmodus (0=Color, 1=Effekt, 2=Film, 3=Demo, 4=Music Reactive, 5=Playlist, 6=Echtzeit)
Twinkly.Effect       | Integer   | Vordefinierte Effekte (1..5)
Twinkly.Movie        | Integer   | Hinterlegte Filme (-1 für keine; >0 für hinterlegte Filme)

### 6. WebFront

Die pro Twinkly Gerät erzeugten Variablen können direkt ins Webfront verlingt werden.

### 7. PHP-Befehlsreferenz

```php
void TWICKLY_Color(int $InstanzID);
```

Liest den aktuell am Gerät eingestelten Farbwert aus und synchronisert ihn mit der dazugehörigen Statusvariable.  
Die Funktion liefert als Rückgabewert einen String (Farbe: 0xRRGGBB (\<Integer-wert\>)).

__Beispiel__: `TWICKLY_Color(12345);` Ausgabe "Helligkeit: 100%".

```php
void TWICKLY_Effect(int $InstanzID);
```

Liest den aktuell am Gerät hinterlegten Effekt aus und synchronisert ihn mit der dazugehörigen Statusvariable.  
Die Funktion liefert als Rückgabewert einen String (Effekt: x (\<uuid\>)).

__Beispiel__: `TWICKLY_Effect(12345);` Ausgabe "Effekt: 1 (00000000-0000-0000-0000-000000000001)".

```php
void TWICKLY_Movie(int $InstanzID);
```

Liest den aktuell am Gerät hinterlegten Film und und alle hinterlegten Filme aus und synchronisert ihn mit der dazugehörigen Statusvariable.  
Die Funktion liefert als Rückgabewert einen String (Film: x (\<name\>)).

__Beispiel__: `TWICKLY_Movie(12345);` Ausgabe "Film: 1 (Rainbow)".

```php
void TWICKLY_Brightness(int $InstanzID);
```

Liest den aktuell am Gerät hinterlegten Helligkeitswert aus und synchronisert ihn mit der dazugehörigen Statusvariable.  
Die Funktion liefert als Rückgabewert einen String (Helligkeit: xy%).

__Beispiel__: `TWICKLY_Brightness(12345);` Ausgabe "Helligkeit: 100%".

```php
void TWICKLY_Saturation(int $InstanzID);
```

Liest de aktuell am Gerät hinterlegten Sättigungswert aus und synchronisert ihn mit der dazugehörigen Statusvariable.  
Die Funktion liefert als Rückgabewert einen String (Sättigung: xy%).

__Beispiel__: `TWICKLY_Saturation(12345);` Ausgabe "Helligkeit: 100%".

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

v3.1.20231127

* _NEU_: Steuerung der Film(effekte) hinzugefügt
* _NEU_: Synchronisation der Statusvariablen erweitert (dynamisches Film-Profil)
* _NEU_: Twinkly-API erweitert
* _NEU_: Kompatibilität auf IPS 6.4 hoch gesetzt
* _FIX_: Interne Bibliotheken überarbeitet
* _FIX_: PHP Syle Check korrigiert
* _FIX_: Dokumentation überarbeitet

v3.0.20221201

* _NEU_: Trennung von Ein/Aus-Schaltung und Änderung des Betriebsmodus
* _NEU_: Betriebsmodus "Farbe" und "Effekt" konfigurierbar hinzugefügt
* _NEU_: Steuerung der Sättigung hinzugefügt
* _NEU_: Synchronisation der Statusvariablen erweitert (z.B. wenn von App geändert wurde)
* _NEU_: Konfigurationsformular überarbeitet und vereinheitlicht
* _NEU_: Twinkly-API erweitert
* _NEU_: Nutzung erweiterte Betriebsmodi (experimentell) ermöglicht
* _FIX_: Interne Bibliotheken überarbeitet und vereinheitlicht
* _FIX_: Doppelte gefundene Geräte gefixt
* _FIX_: Dokumentation überarbeitet

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

[![GitHub](https://img.shields.io/badge/GitHub-@wilkware-181717.svg?style=for-the-badge&logo=github)](https://wilkware.github.io/)

## Spenden

Die Software ist für die nicht kommerzielle Nutzung kostenlos, über eine Spende bei Gefallen des Moduls würde ich mich freuen.

[![PayPal](https://img.shields.io/badge/PayPal-spenden-00457C.svg?style=for-the-badge&logo=paypal)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8816166)

## Lizenz

Namensnennung - Nicht-kommerziell - Weitergabe unter gleichen Bedingungen 4.0 International

[![Licence](https://img.shields.io/badge/License-CC_BY--NC--SA_4.0-EF9421.svg?style=for-the-badge&logo=creativecommons)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
