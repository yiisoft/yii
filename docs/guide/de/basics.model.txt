Model
=====

Ein Model ist eine Instanz von [CModel] oder dessen Kindklasse. Models
werden verwendet, um Daten und ihre Geschäftslogik festzuhalten.

Ein Model steht für ein einzelnes Datenobjekt. Dabei kann es sich um eine Zeile
in einer Datenbanktabelle oder ein HTML-Formular mit Benutzereingaben handeln.
Jedes Attribut des Models entspricht einem Feld des Datenjobekts.
Ein Attribut hat ein Label (Bezeichnung bzw. Beschriftung) und kann mit einer
Reihe von Regeln validiert, also auf Gültigkeit geprüft werden.

Yii bietet zwei Model-Arten: FormModel (Formularmodel) und ActiveRecord. Beide
erweitern die selbe Basisklasse [CModel].

Ein FormModel ist eine Instanz von [CFormModel]. Es wird dann verwendet,
wenn eingegebene Daten nur kurz abgelegt werden sollen. Meist werden solche Daten
gesammelt, benutzt und anschließend wieder verworfen. Für eine Anmeldeseite
könnte man z.B. ein FormModel verwenden, um Benutzernamen und Passwort zu
speichern. Weitere Details dazu finden Sie unter [Arbeiten mit Formularen](/doc/guide/form.model).

ActiveRecords (AR) sind ein bekanntes Entwursfmuster, das oft verwendet wird,
um Datenbankzugriffe auf objektorientierte Weise zu abstrahieren. Jedes
AR-Objekt ist eine Instanz von [CActiveRecord] oder einer davon abgeleiteten
Klasse. Ein Record steht für eine einzelne Zeile einer Datenbanktabelle. Die
Datenfelder werden auf die Eigenschaften des AR-Objekts abgebildet. Weitere
Details zu AR finden Sie unter [ActiveRecord](/doc/guide/database.ar).

<div class="revision">$Id: basics.model.txt 3251 2011-06-01 00:24:06Z qiang.xue $</div>
