Administrowanie komentarzami
=================

Administrowanie komentarzami zawiera aktualizowanie, usuwanie oraz zatwierdzanie komentarzy. Operacje te są zaimplementowane jako akcje w klasie kontrolera `CommentController`.


Aktualizowanie i usuwanie komentarzy
------------------------------

Kod wygenerowany przez narzędzie `yiic` do aktualizowania oraz usuwania komentarzy pozostaje w większej części niezmieniony.


Zatwierdzanie komentarzy
------------------

Kiedy komentarz jest nowo utworzony, oczekuje on na zatwierdzenie i musi zostać zatwierdzony jeśli ma on być widoczny dla gości. Zatwierdzanie komentarza to przede wszystkim zmiana kolumny zawierającej status komentarza.

Utworzymy metodę `actionApprove()` w kontrolerze `CommentController` następująco:

~~~
[php]
public function actionApprove()
{
	if(Yii::app()->request->isPostRequest)
	{
		$comment=$this->loadModel();
		$comment->approve();
		$this->redirect(array('index'));
	}
	else
		throw new CHttpException(400,'Invalid request...');
}
~~~

W powyższym kodzie, gdy akcja zatwierdzenia `approve` jest wywoływana poprzez żądanie POST, wywołujemy metodę `approve()` zdefiniowaną w modelu komentarza `Comment` aby zmienić status. Następnie przekierowujemy przeglądarkę użytkownika do strony wyświetlającej wiadomość, do której należy ten komentarz.

Oczywiście musimy najpierw utworzyć metodę `approve()` w modelu komentarza `Comment`. Wygląda ona następująco:

~~~
[php]
public function approve()
{
	$this->status=Comment::STATUS_APPROVED;
	$this->update(array('status'));
}
~~~

Po prostu ustawiamy w niej właściwość status modelu komentarza na wartość `approved` określoną przez stałe statusu znajdujące się w klasie `Comment`:

~~~
[php]
class Comment extends CActiveRecord
{
	...

	const STATUS_PENDING=1;
	const STATUS_APPROVED=2;

	..
}
~~~

a następnie wywołujemy metodę `update()` aby zapisać tę nowo ustawioną właściwość do bazy danych.

Modyfikujemy również metodę `actionIndex()` kontrolera `CommentController` aby wyświetlała listę wszystkich komentarzy. Na samym początku chcielibyśmy wyświetlić komentarze wymagające zatwierdzenia. 

~~~
[php]
public function actionIndex()
{
	$dataProvider=new CActiveDataProvider('Comment', array(
		'criteria'=>array(
			'with'=>'post',
			'order'=>'t.status, t.create_time DESC',
		),
	));

	$this->render('index',array(
		'dataProvider'=>$dataProvider,
	));
}
~~~

Zauważ, że w powyższym kodzie, ze względu na to, że obie tabele `tbl_post` oraz `tbl_comment` posiadają kolumnę statusu `status` i czasu utworzenia `create_time`, potrzebujemy rozróżnić odpowiednie referencje do kolumny poprzez dodanie im prefiksu z aliasem nazwy tablicy. Tak jak to opisano w [poradniku](http://www.yiiframework.com/doc/guide/database.arr#disambiguating-column-names), alias dla głównego klucza tabeli w tabeli relacyjnej zawsze ma wartość `t`. Dlatego też dodaliśmy w powyższym kodzie prefiks `t` do kolumn statusu `status` oraz czasu utworzenia `create_time` aby zasygnalizować, że chcemy pobrać te wartości z tabeli podstawowej `tbl_comment`.

Tak jak widok index dla wiadomości, tak i widok `index` dla `CommentController` używa klasy [CListView] aby wyświetlić listę komentarzy, która to z kolei używa częściowego widoku `/wwwroot/blog/protected/views/comment/_view.php` aby wyświetlić szczegóły poszczególnych komentarzy. Nie będziemy tutaj wchodzić w szczegóły. Zainteresowani czytelnicy mogą zajrzeć do odpowiednich plików w demo blogu `/wwwroot/yii/demos/blog/protected/views/comment/_view.php`.

<div class="revision">$Id: comment.admin.txt 3480 2011-12-13 03:12:21Z jefftulsa $</div>