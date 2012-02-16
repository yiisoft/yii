Odczytywanie tablicowych danych wejściowych
========================

Czasami mamy potrzebę zbierania danych wejściowych użytkownika w trybie wsadowym.
Inaczej mówiąc, użytkownik może wprowadzić informacje dla wielu instancji modelu 
oraz przesłać je wszystkie naraz. Nazywamy to *tablicowymi danymi wejściowymi* 
(ang. tabular input, ponieważ pola wejściowe są często prezentowane w tabeli HTML.

Aby pracować z tablicowymi danymi wejściowymi, musimy najpierw stworzyć lub też 
wypełnić tablice modeli instancji, w zależności od tego czy wstawiamy dane 
czy też aktualizujemy je. Otrzymujemy wtedy dane wejściowe użytkownika ze zmiennej 
`$_POST` oraz przypisujemy je do każdego modelu. Drobną różnicą pomiędzy w stosunku 
do danych wejściowych jednego modelu jest to, że otrzymujemy dana przy użyciu 
`$_POST['ModelClass'][$i]` zamiast `$_POST['ModelClass']`.

~~~
[php]
public function actionBatchUpdate()
{
  // zwracanie pozycji, które będą aktualizowane w trybie wsadowym
  // przy założeniu, że każda pozycja jest klasą modelu 'Item'
	$items=$this->getItemsToUpdate();
	if(isset($_POST['Item']))
	{
		$valid=true;
		foreach($items as $i=>$item)
		{
			if(isset($_POST['Item'][$i]))
				$item->attributes=$_POST['Item'][$i];
			$valid=$item->validate() && $valid;
		}
		if($valid)  // all items are valid
			// ...rób coś tutaj
	}
	// wyświetl widok do zbierania tabelarycznych danych wejściowych
	$this->render('batchUpdate',array('items'=>$items));
}
~~~

Mając gotową akcję, musimy popracować nad widokiem `batchUpdate` aby wyświetlić 
pola wejściowe za pomocą tabeli HTML.

~~~
[php]
<div class="yiiForm">
<?php echo CHtml::beginForm(); ?>
<table>
<tr><th>Nazwa</th><th>Cena</th><th>Ilość</th><th>Opis</th></tr>
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
</div><!-- yiiForm -->
~~~

Zauważ, że w powyższym przykładzie użyliśmy `"[$i]name"` zamiast `"name"` jako 
drugi parametr podczas wywoływania metody [CHtml::activeTextField].

Jeśli wystąpił tutaj jakikolwiek błąd sprawdzania danych, odpowiednie pole wejściowe
zostanie podświetlone automatycznie, tak jak to było w przypadku pojedynczego 
modelu opisanego wcześniej.

<div class="revision">$Id: form.table.txt 2783 2010-12-28 16:20:41Z qiang.xue $</div>