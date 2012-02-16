Cache de page
=============

Le cache de page sert à mettre en cache l'intégralité d'une page. Le cache de page 
peut être mis en oeuvre à différents niveaux.
Par exemple, en utilisant un header approprié, le navigateur peut mettre en 
cache la page en cours pour une durée définie ou encore, l'application web peut
prendre en charge la mise en cache du contenu de la page.
Dans cette section, nous présenterons cette dernière méthode.

Le cache de page peut être considéré comme une extension du [cache par 
fragments](/doc/guide/caching.fragment). Sachant que généralement
la génération d'une page fait intervenir un layout et une vue, il n'est
pas possible d'appeler les méthodes [beginCache()|CBaseController::beginCache] et
[endCache()|CBaseController::endCache] directement dans le layout. Cela est
du au fait que le layout est appliqué dans la méthode [CController::render()]
une fois que la vue a été évaluée.

Pour cacher l'intégralité d'une page, il faut éviter l'exécution de
l'action qui génère le contenu de la page. Pour ce faire,
nous pouvons utiliser [COutputCache] en tant que 
[filtre](/doc/guide/basics.controller#filter). Le code suivant montre
comment configurer le filtre de cache :

~~~
[php]
public function filters()
{
	return array(
		array(
			'COutputCache',
			'duration'=>100,
			'varyByParam'=>array('id'),
		),
	);
}
~~~

La configuration du filtre ci-dessus sera appliqué à toutes les actions
du contrôleur. Nous pouvons limiter la portée du cache en utilisant
l'opérateur "+". Plus de détails peuvent être trouvés dans la section
[filtre](/doc/guide/basics.controller#filter).

> Tip: Il est possible d'utiliser [COutputCache] en tant que filtre
car il dérive de [CFilterWidget], ce qui signifie que c'est à la fois
un filtre et un widget. En réalité un widget et un filtre sont très proches :
un widget (filtre) commence avant qu'un contenu (action) soit évalué et le
widget (filtre) se termine une fois que le contenu entre les
balises (action) est évalué.

<div class="revision">$Id: caching.page.txt 1014 2009-05-10 12:25:55Z qiang.xue $</div>