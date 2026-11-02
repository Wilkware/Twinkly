# 🎄 Twinkly Device

[![Home](https://img.shields.io/badge/Home-wilkware.de-0b1830.svg?style=flat-square)](https://wilkware.de/module/twinkly/device/)
[![Version](https://img.shields.io/badge/Symcon-PHP--Modul-red.svg?style=flat-square)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Product](https://img.shields.io/badge/Symcon%20Version-8.1-blue.svg?style=flat-square)](https://www.symcon.de/produkt/)
[![Version](https://img.shields.io/badge/Modul%20Version-4.0.20261102-orange.svg?style=flat-square)](https://github.com/Wilkware/Twinkly)
[![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg?style=flat-square)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
[![Actions](https://img.shields.io/github/actions/workflow/status/wilkware/Twinkly/ci.yml?branch=main&label=CI&style=flat-square)](https://github.com/Wilkware/Twinkly/actions)


Ermöglicht die Kommunikation mit den Smart LED-Leuchten _Twinkly_.

## Inhaltverzeichnis

1. [Funktionsumfang](#user-content-1-funktionsumfang)
2. [Voraussetzungen](#user-content-2-voraussetzungen)
3. [Installation](#user-content-3-installation)
4. [Einrichtung](#user-content-4-einrichtung)
5. [Statusvariablen](#user-content-5-statusvariablen)
6. [Darstellungen](#user-content-6-darstellungen)
7. [Visualisierung](#user-content-7-visualisierung)
8. [Befehlsreferenz](#user-content-8-befehlsreferenz)
9. [Versionshistorie](#user-content-9-versionshistorie)

### 1. Funktionsumfang

* Schalten des LED-Betriebsmodus (+ extended mode)
* Einstellen der Farbe, Helligkeit, Sättigung, Effekt und Film
* Auslesen aller Geräte- und Netwerkinformationen
* Auslesen der Firmware Version
* Änderung des Gerätenamens

### 2. Voraussetzungen

* Symcon ab Version 8.1

### 3. Installation

* Über den Modul Store die Bibliothek _Twinkly_ installieren.
* Alternativ über das Modul Control folgende URL hinzufügen.  
`https://github.com/Wilkware/Twinkly` oder `git://github.com/Wilkware/Twinkly.git`

### 4. Einrichtung

* Unter 'Instanz hinzufügen' ist das _Twinkly Gerät_-Modul (Alias: _Twinkly Lichterkette_, _Smarte LED Lichterkette_) unter dem Hersteller 'Ledworks' aufgeführt.

__Konfigurationsseite__:

Einstellungsbereich:

> 💡 Gerät ...

Name                       | Beschreibung
-------------------------- | ---------------------------------
IP-Adresse                 | Netzwerkadresse der Lichterkette
Aktualisierungsintervall   | Intervall in Sekunden, in dem die Statusvariablen mit dem Gerät abgeglichen werden (0 = aus, Standard). Empfehlung: während der Saison z.B. 60 Sekunden einstellen. Ist das Gerät nicht erreichbar, zeigt die Instanz den Status „Gerät nicht erreichbar!“.
Abgleich pausieren nach    | Ist das Gerät so viele Tage nicht erreichbar (z.B. außerhalb der Saison), wird nur noch stündlich geprüft und die Instanz zeigt „Abgleich pausiert“ (Standby). Sobald das Gerät wieder antwortet, ein Befehl erfolgreich war oder die Konfiguration übernommen wird, gilt wieder das normale Intervall (0 = nie pausieren, Standard 3).

> ⏰ Zeitschaltung ...

Name                       | Beschreibung
-------------------------- | ---------------------------------
Aus/Ein                    | Zeitschaltung/Timer aktivieren bzw. deaktivieren.
Einschalten                | Tägliche Einschaltzeit.
Ausschalten                | Tägliche Ausschaltzeit.
JETZT                      | Trägt die aktuelle Uhrzeit als Start- oder Endezeit ein

> ⚙️ Erweiterte Einstellungen ...

Name                       | Beschreibung
-------------------------- | ---------------------------------
Aktivieren des erweiterten Modus | Darüber können zusätzliche (_ungetestete_) LED Betriebsmodi freigeschalten werden (Musik Reaktiv, Wiedergabeliste & Echtzeit)!

Aktionsbereich:

> 🛠️ Gerätefunktionen ...

Aktion            | Beschreibung
----------------- | ------------------------------------------------------------
HELLIGKEIT        | Über die Schaltflächen kann die aktuelle Helligkeit synchronisiert werden (z.B. wenn von App geändert wurde).
SÄTTIGUNG         | Über die Schaltflächen kann die aktuelle Sättigung synchronisiert werden (z.B. wenn von App geändert wurde).
FARBE             | Über die Schaltflächen kann die aktuelle Farbe synchronisiert werden (z.B. wenn von App geändert wurde).
EFFEKT            | Über die Schaltflächen kann der aktuelle Effekt synchronisiert werden.
FILM(E)           | Über die Schaltflächen kann der aktuelle Film und alle hinterlegten Filme synchronisiert werden.
GERÄTEINFOS       | Über die Schaltflächen können die verschiedensten gerätespezifischen Einstellungen abgerufen werden.
FIRMWARE          | Über die Schaltflächen kann die Version des Gerätes ausgelesen und angezeigt werden.
NETZWERKSTATUS    | Über die Schaltflächen können die hinterlegten Netzwerkeinstellungen angezeigt werden.
Neuer Gerätenamen | Zeigt den aktuellen (nach dem Öffnen des Formulares) Namen an bzw. kann in einen neuen Namen geändert werden.
ÄNDERN            | Setzt den Gerätenamen (Alias) neu.

### 5. Statusvariablen

Die Statusvariablen werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.
Werte werden nur übernommen, wenn das Gerät den Befehl bestätigt hat; Änderungen über die App oder den Geräte-Timer werden im Aktualisierungsintervall übernommen.

Ident      | Name       | Typ     | Beschreibung
---------- | ---------- | ------- | ------------------------------
Switch     | Schalter   | Boolean | Ein- bzw. Ausschalten der Lichterkette
Mode       | Modus      | Integer | LED-Betriebsmodus
Color      | Farbe      | Integer | Farbwert
Effect     | Effekt     | Integer | Auswahl des vordefinierten Effekts
Movie      | Film       | String  | Filmauswahl über die eindeutige Film-ID (abhängig von den in der App hinterlegten Filmen/Effekten)
Brightness | Helligkeit | Integer | Helligkeitswert
Saturation | Sättigung  | Integer | Sättigungswert

### 6. Darstellungen

Die Darstellungen werden direkt an den Statusvariablen hinterlegt, es werden keine Profile angelegt.

Variable             | Darstellung   | Werte
-------------------- | ------------- | ------------------------------
Schalter             | Schalter      | An / Aus
Modus                | Aufzählung    | Farbe, Effekt, Film, Demo (erweiterter Modus zusätzlich: Musik Reaktiv, Wiedergabeliste, Echtzeit)
Farbe                | Farbe         | Farbauswahl mit 6 Farbvorgaben
Effekt               | Aufzählung    | Effekt 1 – n (Anzahl wird vom Gerät gelesen, Standard 5)
Film                 | Aufzählung    | Am Gerät hinterlegte Filme (gleichnamige Filme mit angehängter Geräte-ID, z.B. "Rainbow (1)"; ohne Filme: Keine Filme)
Helligkeit           | Schieberegler | 0 – 100 % (Schrittweite 1)
Sättigung            | Schieberegler | 0 – 100 % (Schrittweite 1)

### 7. Visualisierung

Die Instanz kann direkt in der Kachel-Visualisierung verwendet werden, die Statusvariablen werden über ihre Darstellungen bedient. Alternativ können die Variablen einzeln verlinkt werden.

### 8. Befehlsreferenz

```php
string TWINKLY_Color(int $InstanzID);
```

Liest den aktuell am Gerät eingestelten Farbwert aus und synchronisert ihn mit der dazugehörigen Statusvariable.  
Die Funktion liefert als Rückgabewert einen String (Farbe: #RRGGBB (\<Integer-Wert\>)).

__Beispiel__: `TWINKLY_Color(12345);` Ausgabe "Farbe: #FFA500 (16753920)".

```php
string TWINKLY_Effect(int $InstanzID);
```

Liest den aktuell am Gerät hinterlegten Effekt aus und synchronisert ihn mit der dazugehörigen Statusvariable.  
Die Funktion liefert als Rückgabewert einen String (Aktueller Effekt: x).

__Beispiel__: `TWINKLY_Effect(12345);` Ausgabe "Aktueller Effekt: 1".

```php
string TWINKLY_Movie(int $InstanzID);
```

Liest den aktuell am Gerät hinterlegten Film und und alle hinterlegten Filme aus und synchronisert ihn mit der dazugehörigen Statusvariable.  
Die Funktion liefert als Rückgabewert einen String mit dem aktuellen Film und der Anzahl der verfügbaren Filme.

__Beispiel__: `TWINKLY_Movie(12345);` Ausgabe "Aktueller Film: Rainbow" und "Verfügbare Filme: 1".

```php
string TWINKLY_Brightness(int $InstanzID);
```

Liest den aktuell am Gerät hinterlegten Helligkeitswert aus und synchronisert ihn mit der dazugehörigen Statusvariable.  
Die Funktion liefert als Rückgabewert einen String (Helligkeit: xy%).

__Beispiel__: `TWINKLY_Brightness(12345);` Ausgabe "Helligkeit: 100%".

```php
string TWINKLY_Saturation(int $InstanzID);
```

Liest de aktuell am Gerät hinterlegten Sättigungswert aus und synchronisert ihn mit der dazugehörigen Statusvariable.  
Die Funktion liefert als Rückgabewert einen String (Sättigung: xy%).

__Beispiel__: `TWINKLY_Saturation(12345);` Ausgabe "Sättigung: 100%".

```php
string TWINKLY_Gestalt(int $InstanzID);
```

Liest alle Geräteinformationen aus.  
Die Funktion liefert als Rückgabewert einen (sprintf) formatierten String.

__Beispiel__: `TWINKLY_Gestalt(12345);`

> Produktname: Twinkly  
> Hardware-Version: 100  
> Bytes pro LED: 3  
> Hardware-ID: ab01cd  
> Flash-Größe: 64  
> LED-Typ: 14  
> Produktcode: TWS250STP-B  
> Firmware-Familie: F  
> Gerätename: Weihnachtsbaum  
> Laufzeit: 239317051  
> MAC: 12:34:56:ba:dc:fe  
> UUID: 12345678-bacd-1234-abcd-1234567890ab  
> Max. unterstützte LEDs: 1020  
> Anzahl LEDs: 250  
> LED-Profil: RGB  
> Bildrate: 24  
> Gemessene Bildrate: 23,810000  
> Filmkapazität: 992  
> Copyright: LEDWORKS 2018  

```php
string TWINKLY_Version(int $InstanzID);
```

Liest die installierte Firmwareversion des Gerätes aus.  
Die Funktion liefert als Rückgabewert einen String (Firmware: x.yy.zz).

__Beispiel__: `TWINKLY_Version(12345);` Ausgabe "Firmware: 2.4.16".

```php
string TWINKLY_Network(int $InstanzID);
```

Liest die eingestellten Netzwerkinformationen aus.
Die Funktion liefert als Rückgabewert einen (sprintf) formatierten String.

__Beispiel__: `TWINKLY_Network(12345);`

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
string TWINKLY_DeviceName(int $InstanzID, string $NewName);
```

Setzt den Gerätenamen (Alias) neu.  
Die Funktion liefert als Rückgabewert einen String (Erfolgsmeldung).

__Beispiel__: `TWINKLY_DeviceName(12345, 'Lichterkette');` Ausgabe "Der Name wurde erfolgreich geändert!".

### 9. Versionshistorie

v4.0.20261102

* _NEU_: Kompatibilität auf Symcon 8.1 hochgesetzt
* _NEU_: Automatischer Statusabgleich mit dem Gerät (Aktualisierungsintervall), inkl. Pausierung bei längerer Nichterreichbarkeit
* _NEU_: Film als Aufzählung über die eindeutige Film-ID (__Achtung:__ die Variable Film wird neu angelegt, Verknüpfungen müssen erneuert werden)
* _NEU_: Effekt als Aufzählung, Anzahl der Effekte wird vom Gerät gelesen
* _NEU_: Neue Instanzstatus (keine IP-Adresse, Gerät nicht erreichbar, Abgleich pausiert)
* _NEU_: Darstellungen überarbeitet (neue Icons)
* _NEU_: Konfigurationsformular auf Standardlayout umgestellt (inkl. Versionsanzeige)
* _FIX_: Erweiterter Modus wurde nicht übernommen
* _FIX_: Werte werden erst nach Bestätigung durch das Gerät übernommen
* _FIX_: Zeitschaltung wird nur noch bei geänderten Einstellungen übertragen
* _FIX_: Zeitbegrenzung der Gerätezugriffe und Wiederverwendung des Login-Tokens
* _FIX_: Übersetzungen und Dokumentation überarbeitet

v3.2.20240222

* _NEU_: Anpassungen für IPS 7.x (Konfigurator & TileVisu)
* _NEU_: Schaltervariable migriert auf Boolean
* _FIX_: Übersetzungen nachgezogen
* _FIX_: Interne Bibliotheken überarbeitet

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
* _FIX_: Debugausgabe überarbeitet
* _FIX_: API erweitert und überarbeitet

v1.0.20200501

* _NEU_: Initialversion

## Entwickler

Seit nunmehr über 10 Jahren fasziniert mich das Thema Haussteuerung. In den letzten Jahren betätige ich mich auch intensiv in der Symcon Community und steuere dort verschiedenste Skript und Module bei. Ihr findet mich dort unter dem Namen @pitti ;-)

[![GitHub](https://img.shields.io/badge/GitHub-@wilkware-181717.svg?style=for-the-badge&logo=github)](https://wilkware.github.io/)

## Spenden

Die Software ist für die nicht kommerzielle Nutzung kostenlos, über eine Spende bei Gefallen des Moduls würde ich mich freuen.

[![PayPal](https://img.shields.io/badge/PayPal-spenden-00457C.svg?style=for-the-badge&logo=paypal)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8816166)

## Lizenz

Namensnennung - Nicht-kommerziell - Weitergabe unter gleichen Bedingungen 4.0 International

[![Licence](https://img.shields.io/badge/License-CC_BY--NC--SA_4.0-EF9421.svg?style=for-the-badge&logo=creativecommons)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
