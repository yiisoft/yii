Arbeta med databaser
====================

Yii erbjuder kraftfullt stöd för databasprogrammering.

Genom användning av PHP-tillägget Data Objects (PDO) möjliggör Yii Data Access Objects 
(DAO) åtkomst till olika databashanterare (DBMS) via ett enda enhetligt gränssnitt. 
Applikationer utvecklade för att använda Yii DAO kan enkelt ställas om för 
användning med en annan DBMS utan att koden för databasåtkomst behöver ändras. 

Yii Query Builder erbjuder ett objectorienterat sätt att bygga SQL-frågor, 
vilket hjälper till att minska risken för SQL-injection attacker.

Och Yii Active Record (AR) - implementerad enligt ett brett antaget 
synsätt på objekt-relationsmappning (ORM) - förenklar ytterligare 
databasprogrammeringen. Genom att representera en tabell i form av en klass och en 
rad som en instans, eliminerar Yii AR den återkommande uppgiften att skriva de 
SQL-satser som huvudsakligen hanterar CRUD-operationer (create, read, update och 
delete).

Även om Yii DAO och AR kan hantera nästan alla databasrelaterade uppgifter, är 
det fortfarande möjligt att använda egna databasbibliotek i en Yii-applikation. 
Faktum är att Yii-ramverket omsorgsfullt konstruerats med tanke på att kunna 
användas tillsammans med andra tredjepartsbibliotek.

<div class="revision">$Id: database.overview.txt 2666 2010-11-17 19:56:48Z qiang.xue $</div>