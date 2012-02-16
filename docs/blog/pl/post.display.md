Wyświetlanie wiadomości
================

W naszej aplikacji - blogu - wiadomość może być wyświetlana wśród listy wiadomości lub samodzielnie. Pierwszy przypadek zaimplementowany jest jako operacja `index`, drugi jako operacja `view`. W części tej, dostosujemy obie operacje, aby spełniały nasze początkowe wymagania.


Dostosowywanie operacji wyświetlania `view`
----------------------------

Operacja wyświetlania `view` zaimplementowana została poprzez metodę `actionView()` w kontrolerze `PostController`. To co wyświetla jest generowane przez widok `view` znajdujący się w pliku `/wwwroot/blog/protected/views/post/view.php`.

Poniżej znajduje się odpowiedni kod implementujący operację wyświetlania `view` w kontrolerze `PostController`:

~~~
[php]
public function actionView()
{
	$post=$this->loadModel();
	$this->render('view',array(
		'model'=>$post,
	));
}

private $_model;

public function loadModel()
{
	if($this->_model===null)
	{
		if(isset($_GET['id']))
		{
			if(Yii::app()->user->isGuest)
				$condition='status='.Post::STATUS_PUBLISHED
					.' OR status='.Post::STATUS_ARCHIVED;
			else
				$condition='';
			$this->_model=Post::model()->findByPk($_GET['id'], $condition);
		}
		if($this->_model===null)
			throw new CHttpException(404,'The requested page does not exist.');
	}
	return $this->_model;
}
~~~

Nasza zmiana dotyczy przede wszystkim metody `loadModel()`. W metodzie tej odpytujemy tabelę z wiadomościami `Post` w zależności od parametru `id` GET. Jeśli wiadomość nie została znaleziona lub jeśli nie jest ona opublikowana lub zarchiwizowana (gdy użytkownik jest gościem), rzucimy błędem HTTP 404. W przeciwnym przypadku obiekt wiadomości jest zwracany do metody `actionView()`, która z kolei przekazuje obiekt wiadomości do widoku `view` w celu późniejszego jej wyświetlenia.

> Tip|Wskazówka: Yii przechwytuje wyjątki HTTP (instancje klasy [CHttpException]) i wyświetla je zarówno przy użyciu zdefiniowanych szablonów czy też widoków błędów. Szkielet aplikacji wygenerowany przez narzędzie `yiic` zawiera już niestandardowy widok błędów w `/wwwroot/blog/protected/views/site/error.php`. Możemy zmodyfikować ten plik, jeśli mamy taką potrzebę, później jeśli chcemy zmienić sposób wyświetlania błędów.

Zmiana w skrypcie `view` dotyczy przede wszystkim dostosowania formatowania oraz stylów wyświetlania wiadomości. Nie będziemy tutaj wdawać się w szczegóły. Zainteresowani, mogą zajrzeć do pliku `/wwwroot/blog/protected/views/post/view.php`.


Dostosowywanie operacji wyświetlenia listy `index`
----------------------------

Podobnie jak operację wyświetlenia `view`, dostosujemy operację wyświetlenia listy `index` w dwóch miejscach: metodzie `actionIndex()` w kontrolerze `PostController` oraz pliku widoku  `/wwwroot/blog/protected/views/post/index.php`. Musimy przede wszystkim dodać wsparcie dla wyświetlania listy wiadomości, które powiązane są z określonym tagiem.

Poniżej znajduje się zmieniona metoda `actionIndex()` kontrolera `PostController`:

~~~
[php]
public function actionIndex()
{
	$criteria=new CDbCriteria(array(
		'condition'=>'status='.Post::STATUS_PUBLISHED,
		'order'=>'update_time DESC',
		'with'=>'commentCount',
	));
	if(isset($_GET['tag']))
		$criteria->addSearchCondition('tags',$_GET['tag']);

	$dataProvider=new CActiveDataProvider('Post', array(
		'pagination'=>array(
			'pageSize'=>5,
		),
		'criteria'=>$criteria,
	));

	$this->render('index',array(
		'dataProvider'=>$dataProvider,
	));
}
~~~

W powyższym kodzie, tworzymy najpierw kryteria zapytania służące otrzymaniu listy wiadomości. Kryteria te określają, iż tylko opublikowane wiadomości powinny zostać zwrócone i posortowane zgodnie z datą ostatniej modyfikacji w porządku malejącym. Ponieważ wyświetlając listę wiadomości chcemy wiedzieć ile każda z nich otrzymała komentarzy, podajemy w kryteriach iż chcemy zwrócić `commentCount`, które to jak zapewne pamiętasz, jest relacją zadeklarowaną w `Post::relations()`.

W przypadku kiedy użytkownik chce zobaczyć wiadomości z określonym tagiem, dodamy warunek przeszukiwania do kryteriów uwzględniający ten tag.

Używając kryteria zapytań, tworzymy dostawcę danych (ang. data provider), który służy głównie do trzech celów. Po pierwsze, dzieli dane na strony jeśli zbyt wiele wyników zostanie zwróconych. Tutaj dostosowujemy stronicowanie poprzez ustawienie rozmiaru strony równego 5. Po drugie, sortuje on w zależności od żądania użytkownika. I wreszcie, dostarcza podzielone na strony oraz posortowane dane do widżetu lub kodu widoku w celu wyświetlenia. 

Po zakończeniu prac z metodą `actionIndex()`, możemy przejść do modyfikacji widoku `index`. Nasze zmiany wiążą się głównie z dodaniem nagłówka `h1` jeśli użytkownik określił, że chce wyświetlić wiadomości o określonym tagu. 

~~~
[php]
<?php if(!empty($_GET['tag'])): ?>
<h1>Posts Tagged with <i><?php echo CHtml::encode($_GET['tag']); ?></i></h1>
<?php endif; ?>

<?php $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'_view',
	'template'=>"{items}\n{pager}",
)); ?>
~~~

Zauważ, że w powyższym kodzie używamy [CListView] do wyświetlenia listy wiadomości. Widżet ten potrzebuje częściowego widoku do wyświetlenia szczegółów pojedynczych wiadomości. Tutaj określiliśmy ten częściowy widok jako `_view`, co wskazuje na plik `/wwwroot/blog/protected/views/post/_view.php`. W tym pliku widoku, mamy dostęp do instancji wiadomości, która jest wyświetlana poprzez lokalną zmienną o nazwie `$data`.

<div class="revision">$Id: post.display.txt 2121 2010-05-10 01:31:30Z qiang.xue $</div>