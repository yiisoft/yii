Lucrul cu formulare
===================

Colectarea datelor de la utilizator prin formulare HTML este unul din task-urile cele mai importante
din dezvoltarea unei aplicatii Web. In afara de proiectarea formularelor, programatorii
trebuie sa populeze formularele cu date existente sau cu valori implicite, sa valideze
input-urile primite de la utilizatori, sa afiseze mesaje de eroare corespunzatoare atunci
cand exista input-uri care nu sunt valide, sa salveze datele valide primite de la utilizatori
intr-un mediu de stocare permanent. Yii simplifica enorm acest task datorita arhitecturii sale MVC.

De obicei, sunt necesari urmatorii pasi cand ne confruntam cu formulare in Yii:

   1. Cream o clasa cu un model care reprezinta campurile de date care vor fi colectate.
   1. Cream un action intr-un controller in care scriem cod care primeste datele de la utilizatori.
   1. Cream un formular intr-un fisier view asociat cu action-ul controller-ului.

In urmatoarele sub-sectiuni, vom descrie in detaliu fiecare din acesti pasi.

<div class="revision">$Id: form.overview.txt 163 2008-11-05 12:51:48Z weizhuo $</div>