Migracja z wersji 1.0 do 1.1
=================================

Zmiany związane ze scenariuszami modelu
------------------------------------

- usunięto metodę CModel::safeAttributes(). Bezpiecznymi atrybutami w ramach konkretnego scenariusza
są teraz atrybuty, które posiadają zdefiniowane reguły w metodzie CModel::rules() dla tego scenariusza.

- zmieniono metody CModel::validate(), CModel::beforeValidate() oraz CModel::afterValidate().
Dla metod CModel::setAttributes(), CModel::getSafeAttributeNames() usunięto parametr
'scenario'. Powinieneś teraz ustawiać i usuwać scenariusz modelu poprzez CModel::scenario.

- zmieniono metody CModel::getValidators() oraz CModel::getValidatorsForAttribute().
CModel::getValidators() zwraca tylko validatory mające zastosowanie dla scenariusza określonego
we właściwości scenario modelu.

- zmieniono metodę CModel::isAttributeRequired() oraz CModel::getValidatorsForAttribute().
Parametr przekazujący scenariusz został usunięty. W zamian w modelu 
należy używać właściwości scenariusza `scenario`.

- usunięto CHtml::scenario. W zamian CHtml będzie używać właściwości scenariusza `scenario` modelu.

Zmiany związane z zachłannym ładowaniem dla relacyjnego rekordu aktywnego (AR)
---------------------------------------------------------------

- Domyślnie, pojedyncze wyrażenie JOIN będzie wygenerowane i użyte dla wszystkich  
relacji zaangażowanych w zachłanne ładowanie. Jeśli dla głównej tabeli ustawione są 
opcje zapytania takie jak `LIMIT` oraz `OFFSET`, najpierw zostanie zadane zapytanie tylko dla tej tabeli
a następnie wywołane zostanie kolejne zapytanie SQL, które zwróci wszystkie powiązane
obiekty. Poprzednio w wersji 1.0.x, domyślnym zachowaniem było wywołanie `N+1` 
zapytań SQL jeśli zachłanne ładowanie angażowało `N` relacji typu `HAS_MANY` lub `MANY_MANY`.

Zmiany związane z aliasem tabeli w relacyjnym rekordzie aktywnym.
------------------------------------------------------------

- Domyślny alias dla tabeli relacyjnej odpowiada nazwie tej relacji. Poprzednio, 
w wersji 1.0.x, Yii generował domyślny alias tabeli automatycznie, przez co należało
używać prefiksu `??.` podczas odnoszenia się do tego wygenerowanego automatycznie aliasu.

- Alias nazwy tabeli głównej w zapytaniu AR ustalony został jako `t`.
Poprzednio w wersji 1.0.x, był on taki sam jak nazwa tabeli. Powodowało to, 
iż istniejący kod zapytania AR przestawał działać, jeśli jawnie zdefiniowaliśmy
prefiksy kolumn używając nazwy tabeli. Rozwiązaniem tego problemu jest zastąpienie
tych prefiksów przez 't.'.

Zmiany związane tablicowymi danymi wejściowymi
----------------------------------

- Używanie `Field[$i]` dla nazw atrybutów nie jest już poprawne. W zamian należy stosować `[$i]Field` 
w celu wspierania pól o typie tablicowym (np. `[$i]Field[$index]`).

Pozostałe zmiany
-------------
- Sygnatura klasy [CActiveRecord] została zmieniona. Pierwszy parametr (lista atrybutów) została usunięta.

<div class="revision">$Id: upgrade.txt 2305 2010-08-06 10:27:11Z alexander.makarow $</div>