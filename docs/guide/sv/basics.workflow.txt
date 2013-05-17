Arbetsgång vid utveckling
=========================

Nu när vi beskrivit de grundläggande koncepten i Yii, visar vi den normala 
arbetsgången vid utveckling av en webbapplikation med hjälp av Yii. Det 
förutsätts att kravanalys såväl som erforderlig designanalys gjorts för 
applikationen.

   1. Skapa den övergripande katalogstrukturen. Verktyget `yiic`, beskrivet i 
   [Skapa en första Yii-applikation](/doc/guide/quickstart.first-app), kan användas 
   för att snabba upp detta steg.

   2. Konfigurera [application](/doc/guide/basics.application). Detta görs genom 
   modifiering av applikationens konfigurationsfil. I detta steg kan också några 
   applikationskomponenter behöva skrivas (t.ex. komponenten user).

   3. Skapa en [modell](/doc/guide/basics.model)-klass för varje typ av data som 
   skall hanteras.  Verktyget `Gii`, beskrivet i
   [Skapa en första Yii-applikation](/doc/guide/quickstart.first-app#implementing-crud-operations) 
   samt i [Automatisk kodgenerering](/doc/guide/topics.gii), kan användas till att 
   automatiskt generera [active record](/doc/guide/database.ar)-klassen för varje 
   databastabell av intresse.

   4. Skapa en [kontroller](/doc/guide/basics.controller)-klass för varje typ av 
   request från användare. Hur request skall klassificeras beror på det aktuella 
   kravet. Generellt, om en modellklass behöver tillgås av användare bör den ha 
   en motsvarande kontrollerklass. Verktyget `Gii` kan automatisera även detta 
   steg.

   5. Implementera åtgärder, [actions](/doc/guide/basics.controller#action), och 
   deras tillhörande vyer, [views](/doc/guide/basics.view). Det är här det 
   egentliga arbetet behöver göras.

   6. Konfigurera nödvändiga åtgärdsfilter, 
   [filters](/doc/guide/basics.controller#filter), i kontrollerklasserna.

   7. Skapa teman, [themes](/doc/guide/topics.theming), om denna finess erfordras.

   8. Skapa översatta meddelanden om i18n, [internationalization](/doc/guide/topics.i18n), erfordras.

   9. Identifiera data och vyer som kan cachelagras och anslut relevanta 
   [caching](/doc/guide/caching.overview)-tekniker.

   10. Slutlig fintrimning [tune up](/doc/guide/topics.performance) samt utsättning.

För vart och ett av ovanstående steg kan testfall behöva skapas och utföras.


<div class="revision">$Id: basics.workflow.txt 2718 2010-12-07 15:17:04Z qiang.xue $</div>