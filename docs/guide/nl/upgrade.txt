Upgraden van versie 1.0 naar 1.1
=================================

Veranderingen gerelateerd aan Modelscenario's
---------------------------------------------

- CModel::safeAttributes() is verwijderd. Veilig attributen worden nu
gedefinieerd als de attributen waarvoor validatieregels zijn opgesteld, 
zoals in CModel::rules() voor het specifieke scenario.

- CModel::validate(), CModel::beforeValidate(), CModel::afterValidate(),
CModel::setAttributes() en CModel::getSafeAttributeNames() zijn gewijzigd.
De 'scenario'-parameter is verwijderd. Het getten en setten van een
modelscenario moet via CModel::scenario.

- CModel::getValidators() is gewijzigd, en CModel::getValidatorsForAttribute() 
is verwijderd. CModel::getValidators() geeft alleen validators terug die van 
toepassing zijn op het scenario zoals gespecificeerd door de scenario-property 
van een model.

- CModel::isAttributeRequired() en CModel::getValidatorsForAttribute() zijn
gewijzigd. De scenario-parameter is verwijderd. In plaats hiervan word nu de
scenario-property van een model gebruikt.

- CHtml::scenario verwijderd. In plaats hiervan zal CHtml de scenario-property
van een model gebruiken.


Veranderingen gerelateerd aan Eager Loading van relationele Active Record
--------------------------------------------------------------------------

- Standaard word een enkel JOIN-statement gegenereerd en uitgevoerd voor alle
relaties betrokken bij Eager Loading. Wanneer de primaire tabel een `LIMIT` of
`OFFSET` query-optie bevat, word eerst alleen deze tabel opgevraagd, gevolgd
door een ander SQL-statement dat de gerelateerde objecten terughaalt. Eerder,
in versie 1.0.x, is het standaardgedrag dat er `N+1` SQL-statements gedaan
worden bij Eager Loading als er `N`, `HAS_MANY` of `MANY_MANY` relaties bij
betrokken zijn.


Veranderingen gerelateerd aan tabel-aliassen in relationele Active Record
------------------------------------------------------------------------

- Het standaard-alias voor een relationele tabel is nu hetzelfde als de
overeenkomende relatienaam. Eerder in versie 1.0.x genereerde Yii standaard
automatisch een tabel-alias voor elke relationele tabel, en moesten we het
`??`-prefix gebruiken om te refereren naar het automatisch gegenereerde alias.

- Het alias voor een primaire tabel in een AR-query is vastgezet op `t`.
In versie 1.0.x is deze hetzelfde als de tabelnaam. Dit zorgt ervoor
dat bestaande AR-querycode niet meer werkt wanneer expliciet kolom-prefixen
gespecificeerd waren met de tabelnaam er in. De oplossing is om deze prefixen
te vervangen door 't.'.


Veranderingen gerelateerd aan tabulaire input
---------------------------------------------

- Het gebruik van `Field[$i]` is niet meer geldig voor attribuutnamen. Deze
moeten genaamd worden zoals `[$i]Field` om ondersteuning te bieden voor velden
van het array-type (bv. `[$i]Field[$index]`).


Andere wijzigingen
------------------

- De specificatie van de [CActiveRecord]-constructor is veranderd. De eerste
parameter (lijst van attributen) is verwijderd.

<div class="revision">$Id: upgrade.txt 2305 2010-08-06 10:27:11Z alexander.makarow $</div>