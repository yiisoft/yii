Dynamische Inhalte
==================

Cacht man [Fragmente](/doc/guide/caching.fragment) oder ganze 
[Seiten](/doc/guide/caching.page), merkt man oft, dass der Inhalt
- abgesehen von wenigen Stellen - relativ statisch bleibt. 
Eine Hilfeseite könnte z.B. statischen Hilfetext, sowie oben den aktuellen
Benutzernamen enthalten.

In diesem Fall könnte man den Cacheinhalt entsprechend dem angemeldeten
Benutzer variieren. Damit würde aber viel vertvoller Cachespeicher
verschwendet, da außer dem Benutzernamen immer der gleiche Inhalt abgelegt
würde. Man könnte die Seite auch in mehrere Bereiche trennen und diese
individuell speichern. Aber das würde eventuell den Code und den View
verkomplizieren. Ein besserer Ansatz wurde daher im CController-Feature der
*dynamischen Inhalte* verfolgt.

Ein dynamischer Inhalt steht für einen Bereich in der Ausgabe, der nicht
gecacht werden soll, selbst wenn er in einen gecachten Fragment
eingebettet ist. Damit dieser Inhalt dynamisch bleibt, muss er jedesmal
neu erzeugt werden, selbst wenn der umhüllende Inhalt aus dem Cache geliefert
wird. Aus diesem Grund ist es erforderlich, dass dynamischer Inhalt von einer 
Methode oder einer Funktion erzeugt wird.

Um dynamischen Inhalt einzufügen, wird an der gewünschten Stelle
[CController::renderDynamic()] aufgerufen:

~~~
[php]
...Anderer HTML-Inhalt...
<?php if($this->beginCache($id)) { ?>
...Zu cachendes Fragment ...
	<?php $this->renderDynamic($callback); ?>
...Zu cachendes Fragment ...
<?php $this->endCache(); } ?>
...Anderer HTML-Inhalt...
~~~


`$callback` bezieht sich oben auf einen gültigen PHP-Callback. Dies kann ein
String mit dem Namen einer globalen Funktion oder einer Methode in der aktuellen 
Controllerklasse sein. Es kann auch ein Array sein, der sich auf eine
Klassenmethode bezieht. Alle weiteren Parameter von
[renderDynamic()|CController::renderDynamic()] werden an den Callback
weitergegeben. Der Callback sollte den dynamischen Inhalt zurückliefern, statt
ihn auszugeben.

<div class="revision">$Id: caching.dynamic.txt 163 2008-11-05 12:51:48Z weizhuo $</div>
