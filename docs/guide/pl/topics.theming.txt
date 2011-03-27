Tematy
======

Tematy są usystematyzowanym sposobem personalizacji wyglądu stron aplikacji
webowej. Poprzez zastosowanie nowego tematu cały wygląd aplikacji może być
jednocześnie i znacząco zmieniony.

W ramach Yii każdy temat reprezentowany jest przez katalog zawierający pliki
widoków, pliki układów (ang. layout) i powiązane z nimi pliki zasobów,
takie jak obrazy, pliki CSS, Javascript, itp. Nazwa tematu jest też nazwą
jego katalogu. Wszystkie tematy znajdują się we wspólnym katalogu `WebRoot/themes`.
W dowolnej chwili tylko jeden z nich może być aktywny.

> Wskazówka: Domyślny katalog tematów `WebRoot/themes` może być zastąpiony
innym. Po prostu skonfiguruj odpowiednio atrybuty
[basePath|CThemeManager::basePath] oraz [baseUrl|CThemeManager::baseUrl]
komponentu aplikacji [themeManager|CWebApplication::themeManager].


Używanie tematów
-------------

Aby aktywować jakiś temat ustaw atrybut [theme|CWebApplication::theme]
aplikacji webowej na nazwę tematu, który chcesz użyć. Czynność tą możesz
wykonać zarówno w [konfiguracji aplikacji](/doc/guide/basics.application#application-configuration),
jak i w trakcie uruchamiania, poprzez akcję kontrolera.

> Uwaga: Nazwa tematu jest wrażliwa na wielkość liter. Jeżeli próbujesz
aktywować temat, który nie istnieje `Yii::app()->theme` zwróci `null`.


Tworzenie tematów
----------------

Zawartość katalogu tematu powinna być zorganizowana w taką samą strukturę
jak ta w [ścieżce głównej aplikacji](/doc/guide/basics.application#application-base-directory).
Przykładowo, wszystkie pliki widoków muszą być umieszczone w `views`,
pliki układów (ang. layout) widoku w `views/layouts`, a pliki widoków
systemowych w `views/system`. Na przykład gdy będziemy chcieli zamienić
widok `create` kontrolera `PostController` z widokiem tematu `classic`
powinniśmy zapisać nowy plik jako `WebRoot/themes/classic/views/post/create.php`.

Dla widoków należących do kontrolera w [module](/doc/guide/basics.module),
odpowiadający mu plik widoku tematu powinien być również umieszczony
w katalogu `views`. Na przykład, uprzednio omawiany kontroler `PostController`
jest w module nazwanym `forum`, powinniśmy więc zapisać plik widoku `create`
jako `WebRoot/themes/classic/views/forum/post/create.php`.
W module `forum` zagnieżdżony jest inny moduł nazwany `support`, w tym wypadku
plikiem widoku powinien być `WebRoot/themes/classic/views/support/forum/post/create.php`.

> Uwaga: Ponieważ katalog `views` może zawierać wrażliwe z punktu widzenia bezpieczeństwa dane, powinien być zabezpieczony przed dostępem użytkowników internetu.

Gdy wywołujemy metodę [render|CController::render]
lub [renderPartial|CController::renderPartial] aby wyświetlić widok,
odpowiadające temu widokowi pliki, jak również pliki układu (layout)
szukane będą w katalogu aktywnego tematu. Jeżeli zostaną odnalezione,
będą renderowane. W przeciwnym razie aplikacja powraca do domyślnej
lokalizacji określonej przez atrybuty [viewPath|CController::viewPath]
i [layoutPath|CWebApplication::layoutPath].

> Wskazówka: Wewnątrz widoku tematu musimy często dołączać pliki zasobów
> innych tematów. Np. możemy chcieć wyświetlić plik obrazu znajdujący się
> w katalogu `images` tematu. Korzystając z właściwości [baseUrl|CTheme::baseUrl]
> aktualnie aktywnego tematu możemy wygenerować adres URL dla tego obrazka
> w sposób następujący:
>
> ~~~
> [php]
> Yii::app()->theme->baseUrl . '/images/FileName.gif'
> ~~~
>

Poniżej znajduje się przykład organizacji katalogów dla aplikacji,
która posiada dwa tematy podstawowy `basic` oraz fantazyjny `fancy`.

~~~
WebRoot/
  assets
  protected/
    .htaccess
    components/
    controllers/
    models/
    views/
      layouts/
        main.php
      site/
        index.php
  themes/
    basic/
      views/
        .htaccess
        layouts/
          main.php
        site/
          index.php
    fancy/
      views/
        .htaccess
        layouts/
          main.php
        site/
          index.php
~~~

W konfiguracji aplikacji, jeśli użyć następującej skonfiguracji

~~~
[php]
return array(
  'theme'=>'basic',
  ......
);
~~~

to będzie obowiązywać temat podstawowy `basic`, co oznacza iż aplikacja będzie
używać tego tematu z katalogu `themes/basic/views/layouts`, natomiast indeks strony 
będzie używał tego z katalogu `themes/basic/views/site`. W przypadku plik widoku nie  
zostanie znaleziony w temacie, to wykorzystamy ten znajdujący się w katalogu `protected/views`.


Motywy dla widżetów
---------------

Poczynając od wersji 1.1.5, widoki używane w widżetach mogą również używać motywów. W szczególności, jesli wywołujemy [CWidget::render()] w celu wygenerowania widoku widżetu, Yii spróbuje poszukać w katalogu z motywami, tak samo jak to czynie z katalogiem w widokami, żądanego pliku widoku.

Aby nadać motyw dla widoku `xyz` dla widżetu, którego nazwa klasy to `Foo`, powinniśmy najpierw utworzyć katalog
o nazwie `Foo` (taki sam jak nazwa klasy widżetu) w folderze motywu aktualnie aktywnego. Jeśli nazwa klasu widżetu posiada przestrzeń nazw (dostępne w wersjach 5.3.0 lub wyższych), taką jak `\app\widgets\Foo`, powinniśmy utworzyć folder o nazwie `app_widgets_Foo`. Oznacza to, że zastąpiliśmy separatory w przestrzeni nazw poprzez znak podkreślenia.

Następnie tworzymy plik widoku o nazwie `xyz.php` w nowo utworzonym katalogu. Na koniec, powinniśmy mieć plik `themes/basic/views/Foo/xyz.php`, który zostanie użyty przez widżet i zastąpi oryginalny widok, jesli aktualnie aktywnym tematem jest `basic`.


Globalne dostosowanywanie widżetów do własnych potrzeb
----------------------------

> Note|Uwaga: funkcjonalność ta dostępna jest od wersji 1.1.3.

W trakcie używania widżetów dostarczonych przez osoby trzecie czy też Yii, często potrzebujemy
dostosować je do konkretnych potrzeb. Na przykład, możemy chcieć zmienić wartość 
[CLinkPager::maxButtonCount] z 10 (domyślnie) na 5. Możemy zrobić to poprzez przekazanie 
inicjalnych wartości gdy wywołujemy metodę [CBaseController::widget] w celu utworzenia widżetu.
Jednakże staje się to uciążliwe, jeśli musimy powtarzać tę samą operację dostosowywania
w każdym miejscu gdzie używany [CLinkPager].

~~~
[php]
$this->widget('CLinkPager', array(
	'pages'=>$pagination,
    'maxButtonCount'=>5,
    'cssFile'=>false,
));
~~~

Uzywając funkcjonalności globalnego dostosowywania właściwości widżetów, musimy 
okreslić tą wartość inicjalną tylko w jednym miejscu, np. w konfiguracji aplikacji.
Dzięki temu łatwiej nam zapanować nad dostosowywaniem właściwości widżetów.

Aby używać tę funkcjonalność potrzebujemy skonfigurować
[widgetFactory|CWebApplication::widgetFactory] w następujące sposób:

~~~
[php]
return array(
    'components'=>array(
        'widgetFactory'=>array(
            'widgets'=>array(
                'CLinkPager'=>array(
                    'maxButtonCount'=>5,
                    'cssFile'=>false,
                ),
                'CJuiDatePicker'=>array(
                    'language'=>'ru',
                ),
            ),
        ),
    ),
);
~~~

W powyższym kodzie, dostosowaliśmy globalnie właściwości dla dwóch widżetów
[CLinkPager] oraz [CJuiDatePicker] poprzez skonfigurowanie właściwości [CWidgetFactory::widgets].
Zauważ, że globalne konfiguracja dla każdego z widżetów reprezetnowana jest 
przez pary klucz-wartość w tablicy, gdzie klucz odpowiada nazwie klasy widżetu
zaś wartością jest tablica inicjalnych wartości właściwości widżetu.

Od teraz, za każdym razem, gdy tworzymy widżet [CLinkPager] w widoku, powyższe wartości właściwości
będą przypisywane do widżetu a my musimy jedynie napisać następujący kod w widoku w celu utworzenia widżetu:

~~~
[php]
$this->widget('CLinkPager', array(
	'pages'=>$pagination,
));
~~~

Wciąż możemy nadpisać wartości inicjalne jeśli jest taka potrzeba. Na przykład, jeśli 
w niektórych widokach chcemy ustawić właściwość  `maxButtonCount` na 2, możemy zrobić to następująco:

~~~
[php]
$this->widget('CLinkPager', array(
	'pages'=>$pagination,
	'maxButtonCount'=>2,
));
~~~

Skórki
------

Używając tematów możemy szybko zmieniać wygląd widoków. Możemy używać skórek do 
systematycznego dostosowywania wyglądu [widżetów](/doc/guide/basics.view#widget) używanych w widoku.

Skórka jest tavblicą par nazwa-wartość, która może zostać użyta do zainicjalizowania
właściwości widżetu. Skórka należy do klasy widżetu, a klasa widżetu może posiadać  
wiele skórek identyfikowanych przez ich nazwę. Na przykład, możemy mieć skórkę dla widżetu 
[CLinkPager] nazwanej `classic`.

Aby móc skorzystać z funkcjonalności skórek musimy najpierw zmodyfikować konfigurację
aplikacji poprzez zainstalowanie komponentu `widgetFactory`:

~~~
[php]
return array(
    'components'=>array(
        'widgetFactory'=>array(
            'enableSkin'=>true,
        ),
    ),
);
~~~

Zauważ, że w wersjach wcześniejszych niż 1.1.3, musisz użyć następującej konfiguracji aby móc korzystać w widżetach ze skórek:

~~~
[php]
return array(
    'components'=>array(
        'widgetFactory'=>array(
        ),
    ),
);
~~~

Następnie tworzymy potrzebne skórki. Skórki należa do tego samej klasy widżetu
są przechowywane w pojedynczym skrypcie pliku, którego nazwa jest taka jak nazwa
klasy widżetu. Wszystkie pliki skórek przechowywane są domyślnie w katalogu `protected/views/skins`.
Jeśli chcesz zmienić go na inny katalog, możesz skonfigurować właściwość `skinPath` 
komponentu `widgetFactory`. Na przykład możemy stworzyć w katalogu `protected/views/skins` 
plik o nazwie `CLinkPager.php`, którego zawartość jest następująca:

~~~
[php]
<?php
return array(
    'default'=>array(
        'nextPageLabel'=>'&gt;&gt;',
        'prevPageLabel'=>'&lt;&lt;',
    ),
    'classic'=>array(
        'header'=>'',
        'maxButtonCount'=>5,
    ),
);
~~~

W powyższym przykładzie, utworzyliśmy skórki dla widżetu [CLinkPager]: domyślną `default` 
oraz klasyczną `classic`. Pierwsza skórka stosowana jest do wszystkich widżetów [CLinkPager]
dla których nie określiliśmy bezpośrednio ich właściwości `skin`, gdy zaś druga jest 
skórką stosowaną do widżetu [CLinkPager], którego właściwość `skin` jest określona jako `classic`. 
Na przykład, w następującym kodzie widoku, pierwszy pager będzie używał skórki `default`
gdy zaś drugi użyje skórki `classic`:

~~~
[php]
<?php $this->widget('CLinkPager'); ?>

<?php $this->widget('CLinkPager', array('skin'=>'classic')); ?>
~~~
Jeśli utworzymy widżet wraz z zestawem inicjalnych wartości właściwości, będą one miały 
pierwszeństwo oraz zostaną złączone z każdą zastosowaną skórką. Na przykład, następujący 
kod widoku utworzy pager, którego inicjalne wartości będą tablicą 
`array('header'=>'', 'maxButtonCount'=>6, 'cssFile'=>false)`, która jest wynikiem 
połączenia inicjalnych wartości właściwości określonych w widoku oraz skórki `classic`.

~~~
[php]
<?php $this->widget('CLinkPager', array(
    'skin'=>'classic',
    'maxButtonCount'=>6,
    'cssFile'=>false,
)); ?>
~~~

Zauważ, że funkcjonalność skórek nie wymaga stosowanie tematów. Jednakże, jeśli temat jest
aktywny, Yii również będzie poszukiwało skórek w katalogu `skins` dla katalogu tematu widoku 
(np. `WebRoot/themes/classic/views/skins`). W przypadku gdy skórka o tej samej nazwie istnieje 
zarówno w temacie jak i w katalogach widoków głównej aplikacji, skórka tematu będzie miała pierwszeństwo.

Jeśli widżet używa skórki, która nie istnieje, Yii pomimo tego faktu utwoży widżet 
tak jak to czyni zazwyczaj, bez zgłaszani błędu. 

> Info|Info: Używanie skórek może zmiejszyć wydajność, ponieważ Yii musi znaleźć  
plik skórki gdy widżet jest tworzony po raz pierwszy.

Skórka jest bardzo podobna do funkcjonalności globalnego dostosowywania właściwości. 
Podstawowa różnice są nastepujące:

   - skórka wiąże się raczej z dostosowywaniem wartości właściwości związanych z warstwą prezentacji;
   - widżet może posiadać wiele skórek;
   - dla skórek można stosowac motywy (ang. theme);
   - używanie skórek jest bardziej kosztowne niż używanie funkcjonalności globalnego dostosowywania właściwości widżetu;

<div class="revision">$Id: topics.theming.txt 2890 2011-01-18 15:58:34Z qiang.xue $</div>