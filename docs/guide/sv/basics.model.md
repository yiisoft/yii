Model
=====

En `model` är en instans av [CModel] eller av en klass som utökar [CModel]. 
Modeller är avsedda att innehålla data samt tillhörande affärsregler (business rules).

En modell representerar ett enstaka dataobjekt. Det kan vara en rad i en 
databastabell eller ett html-formulär med inmatningsfält. Varje fält i 
dataobjektet representeras som ett attribut i modellen. Attributet 
har en etikett (label) och det kan valideras mot en uppsättning regler.

Yii implementerar två slags modeller: form model och active record. De är båda 
underklasser till samma basklass, [CModel].

En form model är en instans av klassen [CFormModel]. Formulärmodellen används till 
lagring av användarinmatad data. Sådan data brukar ofta samlas, användas och 
därefter slängas. Exempelvis kan en inloggningssida använda en formulärmodell till 
att representera information om användarnamn och lösen tillhandahållna av en 
slutanvändare. För fler detaljer hänvisas till [Arbeta med formulär](/doc/guide/form.overview)

Active Record (AR) är ett designmönster som används för att abstrahera åtkomst 
till databaser på ett objektorienterat sätt. Varje AR-objekt är en instans av 
klassen [CActiveRecord] eller av en nedärvd klass och det representerar en enstaka 
rad i en databastabell. Fälten i raden/posten representeras som propertyn i AR-
objektet. Detaljer om AR finns i [Active Record](/doc/guide/database.ar).

<div class="revision">$Id: basics.model.txt 3251 2011-06-01 00:24:06Z qiang.xue $</div>