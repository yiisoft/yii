Buforowanie danych (ang. Data Caching)
============

Buforowanie danych dotyczy przechowywania pewnych zmiennych PHP w buforze oraz 
przywracania ich później z buforu. Z tego powodu, podstawowa klasa komponentu buforowania 
[CCache] dostarcza dwóch metod, które są używane przez większość czasu: [set()|CCache::set]
oraz [get()|CCache::get].

Aby zachować zmienną `$value` w buforze, wybieramy unikalne ID oraz wywołujemy 
[set()|CCache::set] aby ją zachować:

~~~
[php]
Yii::app()->cache->set($id, $value);
~~~

Zbuforowane dane pozostaną w buforze na zawsze, dopóki nie zostaną z niego usunięte
ze względu na pewne zachowanie buforowania (np. przestrzeń bufora jest pełna i stare dane 
są usuwane). Aby zmienić to zachowanie, możemy podać parametr wygasania podczas wywoływania 
[set()|CCache::set] co spowoduje, że dane zostaną usunięte z bufora po upływie 
określonego okresu czasu:

~~~
[php]
// trzymaj wartość w buforze najdłużej przez 30 sekund
Yii::app()->cache->set($id, $value, 30);
~~~

Następnie, jeśli potrzebujemy uzyskać dostęp do tej zmiennej (albo w tym samym lub też innym żądaniu) 
wywołujemy [get()|CCache::get] wraz z ID aby zwrócić ją z bufora. Jeśli wartość 
zwracana to false, oznacza to, że wartość nie jest dostępna w buforze i powinniśmy ją 
wygenerować.

~~~
[php]
$value=Yii::app()->cache->get($id);
if($value===false)
{
  // odnów $value ponieważ nie została znaleziona w buforze
	// i zachowaj ja w nim do ponownego użycia 
	// Yii::app()->cache->set($id,$value);
}
~~~

Podczas wybierania ID dla zmiennej, która będzie buforowana, upewnij się, że ID jest 
unikalne spośród wszystkich innych zmiennych, które mogą być zbuforowane w aplikacji.
NIE WYMAGA się, aby ID było unikalne pomiędzy aplikacjami, ponieważ komponent cache
jest wystarczająco zmyślny aby rozróżniać te same ID w różnych aplikacjach.


Część systemów buforowania pamięci, takich jak MemCache, APC, wspierają pobieranie 
wartości wielokrotnie zbuforowanych w trybie wsadowym, co może objawić się zredukowaniem
obciążenia związanego z pobieraniem zbuforowanych danych. Metoda [mget()|CCache::mget] 
służy wykorzystaniu tej właściwości. W przypadku kiedy używany system buforowania nie 
wspiera tej funkcjonalności metoda [mget()|CCache::mget] zasymuluje ją.

Aby usunąć zbuforowaną wartość z bufora wywołujemy metodę [delete()|CCache::delete];
aby usunąć całą wartość z bufora wołamy [flush()|CCache::flush]. Bądź bardzo ostrożny
podczas wywoływania [flush()|CCache::flush] ponieważ usuwa ono dane, które zostały 
zbuforowane dla innych aplikacji.

> Tip|Wskazówka: Ponieważ [CCache] implementuje dostęp przez tablice `ArrayAccess`
> komponent cache może być używany jak tablica. Poniżej znajduje się kilka przykładów:
> ~~~
> [php]
> $cache=Yii::app()->cache;
> $cache['var1']=$value1;  // równoznaczne z: $cache->set('var1',$value1);
> $value2=$cache['var2'];  // równoznaczne z: $value2=$cache->get('var2');
> ~~~

Zależności w buforowaniu (ang. Cache Dependency)
----------------

Poza opcją wygasania, dane zbuforowane  mogą również stracić ważność zgodnie
z pewnymi zmianami zależności. Na przykład, jeśli buforujemy zawartość pewnego 
pliku a plik ulegnie zmianie, powinniśmy unieważnić zbuforowaną kopię i przeczytać
najnowszą zawartość z pliku zamiast tej z bufora.

Reprezentujemy zależność jako instancję klasy [CCacheDependency] lub jej klas pochodnych.
Przekazujemy instancję zależności wraz z danymi do buforowania gdy wywołujemy [set()|CCache::set].

~~~
[php]
// wartość wygasa w ciągu 30 sekund
// może ona również stracić ważność wcześniej jeśli zależny plik jest zmieniony
Yii::app()->cache->set($id, $value, 30, new CFileCacheDependency('NazwaPliku'));
~~~

Teraz jeśli zwrócimy `$value` z buforu poprzez wywołanie [get()|CCache::get] zależność
zostanie sprawdzona i jeśli zmieni się, zostanie zwrócona wartość false, ze wskazaniem
danych, które wymagają odświeżenia.

Poniżej znajduje się podsumowanie dostępnych zależności buforowania:

   - [CFileCacheDependency]: zależność jest zmieniona jeśli zmienił się czas
   ostatniej modyfikacji pliku.

   - [CDirectoryCacheDependency]: zależność jest zmieniona jeśli jakikolwiek z plików
   w katalogu lub podkatalogach zmienił się.

   - [CDbCacheDependency]: zależność jest zmieniona jeśli wynik zapytania określonego 
   zapytania SQL zmienił się.

   - [CGlobalStateCacheDependency]: zależność jest zmieniona jeśli wartość określonego 
   globalnego stanu została zmieniona. Globalny stan to zmienna, która jest trwała 
   w aplikacji dla wielu żądań oraz wielu sesji. Jest zdefiniowana poprzez [CApplication::setGlobalState()].

   - [CChainedCacheDependency]: zależność jest zmieniona jeśli jakakolwiek z zależności 
   w łańcuchu zmieniła się.

   - [CExpressionDependency]: zależność jest zmieniona jeśli zmieni się rezultat określonego wyrażenia PHP.
   

Buforowanie zapytań
-------------

Od wersji 1.1.7 Yii dodało wsparcie dla buforowania zapytań. 
Buforowanie zapytań zbudowane w oparciu o buforowanie danych, przechowuje wynik zapytania
do bazy danych w buforze i dzięki temu może skrócić czas ich wykonywania 
dla tego samego zapytania wykonywanego w przyszłości, 
pozwalając na bezpośrednie zwracania z bufora danych w postaci wynikowej.

> Info|Info: Niektóre DBMS (np. [MySQL](http://dev.mysql.com/doc/refman/5.1/en/query-cache.html))
> wpierają buforowanie zapytań po stronie serwera bazy danych. W porównaniu do buforowania zapytań po stronie serwera
> wspieramy te same funkcjonalności przy okazji oferując większą elastyczność i potencjalnie większą wydajność.


### Włączanie buforowania zapytań

Aby włączyć buforowanie zapytań upewnij się, że [CDbConnection::queryCacheID] wskazuje na ID 
odpowiedniego komponentu buforowania aplikacji (domyślnie `cache`).


### Używanie buforowania zapytań w DAO

Aby używać buforowania zapytań wołamy metodę [CDbConnection::cache()] podczas wykonywania zapytania 
bazodanowego. Poniżej znajduje się przykład:

~~~
[php]
$sql = 'SELECT * FROM tbl_post LIMIT 20';
$dependency = new CDbCacheDependency('SELECT MAX(update_time) FROM tbl_post');
$rows = Yii::app()->db->cache(1000, $dependency)->createCommand($sql)->queryAll();
~~~

Podczas wykonywania powyższej instrukcji, Yii sprawdzi najpierw czy bufor zawiera odpowiedni
rezultat instrukcji SQL, która ma zostać wykonana. Dzieje się to poprzez sprawdzenia następujących trzech warunków:

- czy bufor zawiera wpis zindeksowany za pomocą instrukcji SQL.
- czy ważność tego wpisu nie upłynęła (mniej niż 1000 sekund od momentu kiedy został on zapisany w buforze).
- czy zależność nie zmieniła się (maksymalna wartość `update_time` jest taka sama jak wtedy gdy wynik zapytania został zapisany w buforze).

Jeśli wszystkie powyższe warunki zostały spełnione, zbuforowane wartości zostaną zwrócone bezpośrednio z bufora.
W przeciwnym przypadku, instrukcja SQL zostanie wysłana do serwera bazy danych w celu jej wykonania
a zwrócony wynik zostanie zapisany w buforze i zwrócony.


### Używanie buforowania zapytań w rekordzie aktywnym

Buforowanie zapytań może być również używane z [rekordem aktywnym](/doc/guide/database.ar).
W tym celu wywołujemy taką bliźniaczą metodę [CActiveRecord::cache()] jak w poniższym przykładzie:

~~~
[php]
$dependency = new CDbCacheDependency('SELECT MAX(update_time) FROM tbl_post');
$posts = Post::model()->cache(1000, $dependency)->findAll();
// relacyjne zapytanie przy użyciu rekordu aktywnego 
$posts = Post::model()->cache(1000, $dependency)->with('author')->findAll();
~~~

Zasadniczo metoda `cache()` jest tutaj skrótem do [CDbConnection::cache()].
Wewnętrznie, podczas wykonywania instrukcji SQL generowanej przez rekord aktywny, 
Yii spróbuje użyć buforowania zapytań, które opiszemy w ostatniej podsekcji.


### Buforowanie wielu zapytań

Domyślnie każde wywołanie metody `cache()` (zarówno w [CDbConnection] jak i [CActiveRecord]),
będzie oznaczać, że następne zapytanie SQL zostanie zbuforowane. Pozostałe zapytania SQL
NIE BĘDĄ buforowane dopóki nie wywołamy ponownie metody `cache()`. Na przykład:

~~~
[php]
$sql = 'SELECT * FROM tbl_post LIMIT 20';
$dependency = new CDbCacheDependency('SELECT MAX(update_time) FROM tbl_post');

$rows = Yii::app()->db->cache(1000, $dependency)->createCommand($sql)->queryAll();
// buforowanie zapytań NIE BĘDZIE użyte
$rows = Yii::app()->db->createCommand($sql)->queryAll();
~~~

Poprzez wprowadzenie dodatkowego parametru `$queryCount` w metodzie `cache()`, możemy
wymusić buforowanie wielu zapytań. W następnym przykładzie, podczas wywołania metody `cache()`
określimy, iż buforowanie zapytań powinno nastąpić dla dwóch kolejnych zapytań:

~~~
[php]
// ...
$rows = Yii::app()->db->cache(1000, $dependency, 2)->createCommand($sql)->queryAll();
// buforowanie zapytań BĘDZIE używane
$rows = Yii::app()->db->createCommand($sql)->queryAll();
~~~

Jak wiadomo (poprzez sprawdzanie [rejestrowania komunikatów](/doc/guide/topics.logging))
podczas wykonywania zapytań relacyjnych, możliwe jest wykonanie kilku zapytań SQL.
Na przykład, jeśli relacja pomiędzy postem `Post` a komentarzem `Comment` jest typu wiele-do-wielu `HAS_MANY`,
wtedy następujący kod będzie wykonywał dwa zapytania do bazy danych:

- najpierw odczyta 20 wiadomości;
- następnie wybierze komentarze dla poprzednio odczytanych wiadomości.

~~~
[php]
$posts = Post::model()->with('comments')->findAll(array(
	'limit'=>20,
));
~~~

Jeśli użyjemy buforowania zapytań w następujący sposób, jedynie pierwsze zapytanie zostanie zbuforowane:

~~~
[php]
$posts = Post::model()->cache(1000, $dependency)->with('comments')->findAll(array(
	'limit'=>20,
));
~~~

W celu zbuforowanie obu zapytań do bazy danych potrzebujemy przekazać dodatkowy parametr
informujący o tym jak wiele zapytań do bazy danych chcemy zbuforować:

~~~
[php]
$posts = Post::model()->cache(1000, $dependency, 2)->with('comments')->findAll(array(
	'limit'=>20,
));
~~~


### Ograniczenia

Buforowanie zapytań nie działa dla wyników zapytań, które zawierają uchwyty zasobów.
Na przykład jeśli używamy kolumny typu `BLOB` w DBMS, wtedy zapytanie zwróci uchwyt zasobu 
do kolumny z danymi.

Niektóre systemy buforowania pamięci mają ograniczenia rozmiaru. Na przykład, memcache ogranicza
maksymalny rozmiar każdego wpisu do 1MB. Dlatego też, jeżeli rozmiar rezultatu zapytania przekracza
ten limit buforowanie nie powiedzie się.


<div class="revision">$Id: caching.data.txt 3125 2011-03-25 17:05:31Z qiang.xue $</div>