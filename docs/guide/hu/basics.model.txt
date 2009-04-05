Model
=====

Egy model a [CModel] vagy annak egy leszármazottjának példánya. A model-eket
az adatok és ahozzájuk tartozó üzleti logika tárolására használjuk.

Egy model egy adott adat objektumot képvisel. Ez lehet sor egy adatbázis táblában,
vagy egy felhasználó által küldött űrlap (form). Az adat objektum minden mezője
a model egy tulajdonságaként van meghatározva. Minden tulajdonságnak van egy
cimkéje, és érvényesíthető adot szabályokkal szemben.

A Yii kétféle model-t valósít meg: form (űrlap) model-t és active record-ot.
Mindkettő ugyanabból az alap osztályból származik, ami a [CModel].

Egy form model a [CFormModel] egy példánya. A form model felhasználótól érkező
adatok tárolására használható. Ezeket az adatokat általában begyűjtjük,
felhasználjuk majd eldobjuk. Például, egy belépési oldalon használhatunk egy form
model-t, hogy képviseljük a felhasználó által megadott felhasználónevet és jelszót.
Vonatkozó részletek a [Munka űrlapokkal/formokkal](/doc/guide/form.model) részben.

Az Active Record (AR) egy tervezési minta, ami adatbázis elérés absztrakcióra
használatos objektum-orientált módon. Minden AR objektum a [CActiveRecord] vagy egy
leszármazott osztályának példánya, ami egy adott sort képvisel egy adatbázis táblában.
A táblában található mezők az AR objektum tulajdonságaiként vannak meghatározva.
Az AR-ról részletek az [Active Record](/doc/guide/database.ar) részben találhatóak.

<div class="revision">$Id: basics.model.txt 162 2008-11-05 12:44:08Z weizhuo $</div>