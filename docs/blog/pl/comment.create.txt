Tworzenie i wyświetlanie komentarzy
================================

W części tej zaimplementujemy funkcje wyświetlania oraz tworzenia komentarzy.

W celu zwiększenia interakcji z użytkownikiem będziemy chcieli poinformować użytkownika o możliwych błędach za każdym razem gdy skończy on wprowadzać wartości do danego pola. Znane jest to pod pojęciem sprawdzania poprawności danych po stronie klienta. Pokażemy jak można to zrobić w Yii w sposób nierozróżnialny i ekstremalnie prosty. Zauważ, że wymagana jest do tego wersja Yii 1.1.1 lub późniejsza. 


Wyświetlanie komentarzy
-------------------

Zamiast osobnych stron do wyświetlania i tworzenia komentarzy użyjemy strony wyświetlającej szczegóły wiadomości (wygenerowanej przez akcję `view` w kontrolerze `PostController`). Poniżej wyświetlonej zawartości każdej wiadomości, wyświetlimy listę komentarzy należących do tej wiadomości a pod nimi formularz tworzenia komentarza.

W celu wyświetlenia komentarzy na stronie szczegółów wiadomości, zmodyfikujemy skrypt widoku `/wwwroot/blog/protected/views/post/view.php` w następujący sposób:

~~~
[php]
...widok posta...

<div id="comments">
	<?php if($model->commentCount>=1): ?>
		<h3>
			<?php echo $model->commentCount . 'comment(s)'; ?>
		</h3>

		<?php $this->renderPartial('_comments',array(
			'post'=>$model,
			'comments'=>$model->comments,
		)); ?>
	<?php endif; ?>
</div>
~~~

W powyższym kodzie, wywołujemy metodę `renderPartial()` aby wygenerować częściowy widok o nazwie `_comments` w celu wyświetlenia listy komentarzy aktualnie wyświetlanej wiadomości. Zauważ, że w widoku używamy wyrażenia `$model->comments` w celu zwrócenia komentarzy dla tej wiadomości. Jest to poprawne wywołanie dlatego, że zadeklarowaliśmy relację `comments` w klasie wiadomości `Post`.  Wykonanie tego wyrażenia spowoduje niejawne wywołanie zapytania do bazy danych z JOIN-em, które zwróci nam odpowiednie komentarze. Funkcjonalność ta znana jest jako [leniwe zapytania relacyjne](http://www.yiiframework.com/doc/guide/database.arr).

Częściowy widok `_comments` nie jest zbyt interesujący. Głównie przechodzi on przez wszystkie komentarze i wyświetla ich szczegóły. Zainteresowanych czytelników odsyłamy do pliku `/wwwroot/yii/demos/blog/protected/views/post/_comments.php`.


Tworzenie komentarzy
-----------------

Aby obsłużyć tworzenie komentarzy, najpierw zmienimy metodę `actionView()` kontrolera `PostController` w następujący sposób:

~~~
[php]
public function actionView()
{
	$post=$this->loadModel();
	$comment=$this->newComment($post);

	$this->render('view',array(
		'model'=>$post,
		'comment'=>$comment,
	));
}

protected function newComment($post)
{
	$comment=new Comment;
	if(isset($_POST['Comment']))
	{
		$comment->attributes=$_POST['Comment'];
		if($post->addComment($comment))
		{
			if($comment->status==Comment::STATUS_PENDING)
				Yii::app()->user->setFlash('commentSubmitted','Thank you for your comment. Your comment will be posted once it is approved.');
			$this->refresh();
		}
	}
	return $comment;
}
~~~

Następnie modyfikujemy klasę modelu Post` dodając następującą metodę `addComment()`:

~~~
[php]
public function addComment($comment)
{
	if(Yii::app()->params['commentNeedApproval'])
		$comment->status=Comment::STATUS_PENDING;
	else
		$comment->status=Comment::STATUS_APPROVED;
	$comment->post_id=$this->id;
	return $comment->save();
}
~~~

W powyższym kodzie, wołamy metodę `newComment()` zanim wygenerujemy widok `view`. W metodzie `newComment()` generujemy instancję komentarza `Comment` i sprawdzamy czy formularz komentarza został przesłany. Jeśli tak, spróbujemy dodać komentarz do wiadomości poprzez wywołanie `$post->addComment($comment)`. Jeżeli się to uda, odświeżymy stronę ze szczegółami wiadomości, która będzie wyświetlać nam nowo utworzony komentarz chyba, że będzie wymagane jego potwierdzenie. W przypadku kiedy komentarz wymaga najpierw zatwierdzenia zanim zostanie wyświetlony, wyświetlimy wiadomość flash w celu zakomunikowania użytkownikowi, że komentarz zostanie wyświetlony po jego zatwierdzeniu. Wiadomość typu flash jest zazwyczaj wiadomością potwierdzającą coś, wyświetlaną użytkownikowi końcowemu. Jeśli użytkownik kliknie na przycisku odświeżenia w swojej przeglądarce wiadomość ta zniknie.

Musimy ponadto zmodyfikować również `/wwwroot/blog/protected/views/post/view.php`:

~~~
[php]
......
<div id="comments">
	......
	<h3>Leave a Comment</h3>

	<?php if(Yii::app()->user->hasFlash('commentSubmitted')): ?>
		<div class="flash-success">
			<?php echo Yii::app()->user->getFlash('commentSubmitted'); ?>
		</div>
	<?php else: ?>
		<?php $this->renderPartial('/comment/_form',array(
			'model'=>$comment,
		)); ?>
	<?php endif; ?>

</div><!-- comments -->
~~~

W powyższym kodzie wyświetlamy wiadomość typu flash jeśli jest dostępna. W przeciwnym przypadku, wyświetlamy formularz komentarza poprzez wygenerowanie widoku częściowego `/wwwroot/blog/protected/views/comment/_form.php`.


Sprawdzanie poprawności danych oparte na AJAX
----------------------

W celu poprawy wygody użytkownika, możemy użyć sprawdzania poprawności danych w polach formularza opartego na AJAX-ie, zapewniającego użytkownikowi informację zwrotną o poprawności pól w trakcie wypełniania formularza, przed przekazaniem całego formularza do serwera. Aby wspierać sprawdzanie poprawności oparte na AJAX-ie w formularzu komentarza, potrzebujemy dokonać niewielkich zmian zarówno w widoku formularza `/wwwroot/blog/protected/views/comment/_form.php` jak i w metodzie `newComment()`.

W pliku `_form.php` musimy przede wszystkim ustawić [CActiveForm::enableAjaxValidation] jako wartość true, kiedy tworzymy widżet [CActiveForm]:

~~~
[php]
<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'comment-form',
	'enableAjaxValidation'=>true,
)); ?>
......
<?php $this->endWidget(); ?>

</div><!-- form -->
~~~

Natomiast w metodzie `newComment()` wstawiamy część kodu aby odpowiedzieć na żądanie AJAX-a dotyczące sprawdzania poprawności. Kod sprawdza, czy w `POST` występuje zmienna `ajax`. Jeśli tak, wyświetlany jest rezultat sprawdzania poprawności poprzez wywołanie [CActiveForm::validate].

~~~
[php]
protected function newComment($post)
{
	$comment=new Comment;

	if(isset($_POST['ajax']) && $_POST['ajax']==='comment-form')
	{
		echo CActiveForm::validate($comment);
		Yii::app()->end();
	}

	if(isset($_POST['Comment']))
	{
		$comment->attributes=$_POST['Comment'];
		if($post->addComment($comment))
		{
			if($comment->status==Comment::STATUS_PENDING)
				Yii::app()->user->setFlash('commentSubmitted','Thank you for your comment. Your comment will be posted once it is approved.');
			$this->refresh();
		}
	}
	return $comment;
}
~~~

<div class="revision">$Id: comment.create.txt 3495 2011-12-19 03:37:41Z jefftulsa $</div>