Logowanie błędów
==============

Aplikacja działająca produktywnie często potrzebuje wyszukanego rejestrowania dla różnych zdarzeń. W naszym blogu, będziemy chcieli logować błędy występujące podczas jego użytkowania. Takie błędy mogą brać się z pomyłek programisty bądź też z niewłaściwego użytkowania aplikacji przez użytkowników. Rejestrowanie tych błędów pomoże nam usprawniać naszą aplikację. 

Udostępniamy logowanie błędów poprzez modyfikację [konfiguracji aplikacji](http://www.yiiframework.com/doc/guide/basics.application#application-configuration) w następujący sposób:

~~~
[php]
return array(
	'preload'=>array('log'),

	......

	'components'=>array(
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
			),
		),
		......
	),
);
~~~

Przy użyciu powyższej konfiguracji, jeśli wystąpi błąd bądź też ostrzeżenie, szczegółowa informacja zostanie zarejestrowana i zapisana w pliku znajdującym się w katalogu `/wwwroot/blog/protected/runtime`.

Komponent `log` oferuje bardziej zaawansowane funkcjonalności, takie jak wysyłanie zalogowanej wiadomości na daną listę adresów mailowych, wyświetlanie zarejestrowanych wiadomości w oknie konsoli JavaScript, itp. Aby uzyskać więcej szczegółów, zajrzyj do [przewodnika](http://www.yiiframework.com/doc/guide/topics.logging).


<div class="revision">$Id: final.logging.txt 878 2009-03-23 15:31:21Z qiang.xue $</div>