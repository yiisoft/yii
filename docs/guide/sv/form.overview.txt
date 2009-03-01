Arbeta med Forms
================

Insamling av användardata via HTML-formulär är en av huvuduppgifterna vid 
utveckling av webbapplikationer. Förutom utformning av formulär behöver utvecklare se 
till att formulären fylls med existerande data eller standardvärden, validera 
användarinmatning, presentera relevanta felmeddelanden i händelse av ogiltig 
indata samt spara inmatning till icke-flyktigt (persistent) minne. Yii förenklar väsentligt 
detta jobbflöde genom dess MVC-arkitektur.

Följande steg är typiska att följa när man handskas med formulär i Yii:

   1. Skapa en modellklass (model) som representerar datafälten som skall samlas in;
   1. Skapa en kontrolleråtgärd (action) med kod som svarar på inskickning av formuläret.
   1. Skapa ett formulär i vyskriptfilen (view) associerad med kontrolleråtgärden.

I följande underavsnitt beskrivs i detalj vart och ett av dessa steg.

<div class="revision">$Id: form.overview.txt 163 2008-11-05 12:51:48Z weizhuo $</div>