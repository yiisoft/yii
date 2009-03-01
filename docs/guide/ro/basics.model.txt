Model
=====

Un model este o instanta a clasei [CModel] sau a unei clase derivate. Modelele sunt folosite
pentru a pastra date si regulile lor de functionare relevante.

Un model reprezinta un singur obiect de date. Poate fi un rand dintr-o tabela
a bazei de date, sau poate fi un form cu input-uri venite de la utilizator. Fiecare camp
al modelului reprezinta un atribut al modelului. Fiecare atribut are un label care poate fi
validat cu un set de reguli. 

Yii implementeaza doua tipuri de modele: modelul form si active record. Ambele sunt derivate
din aceeasi clasa de baza [CModel].

Un model form este o instanta a clasei [CFormModel]. Modelul form este folosit pentru a
pastra datele furnizate de utilizatorii Web. De obicei, aceste date sunt preluate,
folosite, si apoi sterse. De exemplu, intr-o pagina login, putem folosi un model form
care va contine numele utilizatorului si parola lui. Ele vor fi preluate de la un utilizator web.
Pentru mai multe detalii, trebuie citita sectiunea [Lucrul cu formularele](/doc/guide/form.model)

Active Record (AR) este un concept foarte raspandit si folosit prin care se face accesul la
baza de date asemanator accesului unui obiect. Fiecare obiect AR este o instanta a clasei
[CActiveRecord] sau a unei clase derivate. Un obiect AR reprezinta un singur rand dintr-o tabela
din baza de date. Campurile din acest rand sunt concepute ca proprietati ale obiectului AR.
Detalii despre AR pot fi gasite in sectiunea [Active Record](/doc/guide/database.ar).

<div class="revision">$Id: basics.model.txt 162 2008-11-05 12:44:08Z weizhuo $</div>