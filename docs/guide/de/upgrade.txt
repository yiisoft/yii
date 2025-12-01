Upgrade von Version 1.0 auf 1.1
===============================

Änderungen bei Model-Szenarien
------------------------------

- CModel::safeAttributes() wurde entfernt. Als sichere Attribute gelten jetzt
all jene, die im entsprechenden Szenario durch eine Validierungsregel in CModel::rules() 
überprüft werden.

- CModel::validate(), CModel::beforeValidate(), CModel::afterValidate(),
CModel::setAttributes() und CModel::getSafeAttributeNames() wurden geändert.
Der Parameter 'szenario' wurde entfernt. Szenarien sollten über
CModel::scenario gesetzt werden.

- CModel::getValidator() wurde geändert und CModel::getValidatorsForAttributes() 
entfernt. CModel::getValidator() liefert jetzt nur jene Validatoren zurück, die im Szenario
verwendet werden, das über die scenario-Eigenschaft des Models gesetzt wurde.

- CModel::isAttributeRequired() und CModel::getValidatorsForAttribute() wurden
geändert. Der Parameter scenario wurde entfernt. Stattdessen wird die
Eigenschaft scenario des Models verwendet.

- CHtml::scenario wurde entfernt. CHtml verwendet jetzt stattdessen das in
CModel gesetzte Szenario.


Änderungen in der Eager Loading-Methode bei Relationalen ActiveRecords
----------------------------------------------------------------------

- Standardmäßig wird für alle Verbundeigenschaften ein JOIN-Ausdruck generiert 
und ausgeführt. Falls für die Haupttabelle die `LIMIT`- oder `OFFSET`-Option
gesetzt ist, wird diese zunächst gesondert abgefragt, gefolgt von einer
weiteren SQL-Abfrage, die alle verbundenen Objekte zurückliefert. In Version
1.0.x gab es `N+1` SQL-Abfragen, falls beim Eager Loading `N` `HAS_MANY`- oder
`MANY_MANY`-Beziehungen inbegriffen waren.


Änderungen bei Tabellenaliasen in Relationalen ActiveRecords
------------------------------------------------------------

- Der Standardalias für relationale Tabellen entspricht jetzt dem Namen der
zugehörigen Beziehung. In Version 1.0.x hat Yii die Namen der Verbundtabellen
standardmäßig automatisch erzeugt und man musste die Präfix `??` verwenden,
um sich auf diesen Alias zu beziehen.

- Der Alias für die Haupttabelle lautet jetzt `t`. In 1.0.x Versionen wurde
stattdessen der Name der Haupttabelle verwendet. Dadurch ist bestehender Code
evtl. nicht mehr lauffähig, wenn Spaltennamen explizit mit vorangestelltem Tabellennamen
angesprochen wurden. Dies kann gelöst werden, indem stattdessen 't.'
vorangestellt wird.


Änderungen bei tabellarischen Eingaben
--------------------------------------

- Als Attributname darf jetzt nicht mehr `Feld[$i]` verwendet werden. Sie
sollten jetzt `[$i]Feld` heißen, um auch Array-Felder verwenden zu können
(z.B. `[$i]Feld[$index]`).

Weitere Änderungen
------------------

- Die Signatur des Konstruktors in [CActiveRecord] hat sich geändert. Der
erste Parameter (Liste mit Attributen) wurde entfernt.

<div class="revision">$Id: upgrade.txt 2305 2010-08-06 10:27:11Z alexander.makarow $</div>
