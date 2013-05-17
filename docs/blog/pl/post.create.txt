Tworzenie i aktualizowanie wiadomości
===========================

Mając gotowy model wiadomości `Post`, potrzebujemy dopracować akcje oraz widoki dla kontrolera wiadomości `PostController`. W części tej najpierw dostosujemy kontrolę dostępności operacji CRUD; następnie zmodyfikujemy kod implementujący operacje tworzenia `create` oraz aktualizowania `update`.


Dostosowywanie kontroli dostępności
--------------------------

Pierwszą rzeczą jaką chcemy zrobić jest dostosowanie [kontroli dostępu](http://www.yiiframework.com/doc/guide/topics.auth#access-control-filter) ze względu na to, że kod wygenerowany przez `gii` nie pasuje do naszych wymagań.

Modyfikujemy metodę `accessRules()` w pliku `/wwwroot/blog/protected/controllers/PostController.php` w następujący sposób:

~~~
[php]
public function accessRules()
{
	return array(
		array('allow',  // allow all users to perform 'list' and 'show' actions
			'actions'=>array('index', 'view'),
			'users'=>array('*'),
		),
		array('allow', // allow authenticated users to perform any action
			'users'=>array('@'),
		),
		array('deny',  // deny all users
			'users'=>array('*'),
		),
	);
}
~~~

Powyższe reguły oznaczają, że wszyscy użytkownicy mogą uzyskać dostęp do akcji listowania `index` oraz wyświetlania `view`, zaś uwierzytelnieni użytkownicy mogą uzyskać dostęp do każdej akcji, włączając w to akcję administratora `admin`. Użytkownik powinien spotkać się z odmową dla każdego innego scenariusza. Zauważ, że reguły te przetwarzane są w kolejności w jakiej zostały tutaj pokazane. Pierwsza reguła pasująca do aktualnego kontekstu decyduje o uzyskaniu dostępu. Na przykład, jeśli aktualny użytkownik to właściciel systemu, który próbuje odwiedzić stronę służącą do tworzenia wiadomości, druga reguła będzie pasowała i zagwarantuje ona dostęp dla tego użytkownika.


Dostosowywanie operacji tworzenia `create` oraz aktualizacji `update`
--------------------------------------------

Operacje tworzenia `create` i aktualizacji `update` są bardzo podobne. Obie potrzebują wyświetlić formularz HTML w celu zebrania danych wejściowych od użytkownika, sprawdzenia ich poprawności i zapisania ich w bazie danych. Główną różnicą jest to, że operacja aktualizacji `update` wypełni wstępnie formularz danymi z istniejącej wiadomości znalezionymi w bazie danych. Z tego też powodu, `gii` generuje częściowy widok `/wwwroot/blog/protected/views/post/_form.php`, który jest osadzany w obu widokach `create` oraz `update` w celu wygenerowania potrzebnego formularza HTML.

Na początek zmienimy plik `_form.php`, tak że formularz HTML będzie zbierał jedynie dane wejściowe, które chcemy: tytuł `title`, zawartość `content`, tagi `tags` oraz status `status`. Używamy pól ze zwykłym tekstem aby zebrać dane wejściowe dla pierwszych trzech atrybutów oraz listy rozwijanej do zebrania danych wejściowych dla statusu `status`. Opcje listy rozwijanej stanowią teksty wyświetlające dopuszczalne statusy wiadomości:

~~~
[php]
<?php echo $form->dropDownList($model,'status',Lookup::items('PostStatus')); ?>
~~~

W powyższym kodzie wywołujemy `Lookup::items('PostStatus')` aby zwrócić listę statusów postu. 

Następnie zmodyfikujemy klasę wiadomości `Post`, tak, że będzie ona automatycznie ustawiać pewne atrybuty (np. czas utworzenia `create_time`, ID autora `author_id`) zanim wiadomość zapisywana jest do bazy danych. Nadpisujemy metodę `beforeSave()` następująco:

~~~
[php]
protected function beforeSave()
{
	if(parent::beforeSave())
	{
		if($this->isNewRecord)
		{
			$this->create_time=$this->update_time=time();
			$this->author_id=Yii::app()->user->id;
		}
		else
			$this->update_time=time();
		return true;
	}
	else
		return false;
}
~~~

Podczas zapisywania wiadomości, chcemy zaktualizować tabelę `tbl_tag` aby odzwierciedlić zmiany w częstotliwości występowań tagów. Możemy to uczynić w metodzie `afterSave()`, która jest automatycznie wołana przez Yii po tym jak wiadomość została poprawnie zapisana do bazy danych.  

~~~
[php]
protected function afterSave()
{
	parent::afterSave();
	Tag::model()->updateFrequency($this->_oldTags, $this->tags);
}

private $_oldTags;

protected function afterFind()
{
	parent::afterFind();
	$this->_oldTags=$this->tags;
}
~~~

W trakcie implementacji potrzebujemy wiedzieć jakie są stare tagi dlatego, że chcemy wykryć czy użytkownik zmienił tagi podczas aktualizacji istniejącej wiadomości. Z tego powodu napisaliśmy metodę `afterFind()` aby przechowywała stare tagi w zmiennej `_oldTags`. Metoda `afterFind()` jest wywoływana automatycznie przez Yii jeśli AR jest wypełniany danymi z bazy danych. 

Nie będziemy tutaj wchodzić w szczegóły metod `Tag::updateFrequency()`. Zainteresowani tym czytelnicy mogą zajrzeć do pliku `/wwwroot/yii/demos/blog/protected/models/Tag.php`.


<div class="revision">$Id: post.create.txt 3209 2011-05-12 12:11:03Z mdomba $</div>