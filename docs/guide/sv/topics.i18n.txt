Internationalisering
====================

Internationalisering (I18N) avser processen att utforma en applikations mjukvara 
så att den, utan konstruktionsändringar, kan anpassas till varierande språk och 
regioner. För webbapplikationer är detta av speciell vikt, eftersom basen av 
potentiella användare är världsomspännande.

Ramverket Yii stöder I18N ur flera aspekter. Det tillhandahåller:

   - Locale-data för flertalet språk samt språkvarianter.
   - Meddelande- och filöversättningstjänst.
   - Formatering av datum och tid anpassad efter locale.
   - Formatering av tal anpassad efter locale.

I följande underavsnitt, kommer varje aspekt ovan att utvecklas närmare.

Locale och språk
----------------

Locale är en uppsättning parametrar som definierar användarens språk, land samt 
eventuella mer speciella variationer som användare kan föredra att se i sina 
användargränssnitt. Den identifieras vanligen genom ett ID bestående av ett 
språk-ID och ett region-ID. Till exempel, ID:t `en_US` står för engelska språket 
och USA. För enhetlighetens skull har alla locale-ID:n i Yii kanoniserats till 
formatet `LanguageID` eller `LanguageID_RegionID` i gemena (t.ex. `en`, 
`en_us`).

Locale representeras som en instans av [CLocale]. Den tillhandahåller 
localeberoende information, inkluderat valutasymboler, talsymboler, 
valutaformat, talformat, datum- och tidformat samt datumrelaterade namn. 
Eftersom information om språk impliceras av locale-ID, tillhandahålls den 
inte av  [CLocale]. Av samma skäl, används i många fall termerna locale och 
språk utbytbart.

Givet ett locale-ID, kan man erhålla motsvarande [CLocale]-instans genom 
`CLocale::getInstance($localeID)` eller `CApplication::getLocale($localeID)`.

> Info: Yii kommer med locale-data för nästan varje språk och region. Dessa data 
erhålls från [Common Locale Data Repository](http://unicode.org/cldr/) (CLDR). 
För varje locale används endast en delmängd av data CLDR tillhandahåller, 
eftersom originaldata även omfattar mycket sällan använd information. 
Användare kan även tillfoga sitt eget anpassade locale-data. För att göra detta, 
konfigurera propertyn [CApplication::localeDataPath] med den katalog som innehåller 
anpassat locale-data. Referera till filerna med locale-data i katalogen 
`framework/i18n/data` för att skapa anpassade filer med locale-data.

I en Yii-applikation skiljer vi på dess [målspråk|CApplication::language] och 
dess [källspråk|CApplication::sourceLanguage]. Målspråket är det språk (locale) 
som applikationens användare önskar använda sig av, medan källspråket refererar 
till det språk (locale) som applikationens källkodsfiler använder. 
Internationalisering är endast aktuell om de två språken är olika.

Man kan konfigurera [målspråk|CApplication::language] i 
[applikationskonfigurationen](/doc/guide/basics.application#application-configuration), 
eller ändra det dynamiskt innan någon internationalisering uppträder.

> Tip|Tips: Ibland vill vi kunna sätta målspråket till det språk en användare 
föredrar (specificerat som användarens preferenser i webbläsaren). För att 
åstadkomma detta kan vi hämta ID för det språk användaren föredrar med hjälp av 
[CHttpRequest::preferredLanguage].

Översättning
------------

Den I18N-finess som behövs mest är kanske översättningsfinessen, inkluderat 
meddelanden och vyer. För meddelanden översätts ett textmeddelande till det önskade 
språket, för vyer tillgängliggörs en hel fil på önskat språk.

En översättningsbegäran består av objektet som skall översättas, källspråket för 
objektet, samt målspråket objektet behöver översättas till. I Yii är källspråket 
som standard [applikationens källspråk|CApplication::sourceLanguage] medan 
målspråket som standard är [applikationsspråket|CApplication::language]. Om de 
båda språken är identiska, sker ingen översättning.

### Översättning av meddelanden

Översättning av meddelanden sker genom anrop till [Yii::t()|YiiBase::t]. Metoden 
översätter det givna meddelandet från [källspråk|CApplication::sourceLanguage] 
till [målspråk|CApplication::language].

Vid översättning av ett meddelande behöver dess kategori specificeras eftersom 
ett meddelande kan ha olika översättningar i olika kategorier (kontext). 
Kategorin `yii` är reserverad för systemmeddelanden från koden i Yii:s ramverk.

Meddelanden kan innehålla platshållare för parametrar som kommer att ersättas 
med aktuella parametervärden vid anrop till [Yii::t()|YiiBase::t]. Till exempel, 
följande meddelandeöversättningsbegäran skulle ersätta platshållaren `{alias}` i 
originalmeddelandet med aktuellt aliasvärde.

~~~
[php]
Yii::t('app', 'Path alias "{alias}" is redefined.',
	array('{alias}'=>$alias))
~~~

> Note|Märk: Meddelanden som skall översättas måste bestå av strängkonstanter. 
De får inte innehålla variabler som skulle kunna förändra meddelandeinnehållet 
(t.ex. `"Invalid {$message} content."`). Använd platshållare för parametrar om 
ett meddelande behöver varieras beroende på någon parameter.

Översatta meddelanden lagras ett förråd som kallas *meddelandekälla* (message 
source). En meddelandekälla representeras av en [CMessageSource]-instans eller 
nedärvd klass. När [Yii::t()|YiiBase::t] anropas, söker den efter meddelandet i 
meddelandekällan och returnerar dess översatta version om den hittades.

Yii kommer med följande typer av meddelandekällor. Man kan även ärva och utvidga 
[CMessageSource] för att skapa egna typer av meddelandekällor.

   - [CPhpMessageSource]: översättningarna av meddelanden lagras i form av 
   nyckel-värdepar i en PHP-array. Originalmeddelandet bildar nyckel och det 
   översatta meddelandet värde. Varje array representerar översättningar för en 
   viss kategori av meddelanden och lagras i en separat PHP-skriptfil med samma 
   namn som kategorin. PHP-filerna med översättning tillhörande samma språk 
   samlas i samma katalog namngiven med locale-ID. Alla dessa kataloger är i sin 
   tur placerade under katalogen specificerad i 
   [basePath|CPhpMessageSource::basePath].

   - [CGettextMessageSource]: översättningarna av meddelanden lagras som [GNU 
   Gettext](http://www.gnu.org/software/gettext/)-filer.

   - [CDbMessageSource]: översättningarna av meddelanden lagras i 
   databastabeller. För fler detaljer, se API-dokumentationen för 
   [CDbMessageSource].

En meddelandekälla laddas som en 
[applikationskomponent](/doc/guide/basics.application#application-component). 
Yii innehåller en fördeklarerad applikationskomponent vid namn 
[messages|CApplication::messages] vilken lagrar meddelanden som användarens 
applikation använder. Som standard är typen på denna meddelandekälla 
[CPhpMessageSource] och rotsökvägen till de översatta PHP-filerna 
`protected/messages`.

Sammanfattningsvis, för att använda översatta meddelanden krävs följande steg:

   1. Anropa [Yii::t()|YiiBase::t] på de ställen som är relevanta;

   2. Skapa PHP-filer med översättningar som 
   `protected/messages/LocaleID/CategoryName.php`. Varje fil returnerar kort och 
   gott en array bestående av meddelandeöversättningar. Märk att detta 
   förutsätter att [CPhpMessageSource], vilken är standardvalet, används för 
   lagring av översatta meddelanden.

   3. Konfigurera [CApplication::sourceLanguage] och [CApplication::language].

> Tip|Tips: Verktyget `yiic` i Yii kan användas för att hantera 
meddelandeöversättningar när [CPhpMessageSource] används som meddelandekälla. 
Kommandot `message` kan automatiskt extrahera meddelanden som är kandidater för 
översättning från utvalda källkodsfiler samt, om så erfordras, sammanfoga dessa 
översättningskandidater med redan befintliga översättningar. Ytterligare detaljer 
om användning av `message`-kommandot, erhålls genom kommandot `yiic help message`.

När [CPhpMessageSource] används till att hantera meddelandekällor, kan meddelanden 
tillhörande en specifik utvidgningsklass (t.ex. en widget, en modul) hanteras och användas 
specialiserat. Mer specifikt, om ett meddelande tillhör en utvidgning vars klassnamn är `Xyz`, 
kan meddelandekategorin specifieras på formatet `Xyz.categoryName`. Motsvarande meddelandefil antas 
vara `BasePath/messages/LanguageID/categoryName.php`, där `BasePath` refererar till katalogen som 
innehåller utvidgningens klassfil . När `Yii::t()` används för att översätta meddelanden från en 
utvidgning, kommer formatet då att bli:

~~~
[php]
Yii::t('Xyz.categoryName', 'message to be translated')
~~~

Yii har stöd för [pluralisformat|CChoiceFormat] (choice format). Pluralisformat 
avser val av översättning beroende på given kvantitet. Till exempel, det engelska 
ordet 'book' kan antingen ha singularisform eller pluralisform beroende på antal 
böcker, medan andra språk (såsom kinesiska) inte gör skillnad på formerna eller 
(såsom ryska) kan ha mer komplexa regler för pluralisform. Pluralisformat löser 
detta problem på ett enkelt men ändå effektivt sätt.

För att använda pluralisformat måste ett översatt meddelande bestå av en sekvens 
av uttryck-meddelandepar separerade av `|`, så som visas nedan:

~~~
[php]
'expr1#message1|expr2#message2|expr3#message3'
~~~

där `exprN` avser ett giltigt PHP-uttryck vilket kan utvärderas till ett boolskt 
värde indikerande huruvida motsvarande meddelande skall returneras. Endast det 
meddelande kommer att returneras, som motsvarar det första uttrycket vilket 
utvärderas till true. Ett uttryck kan innehålla en specialvariabel, `n` (märk, 
det är inte `$n`), som kommer att anta värdet lämnat som den första 
meddelandeparametern. Till exempel, antag att ett översatt meddelande är:

~~~
[php]
'n==1#one book|n>1#many books'
~~~

och vi lämnar det numeriska värdet 2 i arrayen av meddelandeparameter när 
[Yii::t()|YiiBase::t] anropas, erhåller vi `many books` som det slutliga 
översatta meddelandet.

~~~
[php]
Yii::t('app', 'n==1#one book|n>1#many books', array(1)));
//or since 1.1.6
Yii::t('app', 'n==1#one book|n>1#many books', 1));
~~~

Som kortform om uttrycket är ett tal, kommer det att behandlas som `n==Number`. 
Ovanstående översatta meddelande kan därför även skrivas:

~~~
[php]
'1#one book|n>1#many books'
~~~

### Pluralisformat

Sedan version 1.1.6 kan CLDR-baserat pluralisformat användas med en enklare syntax. 
Det är användbart för språk som har komplexa regler för pluralisformer.


Regeln för engelska pluralisformer ovan, kan skrivas påföljande sätt:

~~~
[php]
Yii::t('test', 'cucumber|cucumbers', 1);
Yii::t('test', 'cucumber|cucumbers', 2);
Yii::t('test', 'cucumber|cucumbers', 0);
~~~

Ovanstående kod ger som resultat:

~~~
cucumber
cucumbers
cucumbers
~~~

Om tal skall inkluderas kan följande kod användas.

~~~
[php]
echo Yii::t('test', '{n} cucumber|{n} cucumbers', 1);
~~~

Här är `{n}` en speciell platsmarkör för talet som lämnas med. Resultatet blir `1 cucumber`.

Det är möjligt att lämna med ytterligare parametrar:

~~~
[php]
Yii::t('test', '{username} has a cucumber|{username} has {n} cucumbers',
array(5, '{username}' => 'samdark'));
~~~

och även att byta ut tal mot något annat:

~~~
[php]
function convertNumber($number)
{
	// byt tal till ord
	return $number;
}

Yii::t('test', '{n} cucumber|{n} cucumbers',
array(5, '{n}' => convertNumber(5)));
~~~

För ryska blir det:
~~~
[php]
Yii::t('app', '{n} cucumber|{n} cucumbers', 62);
Yii::t('app', '{n} cucumber|{n} cucumbers', 1.5);
Yii::t('app', '{n} cucumber|{n} cucumbers', 1);
Yii::t('app', '{n} cucumber|{n} cucumbers', 7);
~~~

om meddelandeöversättningen är

~~~
[php]
'{n} cucumber|{n} cucumbers' => '{n} огурец|{n} огурца|{n} огурцов|{n} огурца',
~~~

blir resultatet

~~~
62 огурца
1.5 огурца
1 огурец
7 огурцов
~~~
 

> Info: För detaljer om hur många värden som skall lämnas med och i vilken ordning, 
vänligen se CLDR [Language Plural Rules page](http://unicode.org/repos/cldr-tmp/trunk/diff/supplemental/language_plural_rules.html).

### Översättning av fil

Filöversättning åstadkommes genom anrop till metoden 
CApplication::findLocalizedFile()]. Givet sökvägen till en fil som skall 
översättas, kommer denna metod att leta i underkatalogen `LocaleID` efter en fil 
med samma namn. Om den hittas kommer filsökvägen (file path) att returneras; i 
annat fall returneras originalsökvägen.

Översatta filer används huvudsakligen vid rendering av vyer. När en av 
renderingsmetoderna anropas i en kontroller eller widget, kommer vyfilerna att 
översättas automatiskt. Till exempel, om [målspråket|CApplication::language] är 
`zh_cn` medan [källspråket|CApplication::sourceLanguage] är `en_us`, leder 
rendering av en vy vid namn `edit` till att vyfilen 
`protected/views/ControllerID/zh_cn/edit.php` eftersöks. Om filen hittas, kommer 
denna översatta version att användas för rendering; i annat fall används i stället 
filen protected/views/ControllerID/edit.php`.

Översatta filer kan även användas för andra ändamål, till exempel för att visa 
en översatt bild eller ladda en locale-beroende datafil.

Formatering av datum och tid
----------------------------

Datum och tid har ofta skilda format i olika länder eller regioner. Uppgiften 
att formatera datum och tid består därför av att generera en datum- eller 
tidsträng som passar för angiven locale. Yii tillhandahåller 
[CDateFormatter] för detta ändamål.

Varje instans av [CDateFormatter] är associerad med en (mål-)locale. 
Datumformateraren för hela applikationens locale kan nås via applikationens 
property [dateFormatter|CApplication::dateFormatter].

Klassen [CDateFormatter] tillhandahåller huvudsakligen två metoder för 
formatering av datumtid i UNIX-stil.

   - [format|CDateFormatter::format]: denna metod formaterar den givna UNIX-
   tidstämpeln till en sträng enligt ett anpassat mönster (t.ex. 
   `$dateFormatter->format('yyyy-MM-dd',$timestamp)`).

   - [formatDateTime|CDateFormatter::formatDateTime]: denna metod formaterar den 
   givna UNIX-tidstämpeln till en sträng enligt ett fördefinierat mönster i mål-
   localens data (t.ex. `short`-formatet för datum, `long`-formatet för tid).

Formatering av tal
------------------

Liksom datum och tid formateras även tal olika i olika länder och regioner. 
Formatering av tal inkluderar decimalformatering, valutaformatering och 
procentformatering. Yii tillhandahåller [CNumberFormatter] för dessa ändamål.

Talformateraren för hela applikationens locale kan nås via applikationens 
property [numberFormatter|CApplication::numberFormatter].

Följande metoder tillhandahålls av [CNumberFormatter] för formatering av heltal 
eller flyttal (double).

   - [format|CNumberFormatter::format]: denna metod formaterar det givna talet 
   till en sträng enligt ett anpassat mönster (t.ex. 
   `$numberFormatter->format('#,##0.00',$number)`).

   - [formatDecimal|CNumberFormatter::formatDecimal]: denna metod formaterar det 
   givna talet genom användning av det fördefinierade decimalmönstret i 
   mål-localens data.

   - [formatCurrency|CNumberFormatter::formatCurrency]: denna metod formaterar 
   det givna talet med valutakod genom användning av det fördefinierade 
   valutamönstret i mål-localens data.

   - [formatPercentage|CNumberFormatter::formatPercentage]: denna metod 
   formaterar det givna talet genom användning av det fördefinierade 
   procentmönstret i mål-localens data.

<div class="revision">$Id: topics.i18n.txt 2890 2011-01-18 15:58:34Z qiang.xue $</div>