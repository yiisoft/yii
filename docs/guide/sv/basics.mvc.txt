Model-View-Controller (MVC)
===========================

Yii implementerar designmönstret model-view-controller (MVC), vilket är brett 
antaget inom webbprogrammering. MVC syftar till att separera överväganden om 
affärsregler (business logic) från sådana som avser användargränssnitt (user 
interface), så att utvecklare lättare kan ändra det ena utan att påverka det 
andra. Inom MVC, representerar modellen information (data) samt affärsregler; Vyn 
(view) innehåller element ur användargränssnittet såsom text och inmatningsfält; 
kontrollern ombesörjer kommunikation mellan modell och vy.

Utöver MVC, introducerar Yii även en förgrundskontroller (front-controller), 
Application, som representerar en exekveringsomgivning för bearbetning av inkomna 
request. Application samlar information om en request från användare och skickar 
sedan denna vidare till relevant kontroller för fortsatt behandling.

Följande diagram visar den statiska strukturen hos en Yii-applikation:

![Statisk struktur hos Yii-applikation](structure.png)


Typiskt bearbetningsflöde
-------------------------

Följande diagram visar det typiska bearbetningsflödet för en Yii-applikation när 
den hanterar en request från användare:

![Typiskt bearbetningsflöde för en Yii-applikation](flow.png)

   1. En användare skickar en request med URL:en 
   `http://www.example.com/index.php?r=post/show&id=1` och webbservern hanterar 
   denna request genom att köra startskriptet `index.php`.
   
   2. Startskriptet skapar ett applikationsobjekt, en instans av 
   [Application](/doc/guide/basics.application) samt kör denna.
   
   3. Applikationsobjektet skaffar sig den detaljerade informationen 
   om användarens request från [applikationskomponenten](/doc/guide/basics.application#application-component) `request`.
   
   4. Applikationen avgör vilken [controller](/doc/guide/basics.controller) 
   resp. [action](/doc/guide/basics.controller#action) som efterfrågas, med 
   hjälp av applikationskomponenten `urlManager`. I detta exempel är kontrollern 
   `post`, vilket motsvarar klassen `PostController`; åtgärden (action) är 
   `show`, vars faktiska innebörd bestäms av kontrollern.
   
   5. Applikationen skapar en instans av den begärda kontrollern för fortsatt 
   hantering av användarens request. Kontrollern avgör att åtgärden `show` 
   refererar till metoden `actionShow` i kontrollerklassen. Därefter skapar och 
   exekverar den filter (t.ex. tillträdeskontroll, prestandamätning) som 
   associerats till denna åtgärd. Åtgärden exekveras sedan om detta tillåts av 
   filtren.
   
   6. Åtgärden läser en `Post`-[modell](/doc/guide/basics.model) vars ID är `1` 
   från databasen.
   
   7. Åtgärden renderar [vyn](/doc/guide/basics.view) `show` med innehåll från 
   modellen `Post`.
   
   8. Vyn läser och presenterar attributen från modellen `Post`.
   
   9. Vyn exekverar några [widgets](/doc/guide/basics.view#widget).
   
   10. Resultatet från vyrenderingen bäddas in i en [layout](/doc/guide/basics.view#layout). 
   
   11. Åtgärden slutför renderingen och det färdiga resultatet presenteras för användaren.


<div class="revision">$Id: basics.mvc.txt 3321 2011-06-26 12:54:22Z mdomba $</div>