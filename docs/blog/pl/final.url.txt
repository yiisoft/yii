Upiększanie URLi
================

Adresy URL łączące różne strony naszego blogu wyglądają obecnie brzydko. Na przykład URL dla strony wyświetlającej wiadomości wygląda następująco:

~~~
/index.php?r=post/show&id=1&title=A+Test+Post
~~~

W części tej opiszemy jak upiększyć te adresy URL i uczynić je przyjaznymi dla SEO. Naszym celem jest używanie następujących adresów URL w aplikacji:

 1. `/index.php/posts/yii`: prowadzi do strony pokazującej listę wiadomości z tagiem `yii`;
 2. `/index.php/post/12/A+Test+Post`: prowadzi do strony pokazującej szczegóły wiadomości o ID równym 2, którego tytułem jest `A Test Post`;
 3. `/index.php/post/update?id=1`: prowadzi do strony, która pozwala aktualizować wiadomość o ID 1;

Zauważ, że w drugim formacie adresu URL dodajemy tytuł wiadomości do adresu URL. Czynimy to aby uczynić adres URL przyjaznym SEO. Mówi się, że wyszukiwarki mogą również uwzględniać słowa znalezione w adresach URL podczas ich indeksowania.

Aby osiągnąć nasz cel, zmodyfikujemy [konfigurację aplikacji](http://www.yiiframework.com/doc/guide/basics.application#application-configuration) w następujący sposób:

~~~
[php]
return array(
	......
	'components'=>array(
		......
		'urlManager'=>array(
			'urlFormat'=>'path',
			'rules'=>array(
        		'post/<id:\d+>/<title:.*?>'=>'post/view',
        		'posts/<tag:.*?>'=>'post/index',
        		'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
			),
		),
	),
);
~~~

W powyższym kodzie, skonfigurowaliśmy komponent [urlManager](http://www.yiiframework.com/doc/guide/topics.url) poprzez ustawienie jego właściwości `urlFormat` jako `path` oraz dodanie zestawu reguł `rules`.

Reguły używane są przez `urlManager` do analizowania i tworzenia URLi w zaprojektowanym formacie. Na przykład, pierwsza reguła mówi, że jeśli żądamy URLu `/index.php/posts/yii` komponent `urlManager` powinien być odpowiedzialny za wysłania żądania do [trasy](http://www.yiiframework.com/doc/guide/basics.controller#route) `post/index` oraz wygenerowania parametru GET o nazwie`tag` i wartości `yii`. Z drugiej strony, podczas tworzenia URL-a o ścieżce `post/list` oraz parametrze `tag`, komponent `urlManager` również użyje tej reguły do wygenerowania pożądanego adresu URL `/index.php/posts/yii`. Z tego też powodu mówimy, że `urlManager` jest dwukierunkowym menadżerem URLi.

Komponent `urlManager` może później upiększyć nasze URLe, np. poprzez ukrycie `index.php` w URLach, dodanie sufiksu takiego jak `.html` do URLi. Możemy uzyskać te funkcje w prosty sposób poprzez konfigurowanie różnych właściwości `urlManager` w konfiguracji aplikacji. Aby uzyskać więcej szczegółów, zajrzyj do [przewodnika](http://www.yiiframework.com/doc/guide/topics.url).


<div class="revision">$Id: final.url.txt 2240 2010-07-03 18:06:11Z alexander.makarow $</div>