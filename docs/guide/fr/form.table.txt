Collecte d'entrées tabulaires
=============================

Il peut être utile de récupérer des données utilisateur par lot (batch mode).
L'utilisateur peut par exemple entrer des informations dans de multiples
instances de modèles et les soumettre d'une seule fois. On appelle cela une
entrée tabulaire car les champs sont souvent présentés dans une table HTML.

Pour utiliser les entrées tabulaires, il faut d'abord créer ou remplir un
tableau d'instances de modèle. On récupère alors les données saisies par
l'utilisateur dans la variable `$_POST` et on les assignent à chaque modèle.
Une différence par rapport à la saisie pour un modèle unique est de récupérer
les données en utilisant `$_POST['ModelClass'][$i]` à la place de `$_POST['ModelClass']`.

~~~
[php]
public function actionBatchUpdate()
{
	// récupère les objets par lot
	// on assume ici que chaque objet est de modèle 'Item'
	$items=$this->getItemsToUpdate();
	if(isset($_POST['Item']))
	{
		$valid=true;
		foreach($items as $i=>$item)
		{
			if(isset($_POST['Item'][$i]))
				$item->attributes=$_POST['Item'][$i];
			$valid=$valid && $item->validate();
		}
		if($valid)  // tous les objets sont valides
			// ...faire quelque chose ici
	}
	// affiche la vue pour récupérer les entrées tabulaires
	$this->render('batchUpdate',array('items'=>$items));
}
~~~

Maintenant que l'action est prête, on peut travailler sur la vue
`batchUpdate` afin d'afficher les champs de saisie de la table HTML.

~~~
[php]
<div class="form">
<?php echo CHtml::beginForm(); ?>
<table>
<tr><th>Name</th><th>Price</th><th>Count</th><th>Description</th></tr>
<?php foreach($items as $i=>$item): ?>
<tr>
<td><?php echo CHtml::activeTextField($item,"[$i]name"); ?></td>
<td><?php echo CHtml::activeTextField($item,"[$i]price"); ?></td>
<td><?php echo CHtml::activeTextField($item,"[$i]count"); ?></td>
<td><?php echo CHtml::activeTextArea($item,"[$i]description"); ?></td>
</tr>
<?php endforeach; ?>
</table>

<?php echo CHtml::submitButton('Save'); ?>
<?php echo CHtml::endForm(); ?>
</div><!-- form -->
~~~

Veuillez notez que `"[$i]name"` est utilisé ci dessus à la place de `"name"`
en tant que deuxième paramètre lors de l'appel à [CHtml::activeTextField].

Si il y des erreurs de validation, les champs de saisies correspondant
seront mis en valeurs (highlighted) automatiquement, de la même manière
que pour la saisie pour un modèle unique décrit précédemment.

<div class="revision">$Id: form.table.txt 2232 2010-06-26 22:42:03Z qiang.xue $</div>
