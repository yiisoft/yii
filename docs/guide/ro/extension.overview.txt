Generalitati
============

Extinderea platformei Yii este o activitate obisnuita in timpul dezvoltarii.
De exemplu, daca scriem un nou controller, extindem Yii prin derivarea
clasei sale [CController]. In cazul unui nou widget, derivam [CWidget].
Daca scriem cod care este proiectat sa fie refolosit de catre alti programatori,
atunci denumim acest cod *extensie*.

O extensie de obicei foloseste pentru un singur scop. In termenii platformei Yii,
o extensie poate fi clasificata in felul urmator:

 * [componenta de aplicatie](/doc/guide/basics.application#application-component)
 * [widget](/doc/guide/basics.view#widget)
 * [controller](/doc/guide/basics.controller)
 * [action](/doc/guide/basics.controller#action)
 * [filtru](/doc/guide/basics.controller#filter)
 * [comanda de consola](/doc/guide/topics.console)
 * validator: un validator este o clasa de componenta derivata din [CValidator].
 * helper: un helper este o clasa care contine doar metode statice. Folosim metodele ca niste
   functii globale impreuna cu numele clasei din care apartin ca namespace.
 * [modul](/doc/guide/basics.module): un modul este o unitate de sine statatoare care este
 formata din [modele](/doc/guide/basics.model), [view-uri](/doc/guide/basics.view),
 [controllere](/doc/guide/basics.controller) si alte componente suportate.
 In multe privinte, un modul seamana cu o [aplicatie](/doc/guide/basics.application).
 Singura diferenta este ca un modul este in interiorul unei aplicatii.
 De exemplu, putem putem avea un modul care pune la dispozitie functionalitati de gestiune utilizatori.

O extensie poate fi de asemenea o componenta care nu apartine nici unei categorii de mai sus.
De fapt, platforma Yii este proiectata foarte atent pentru a permite extinderea
oricarei parti din codul sau pentru a fi potrivita fiecarei nevoi individuale.

<div class="revision">$Id: extension.overview.txt 759 2009-02-26 21:23:53Z qiang.xue $</div>