Widok
====

Widok jest skryptem PHP zawierającym przede wszystkim elementy interfejsu użytkownika.
Może on zawierać wyrażenia PHP, ale rekomendowane jest, aby te wyrażenia 
nie zmieniały danych modelu oraz były w miarę proste. W duchu rozdzielania 
logiki i prezentacji, duże porcje logiki powinny zostać umieszczone w kontrolerach
lub modelach zamiast w widokach.

Widok posiada nazwę, która jest używana do zidentyfikowania pliku skryptu widoku 
podczas generowania. Nazwa widoku to ta sama nazwa co nazwa pliku skryptu zawierającego
widok. Na przykład, widok `edit` odnosi się do pliku skryptu widoku nazwanego 
`edit.php`.  Aby wygenerować widok wywołaj metodę [CController::render()] wraz 
z nazwą widoku. Metoda ta będzie szukała odpowiadającego widokowi pliku w katalogu 
`protected/views/ControllerID`.

Wewnątrz skryptu widoku mamy dostęp do instancji kontrolera poprzez `$this`. 
Możemy w ten sposób `przeciągnąć` każdą właściwość kontrolera do widoku poprzez 
wywołanie `$this->nazwaWłaściwości`.

Możemy również użyć podejścia `pchnij` do przekazania danych do wyświetlenia:

~~~
[php]
$this->render('edit', array(
	'var1'=>$value1,
	'var2'=>$value2,
));
~~~

W powyższym kodzie, metoda [render()|CController::render] rozkoduje drugi parametr 
tablicy do zmiennych. W rezultacie, w skrypcie widoku uzyskamy dostęp do zmiennych 
lokalnych `$var1` oraz `$var2`.

Układ (ang. Layout)
------

Układ jest specjalnym widokiem, który jest używany do dekorowania widoku. 
Zazwyczaj zawiera części interfejsu użytkownika, które są wspólne dla różnych 
widoków. Na przykład, widok może zawierać część nagłówkową i stopkę oraz osadzać 
widok pomiędzy nimi, jak w poniższym kodzie:

~~~
[php]
......nagłówek......
<?php echo $content; ?>
......stopka......
~~~

gdzie `$content` przechowuje wynik generowania widoku.

Widok jest domyślnie stosowany podczas wołania metody [render()|CController::render].
Domyślnie, skrypt widoku `protected/views/layouts/main.php` jest używany jako widok.
Można to zmienić poprzez zmianę wartości [CWebApplication::layout] lub [CController::layout].
Aby wygenerować widok bez korzystania z żadnego widoku wywołaj w zamian metodę 
[renderPartial()|CController::renderPartial].

Widżety (ang. Widget)
------

Widżet jest instancją klasy [CWidget] lub klas pochodnych [CWidget]. Jest komponentem 
stosowanym dla celów prezentacji. Widżet jest zazwyczaj osadzany w skrypcie w celu
generowania pewnych skomplikowanych, samowystarczalnych (niezależnych)
interfejsów użytkownika. Na przykład, widżet kalendarza może być używany do wygenerowania 
skomplikowanego interfejsu użytkownika dla kalendarza. Widżety ułatwiają 
lepsze ponowne wykorzystanie kodu w interfejsie użytkownika.

Aby użyć widżetu w skrypcie widoku, zrób co następuje:

~~~
[php]
<?php $this->beginWidget('path.to.WidgetClass'); ?>
...zawartość, która może zostać uwzględniona przez widżet...
<?php $this->endWidget(); ?>
~~~

lub

~~~
[php]
<?php $this->widget('ścieżka.do.klasy.widżetu'); ?>
~~~

Drugie podejście jest używane kiedy widżet nie potrzebuje żadnej zawartości.

Widżety można skonfigurować, tak by zmienić ich zachowanie. Można to zrobić poprzez 
ustawienie wartości ich początkowych właściwości podczas wywołania [CBaseController::beginWidget] 
lub [CBaseController::widget]. Na przykład, używając widżetu [CMaskedTextField],
chcielibyśmy określić używaną maskę. Możemy to zrobić przez przekazywanie tablicy  
początkowych wartości właściwości, w której kluczami są nazwy właściwości a wartościami 
inicjalne wartości tych właściwości, w następujący sposób:


~~~
[php]
<?php
$this->widget('CMaskedTextField',array(
	'mask'=>'99/99/9999'
));
?>
~~~

Aby zdefiniować nowy widżet, rozszerz klasę [CWidget] oraz nadpisz jej metody 
[init()|CWidget::init] oraz [run()|CWidget::run]:

~~~
[php]
class MyWidget extends CWidget
{
	public function init()
	{
	  // jest to metoda wołana przez CController::beginWidget()
	}

	public function run()
	{
	  // jest to metoda wołana przez CController::endWidget()
	}
}
~~~

Tak jak w kontrolerze, widżet może mieć swój własny widok. Domyślnie, pliki widoku 
widżetu są umieszczone w podkatalogu `views` katalogu zawierającego klasę widżetu.
Widoki te mogą być renderowane poprzez wywołanie [CWidget::render()], w sposób podobny
do tego w kontrolerze. Jedyną różnicą jest to, że żaden układ nie będzie używany 
dla widoku widżetu. Ponadto, `$this` w widoku, wskazuje na instancję widżetu zamiast
na instancję kontrolera.


Widok systemowy (ang. System View)
-----------

Widok systemowy odpowiada widokowi używanemu w Yii do wyświetlania błędów oraz 
logowania informacji. Na przykład, kiedy użytkownik żąda dostępu do nieistniejącego 
kontrolera lub akcji, Yii rzuci wyjątkiem wyjaśniającym błąd. Wyświetli ten wyjątek
przy użyciu określonego widoku systemowego.

Nazywanie widoków systemowych podlega pewnym regułom. Nazwy takie jak `errorXXX` 
odpowiadają widokom do wyświetlania wyjątków [CHttpException] dla kodu błędu `XXX`.
Na przykład, jeśli wywołany jest wyjątek [CHttpException] o kodzie błędu 404, 
wtedy błąd `error404` będzie wyświetlony.

Yii dostarcza zestawu domyślnych widoków systemowych umieszczonych w folderze 
`framework/views`. Mogą one zostać zmienione poprzez utworzenie tak samo nazwanych
plików widoku w folderze `protected/views/system`.

<div class="revision">$Id: basics.view.txt 3251 2011-06-01 00:24:06Z qiang.xue $</div>