Nieuwe Features
===============

Deze pagina geeft een samenvatting van de nieuwe features geintroduceerd in elke Yii-release.

Versie 1.1.7
-------------
 * [Ondersteuning toegevoegd voor RESTful URL's](/doc/guide/topics.url#user-friendly-urls)
 * [Ondersteuning toegevoegd voor query caching](/doc/guide/caching.data#query-caching)

Versie 1.1.6
-------------
 * [Toegevoegd: query builder](/doc/guide/database.query-builder)
 * [Toegevoegd: database-migratie](/doc/guide/database.migration)
 * [Richtlijnen voor MVC gebruik](/doc/guide/basics.best-practices)
 * [Ondersteuning toegevoegd voor het gebruik van anonieme parameters en globale opties in console-commando's](/doc/guide/topics.console)

Versie 1.1.5
-------------

 * [Ondersteuning toegevoegd voor console-commando-acties en parameter binding](/doc/guide/topics.console)
 * [Ondersteuning toegevoegd voor autoloading van namespaced classes](/doc/guide/basics.namespace)
 * [Ondersteuning toegevoegd voor theming van widget views](/doc/guide/topics.theming#theming-widget-views)

Versie 1.1.4
-------------

 * [Ondersteuning toegevoegd voor automatic binding van action parameters](/doc/guide/basics.controller#action-parameter-binding)

Versie 1.1.3
-------------

 * [Ondersteuning toegevoegd om standaardwaardes voor widgets in de applicatieconfiguratie in te stellen](/doc/guide/topics.theming#customizing-widgets-globally)

Versie 1.1.2
-------------

 * [Een web-based codegenerator toegevoegd, genaamd Gii](/doc/guide/topics.gii)

Versie 1.1.1
-------------

 * CActiveForm toegevoegd, welke het schrijven van formuliergerelateerde code
 eenvoudiger maakt, en ondersteund naadloze en consistente validatie aan
 client- en server-zijde.
 
 * Code gegenereerd door de yiic-tool is gerefactoreerd. In het bijzonder is de
 raamwerkapplicatie nu gegenereerd met meerdere layouts; Het menu met bewerkingen is
 geherorganiseerd voor CRUD-pagina's; zoek- en filteropties zijn toegevoegd aan de
 gegenereerde beheerpagina; CActiveForm word gebruikt om formulieren te renderen.

 * [Support toegevoegd voor het definiëren van globale yiic-commando's](/doc/guide/topics.console)

Versie 1.1.0
-------------

 * [Ondersteuning toegevoegd voor het schrijven van unit- en functional-tests](/doc/guide/test.overview)

 * [Ondersteuning toegevoegd voor het gebruik van widget skins](/doc/guide/topics.theming#skin)

 * [Een uitbreidbare formulieren-builder toegevoegd](/doc/guide/form.builder)

 * De manier verbeterd waarmee veilige attributes gedeclareerd worden. Zie
 [Beveiligen van Attribute Assignments](/doc/guide/form.model#securing-attribute-assignments).

 * Het standaard eager loading-algoritme veranderd in relationele active record queries, zodat alle tabellen gejoined worden in één SQL-statement.

 * Het standaard tabelalias veranderd naar de naam van active-record relaties.
 Changed the default table alias to be the name of active record relations.

 * [Ondersteuning toegevoegd voor tabel-prefixen](/doc/guide/database.dao#using-table-prefix).

 * Een hele verzameling nieuwe extensies toegevoegd, bekend als de [Zii library](http://code.google.com/p/zii/).

 * Het alias voor de primaire tabel in een AR query is vastgezet op 't'

<div class="revision">$Id: changes.txt 2949 2011-02-11 03:48:01Z qiang.xue $</div>
