Tworzenie portletu menu użytkownika
==========================

Bazując na analizie wymagań, będziemy potrzebowali trzech portletów: portletu "menu użytkownika", "chmury tagów" oraz portletu "ostatnich komentarzy". Utworzymy te portlety poprzez rozszerzenie widżetu [CPortlet] dostarczonego przez Yii.

W części tej, utworzymy nasz pierwszy, konkretny portlet - portlet menu użytkownika, który wyświetla listę pozycji w menu, które są dostępne tylko dla uwierzytelnionych użytkowników. Menu zawiera cztery pozycje:

 * zatwierdź komentarz: hiperłącze, które prowadzi do lity komentarzy czekających na zatwierdzenie;
 * utwórz nową wiadomość: hiperłącze, które prowadzi do strony tworzenia wiadomości;
 * zarządzanie wiadomościami: hiperłącze, które prowadzi do strony zarządzania wiadomościami;
 * wylogowanie: link będący przyciskiem, który wyloguje aktualnego użytkownika;


Tworzenie klasy `UserMenu`
-------------------------

Tworzymy klasę `UserMenu` opisującą część logiczną portletu menu użytkownika. Klasa ta jest zachowana w pliku `/wwwroot/blog/protected/components/UserMenu.php`, który posiada następującą zawartość:

~~~
[php]
Yii::import('zii.widgets.CPortlet');

class UserMenu extends CPortlet
{
	public function init()
	{
		$this->title=CHtml::encode(Yii::app()->user->name);
		parent::init();
	}

	protected function renderContent()
	{
		$this->render('userMenu');
	}
}
~~~

Klasa `UserMenu` dziedziczy z klasy `CPortlet` z biblioteki `zii`. Nadpisuje zarówno metodę `init()` jak i metodę `renderContent()` z klasy `CPortlet`. Pierwsza metoda ustawia tytuł jako nazwę aktualnego użytkownika; druga generuje zawartość ciała portletu poprzez wygenerowanie widoku o nazwie `userMenu`.

> Tip|Wskazówka: Zauważ, że dołączamy jawnie klasę `CPortlet` poprzez wywołanie metody `Yii::import()` zanim odwołamy się do niej po raz pierwszy. Dzieje się tak, ponieważ `CPortlet` jest częścią projektu `zii` - oficjalnej biblioteki rozszerzeń dla Yii. Ze względu na wydajność, klasy w tym projekcie nie są wymieniane jako klasy podstawowe (ang. core classes). Dlatego też, musimy je zaimportować zanim ich użyjemy po raz pierwszy. 


Tworzenie widoku `userMenu`
------------------------

Następnie, tworzymy widok `userMenu`, który jest zapisany w pliku `/wwwroot/blog/protected/components/views/userMenu.php`:

~~~
[php]
<ul>
	<li><?php echo CHtml::link('Create New Post',array('post/create')); ?></li>
	<li><?php echo CHtml::link('Manage Posts',array('/admin')); ?></li>
	<li><?php echo CHtml::link('Approve Comments',array('comment/index'))
		. ' (' . Comment::model()->pendingCommentCount . ')'; ?></li>
	<li><?php echo CHtml::link('Logout',array('site/logout')); ?></li>
</ul>
~~~

> Info|Info: Domyślnie, pliki widoku dla widżetu powinny się znajdować w podkatalogu `views` katalogu zawierającego klasę widżetu. Nazwa pliku powinna być taka sam jak nazwa widoku. 


Używanie portletu `UserMenu`
------------------------

Nadszedł już czas, aby użyć nasz nowo ukończony portlet `UserMenu`. Zmienimy plik widoku układu `/wwwroot/blog/protected/views/layouts/column2.php` następująco:

~~~
[php]
......
<div id="sidebar">
	<?php if(!Yii::app()->user->isGuest) $this->widget('UserMenu'); ?>
</div>
......
~~~

W powyższym kodzie wołamy metodę `widget()` w celu wygenerowania i wywołania instancji klasy `UserMenu`. Ponieważ portlet powinien być wyświetlany tylko dla uwierzytelnionych użytkowników, wywołujemy metodę `widget()` jeśli wartość właściwości `isGuest` dla aktualnego użytkownika to false (co oznacza, że użytkownik został uwierzytelniony).


Testowanie portletu `UserMenu`
--------------------------

Przetestujmy to co dotychczas mamy.

 1. Otwórz okno przeglądarki i wprowadź URL `http://www.example.com/blog/index.php`. Upewnij się, że nic nie wyświetla się na pasku bocznym tej strony.
 2. Kliknij na hiperłącze `Login` i wypełnij formularz logowania. Jeśli zakończy się to sukcesem, sprawdź czy portlet `UserMenu` pojawił się na pasku bocznym i czy jego tytuł zawiera nazwę użytkownika.
 3. Kliknij na hiperłącze 'Logout w portlecie `UserMenu`. Sprawdź czy akcja wylogowania powiodła się i portlet `UserMenu` portlet zniknął.


Podsumowanie
-------

To co stworzyliśmy to portlet, który można wielokrotnie, ponownie używać. Możemy go w prosty sposób użyć ponownie w innych projektach z drobnymi lub bez żadnych modyfikacji. Więcej, zaprojektowanie tego portletu zgadza się ściśle z filozofią mówiącą, że warstwa logiczna i prezentacyjna powinny być rozdzielone. Chociaż nie zwróciliśmy na to uwagi w poprzednich częściach, praktyka taka ma miejsce niemal wszędzie w typowych aplikacjach opartych na Yii.

<div class="revision">$Id: portlet.menu.txt 1739 2010-01-22 15:20:03Z qiang.xue $</div>