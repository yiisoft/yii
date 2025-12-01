Utvidgning av Yii
=================

Utvidgning av Yii är en vanlig åtgärd vid utvecklingsarbete. Till exempel, när 
en ny kontroller skall skrivas, sker detta genom utvidgning av Yii i form av arv 
från dess [CController]-klass; när en ny widget skrivs, utvidgas [CWidget] eller 
en redan existerande widgetklass. Om den utvidgade koden är konstruerad med 
avsikt att återanvändas av tredjepartsutvecklare, kallas den för en *extension*.

En extension tjänar vanligtvis ett distinkt syfte. Med Yii's nomenklatur, kan den klassificeras enligt följande,

 * [applikationskomponent](/doc/guide/basics.application#application-component)
 * [behavior](/doc/guide/basics.component#component-behavior)
 * [widget](/doc/guide/basics.view#widget)
 * [kontroller](/doc/guide/basics.controller)
 * [åtgärd](/doc/guide/basics.controller#action)
 * [filter](/doc/guide/basics.controller#filter)
 * [konsolkommando](/doc/guide/topics.console)
 * validator: en validator är en komponentklass som utvidgar [CValidator].
 * helper: en helper är en klass bestående endast av (statiska) klassmetoder. Det motsvarar globala funktioner med klassens namn som namnområde.
 * [modul](/doc/guide/basics.module): en modul är en komplett och oberoende mjukvaruenhet som består av [modeller](/doc/guide/basics.model), [vyer](/doc/guide/basics.view), [kontrollrar](/doc/guide/basics.controller) samt andra stödkomponenter. En modul motsvarar ur många aspekter en [applikation](/doc/guide/basics.application). Den huvudsakliga skillnaden är att en modul finns inuti en applikation. Till exempel kan en modul som erbjuda funktionalitet för administrering av användare.
 
En extension kan även vara en komponent som inte faller inom någon av ovannämnda 
kategorier. Faktum är att Yii omsorgsfullt konstruerats så att nästan varje del 
av dess kodbas kan utvidgas och anpassas till individuella behov.

<div class="revision">$Id: extension.overview.txt 2739 2010-12-14 01:50:04Z weizhuo $</div>