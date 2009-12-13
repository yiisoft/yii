Composant
=========

Les applications Yii sont construites sur des composants.
Un composant est une instance de [CComponent] ou d'une de ses classes dérivées.
L'utilisation d'un composant implique l'accès à ses propriétés et la gestion de
ses événements. La classe de base [CComponent] spécifie comment définir les
propriétés et les événements.

Propriété d'un composant
------------------------

Une propriété d'un composant fonctionne comme les propriétés publique d'un
objet. Il est possible d'y lire ou d'y assigner une valeur. Par exemple,

~~~
[php]
$width=$component->textWidth;     // récupère la propriété textWidth
$component->enableCaching=true;   // défini la propriété enableCaching
~~~

Pour définir une propriété, il est possible soit de déclarer une
variable publique au sein de la classe du composant, soit de définir
les getter et setter correspondant comme ceci:

~~~
[php]
public function getTextWidth()
{
    return $this->_textWidth;
}

public function setTextWidth($value)
{
    $this->_textWidth=$value;
}
~~~

Le fragment de code ci dessus défini un accès en lecture/écriture
à la propriété nommée `textWidth` (le nom n'est pas sensible à la casse).
Lors de la lecture de la propriété, `getTextWidth()` est appelé et la
valeur retournée devient la valeur de la propriété; le fonctionnement est
similaire lorsque l'on y accède en écriture, la méthode `setTextWidth()` est
appelé. Si le setter n'est pas défini, la propriété est accessible en
lecture seule et un accès en écriture lève une exception. L'utilisation
de getter et setter à l'avantage de permettre d'insérer de la logique de traitement
lors de l'accès à la propriété (e.g. effectuer une validation, lever un événement).


>Attention: Il existe une légère différence entre une propriété définie
par un getter/setter et une variable de classe. Lors de l'utilisation d'un
getter/setter, le nom de la propriété est insensible à la casse ce qui n'est
pas le cas pour une variable de classe.

Evénement et composant
----------------------

Les évènements d'un composant sont des propriétés spéciales qui prennent pour
valeur des méthodes (appelées `gestionnaires d'évènements`). Le fait d'attacher
une méthode à un évènement va permettre de l'invoquer dès que l'évènement
est levé. Il faut donc être attentif car cette gestion d'évènement peu
modifier le comportement du composant de manière imprévisible.

Un évènement est défini par une méthode dont le nom commence par `on`.
Comme pour les propriétés définies par getter/setter, les noms des
évènements ne sont pas sensible à la casse. Le code suivant défini
un évènement `onClicked`:

~~~
[php]
public function onClicked($event)
{
	$this->raiseEvent('onClicked', $event);
}
~~~

ou `$event` est une instance de [CEvent] ou de l'une de ses classes filles.

Une méthode est attachée à un évènement de la façon suivante:

~~~
[php]
$component->onClicked=$callback;
~~~

ou `$callback` est une fonction de rappel PHP valide. Ce peut être une fonction
globale ou une méthode de classe. Dans ce cas (méthode de classe), la fonction
de rappel doit être donnée via un array: `array($object,'methodName')`.

La signature d'un gestionnaire d'évènement doit être:

~~~
[php]
function methodName($event)
{
    ......
}
~~~

ou le paramètre `$event` décrit l'évènement (il provient de l'appel de
`raiseEvent()`). Le paramètre `$event` doit être une instance de [CEvent]
ou de l'une de ses classes dérivées. A minima, il doit permettre de
savoir qui à levé l'évènement.

A partir de la version 1.0.10, un gestionnaire d'évènement peut être une fonction anonyme (ou lambda function)
ce qui est supporté depuis PHP 5.3. Par exemple,

~~~
[php]
$component->onClicked=function($event) {
	......
}
~~~


Désormais, si l'on appelle `onClicked()`, l'évènement `onClicked` va être
levé (a l'intérieur de `onClicked()`), et le gestionnaire d'évènement
associé va être invoqué automatiquement.

Un évènement peut être attaché à plusieurs gestionnaires. Lorsqu'un évènement
est levé, les gestionnaires seront invoqués dans l'ordre ou ils ont été attachés.
Si un gestionnaire doit interdire l'invocation des autres gestionnaires, il lui
suffit de mettre la propriété [$event->handled|CEvent::handled] à true.


Comportement d'un composant
---------------------------

A partir de la version 1.0.2, le support des [mixin](http://en.wikipedia.org/wiki/Mixin) a été
ajouté pour permettre d'attacher un composant à plusieurs comportements.
Un *comportement* (behavior) est un objet dont les méthodes peuvent être
'hérités' par les composants qui y sont attachés. L'idée étant de collecter
des fonctionnalités au lieu de spécialiser les composants (i.e., notion
d'héritage classique). Un composant peut donc être attaché à plusieurs
composants pour simuler 'l'héritage multiple'.

Les classes de comportement doivent implémenter l'interface [IBehavior].
La plupart des comportements peuvent directement étendre la classe de
base [CBehavior]. Si un comportement doit être attaché à un
[modèle](/doc/guide/basics.model), il est possible de le faire hériter
de [CFormBehavior] ou [CActiveRecordBehavior] qui implémentent des
fonctionnalités spécifiques à la gestion des modèles.

Pour pouvoir utiliser un comportement, il doit tout d'abord être attaché
à un composant en appelant la méthode [attach()|IBehavior::attach]. Ensuite,
le comportement peut appelé via le composant comme ceci:

~~~
[php]
// $name identifie de façon unique le comportement du composant
$behavior->attach($name,$component);
// test() est une méthode de $behavior
$component->test();
~~~

Un comportement attaché à un composant peut être accédé comme une propriété
classique. Par exemple, si le comportement `tree` est attaché au composant
component, on peut obtenir la référence du comportement en utilisant:

~~~
[php]
$behavior=$component->tree;
// équivalent de la syntaxe
// $behavior=$component->asa('tree');
~~~

Un comportement peut être temporairement désactivé pour que ses méthodes
ne puissent être appelées par le composant.
Par exemple,

~~~
[php]
$component->disableBehavior($name);
// la ligne suivante va lever une exception
$component->test();
$component->enableBehavior($name);
// elle fonctionne désormais
$component->test();
~~~

Il est possible que deux comportements attachés au même composant aient des méthodes
avec un même nom. Dans ce cas, la méthode appartement au comportement qui a été
attaché en premier aura la précédence.

Lorsqu'ils sont utilisés avec les [évènements](#component-event), les comportements
peuvent être extrèmement puissant. Un comportement, lorsqu'il est affecté à un
composant, peut attacher certaines de ses méthodes à des évènements du composant.
Cela permet à un comportement d'observer et/ou de changer le cycle d'exécution du
composant.

Depuis la version 1.1.0, les propriétés d'un `comportement` sont accessibles directement depuis
le composant auquel il est rattaché. Les propriétés intègrent à la fois les variables publiques
et les propriétés du comportement définies par getter/setter. Par exemple, si un comportement
à une propriété `xyz` et que ce comportement est attaché à un composant `$a`. Alors il devient possible
d'utiliser l'expression `$a->xyz` pour accéder à la propriété du comportement.

<div class="revision">$Id: basics.component.txt 1474 $</div>