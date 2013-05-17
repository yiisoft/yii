Dostosowywanie modelu wiadomości
======================

Klasa wiadomości `Post`, wygenerowana przez narzędzie `Gii` musi zostać zmodyfikowana głównie w dwóch miejscach:

 - w metodzie `rules()`: określającej reguły sprawdzania poprawności dla atrybutów modelu;
 - w metodzie `relations()`: definiującej obiekty pokrewne;

> Info|Info: [Model](http://www.yiiframework.com/doc/guide/basics.model) zawiera listę atrybutów, każdy z nich jest powiązany z odpowiadającą mu kolumną w bazie danych. Atrybuty mogą być zadeklarowane bezpośrednio jako zmienne klasy lub też pośrednio bez żadnej deklaracji. 


Dostosowywanie metody `rules()`
----------------------------

Najpierw określamy zasady sprawdzania poprawności, które pozwalają nam upewnić się, że wartości atrybutów wprowadzonych przez użytkownika są poprawne, zanim zostaną zapisane do bazy danych. Na przykład, atrybut `status` dla wiadomości `Post` powinien posiadać wartość 1, 2 lub 3. Narzędzie `Gii` również generuje zasady sprawdzania poprawności dla każdego modelu. Jednakże, reguły te bazują na informacjach o kolumnie tabeli i mogą być nieodpowiednie.

W oparciu o analizę potrzeb, modyfikujemy metodę `rules()` w następujący sposób:

~~~
[php]
public function rules()
{
	return array(
		array('title, content, status', 'required'),
		array('title', 'length', 'max'=>128),
		array('status', 'in', 'range'=>array(1,2,3)),
		array('tags', 'match', 'pattern'=>'/^[\w\s,]+$/',
			'message'=>'Tags can only contain word characters.'),
		array('tags', 'normalizeTags'),

		array('title, status', 'safe', 'on'=>'search'),
	);
}
~~~

W powyższym kodzie określiliśmy, że atrybuty tytułu `title`, zawartości `content` i statusu `status` są atrybutami wymaganymi (muszą być wypełnione); długość tytułu `title` nie powinna przekraczać 128 (znaków); wartość atrybutu statusu `status` powinna być 1 (wersja robocza, ang. draft), 2 (opublikowana, ang. published) lub 3 (zarchiwizowana, ang. archived); a atrybut otagowania `tags` powinien zawierać wyłącznie znaki słów oraz przecinki. Dodatkowo używamy metody `normalizeTags` do unormowania wprowadzonych przez użytkownika tagów, w taki sposób, że tagi będą unikalne i poprawnie rozdzielone za pomocą przecinków. Ostatnia reguła jest używana przez funkcjonalność wyszukiwania, co zostanie opisane później.

Walidatory takie jak `required`, `length`, `in` oraz `match` są wbudowanymi walidatorami dostarczanymi przez Yii. Natomiast walidator `normalizeTags` jest walidatorem opartym na metodzie, którą musimy zdefiniować w klasie wiadomości `Post`. Aby uzyskać więcej informacji o tym jak określić reguły sprawdzania poprawności, zajrzyj do [poradnika](http://www.yiiframework.com/doc/guide/form.model#declaring-validation-rules).

~~~
[php]
public function normalizeTags($attribute,$params)
{
	$this->tags=Tag::array2string(array_unique(Tag::string2array($this->tags)));
}
~~~

gdzie metody `array2string` oraz `string2array` są nowymi metodami, które musimy zdefiniować w klasie modelu `Tag`:

~~~
[php]
public static function string2array($tags)
{
	return preg_split('/\s*,\s*/',trim($tags),-1,PREG_SPLIT_NO_EMPTY);
}

public static function array2string($tags)
{
	return implode(', ',$tags);
}
~~~

Reguły zadeklarowane w metodzie `rules()` są wywoływane jedna po drugiej podczas wywoływania metody [validate()|CModel::validate] lub [save()|CActiveRecord::save] dla instancji modelu.

> Note|Uwaga: Niezmiernie ważnym jest aby zapamiętać, że atrybuty które pojawiły się w metodzie `rules()` były tymi, które są wprowadzane przez użytkownika. Pozostałe atrybuty takie jak `id` oraz czas utworzenia `create_time` w modelu wiadomości `Post`, które są ustawiane przez nasz kod lub też bazę danych, nie powinny się znajdować w metodzie `rules()`. Aby uzyskać więcej informacji o tym, proszę zajrzeć do [zabezpieczanie przypisywania atrybutów](http://www.yiiframework.com/doc/guide/form.model#securing-attribute-assignments).

Po wprowadzeniu tych zmian, możemy odwiedzić ponownie stronę tworzenia wiadomości w celu weryfikacji czy nowe zasady sprawdzania poprawności mają miejsce.


Dostosowywanie metody `relations()`
--------------------------------

Na samym końcu dostosowujemy metodę `relations()` w celu zdefiniowania obiektów powiązanych do wiadomości. Poprzez zadeklarowanie tych powiązanych obiektów w metodzie `relations()`, możemy wykorzystać potęgę funkcji [relacyjnego aktywnego rekordu (RAR)](http://www.yiiframework.com/doc/guide/database.arr) w celu uzyskania dostępu do informacji z powiązanych z wiadomością obiektów, takich jak jej autor oraz komentarze bez potrzeby pisania złożonych wyrażeń SQL z JOIN.

Dostosowujemy metodę `relations()` w następujący sposób:

~~~
[php]
public function relations()
{
	return array(
		'author' => array(self::BELONGS_TO, 'User', 'author_id'),
		'comments' => array(self::HAS_MANY, 'Comment', 'post_id',
			'condition'=>'comments.status='.Comment::STATUS_APPROVED,
			'order'=>'comments.create_time DESC'),
		'commentCount' => array(self::STAT, 'Comment', 'post_id',
			'condition'=>'status='.Comment::STATUS_APPROVED),
	);
}
~~~

Wprowadzamy również w klasie modelu komentarza `Comment` dwie poniższe stałe, które są używanie w metodzie powyżej: 

~~~
[php]
class Comment extends CActiveRecord
{
	const STATUS_PENDING=1;
	const STATUS_APPROVED=2;
	......
}
~~~

Relacje zadeklarowane w metodzie `relations()` oznaczają, że:

 * Wiadomość należy do autora, którego reprezentuje klasa `User` a relacja pomiędzy nimi zostaje określona w oparciu o wartość atrybutu `author_id` wiadomości;
 * Wiadomość posiada wiele komentarzy, które reprezentuje klasa `Comment` a relacja pomiędzy nimi zostaje określona w oparciu o wartość atrybutu `post_id` tych komentarzy. Komentarze te powinny zostać posortowane odpowiednio do czasu ich utworzenia.
 * Relacja `commentCount` trochę innego typu, gdyż zwraca nam zagregowany wynik, który mówi nam ile komentarzy posiada wiadomość.


Przy użyciu powyższej deklaracji relacji, możemy w łatwy sposób uzyskać dostęp do autora oraz komentarzy wiadomości w następujący sposób:

~~~
[php]
$author=$post->author;
echo $author->username;

$comments=$post->comments;
foreach($comments as $comment)
	echo $comment->content;
~~~

Aby uzyskać więcej informacji o tym jak deklarować i używać relacji, sprawdź [Poradnik](http://www.yiiframework.com/doc/guide/database.arr).


Dodanie właściwości `url`
---------------------

Wiadomość jest pewną zawartością, która jest powiązana z unikalnym adresem URL, służącym do jej wyświetlenia. Zamiast wywoływać metodę [CWebApplication::createUrl] wszędzie, w naszym kodzie, gdzie chcemy dostać ten adres URL, możemy dodać właściwość `url` do modelu wiadomości `Post`, tak że ta sama część kodu tworzącego adres URL może zostać użyta ponownie. W dalszej części, gdy już opiszemy jak upiększać URLe, zobaczymy że wprowadzenie tej właściwości niesie za sobą dużo wygody.

Aby dodać właściwość `url` zmodyfikujemy klasę wiadomości `Post` w następujący sposób poprzez dodanie metody gettera:

~~~
[php]
class Post extends CActiveRecord
{
	public function getUrl()
	{
		return Yii::app()->createUrl('post/view', array(
			'id'=>$this->id,
			'title'=>$this->title,
		));
	}
}
~~~

Zauważ, że oprócz ID wiadomości, dodajemy również jej tytuł jako parametr GET w adresie URL. Robimy to głównie z powodu optymalizacji dla wyszukiwarek (SEO), tak jak to opisaliśmy w [upiększaniu URLi](/doc/blog/final.url).

Ponieważ klasa [CComponent] jest ostatecznym przodkiem klasy wiadomości `Post`, dodanie metody `getUrl()` pozwala nam używać wyrażenia `$post->url`. Kiedy uzyskujemy dostęp do `$post->url` metoda gettera zostanie wywołana i jej rezultat zostanie zwrócony w postaci wartości wyrażenia. Aby dowiedzieć się więcej o tej funkcjonalności, zajrzyj do [przewodnika](/doc/guide/basics.component).


Reprezentacja statusu w postaci tekstowej
---------------------------

Ponieważ status wiadomości jest przechowywany jako wartość całkowita (integer) w bazie danych potrzebujemy dostarczyć jego reprezentację tekstową, po to aby wyświetlić ją w bardziej przystępnym formacie użytkownikowi końcowemu. Podobne wymagania występują bardzo często w dużych systemach. 

Jako ogólne rozwiązanie dla ww problemu stosujemy tabelę `tbl_lookup` przechowującą mapowania pomiędzy wartościami całkowitymi a tekstową reprezentacją potrzebną innym obiektom danych. Modyfikujemy klasę modelu `Lookup` w następujący sposób aby ułatwić dostęp do danych testowych w tabeli: 

~~~
[php]
class Lookup extends CActiveRecord
{
	private static $_items=array();

	public static function items($type)
	{
		if(!isset(self::$_items[$type]))
			self::loadItems($type);
		return self::$_items[$type];
	}

	public static function item($type,$code)
	{
		if(!isset(self::$_items[$type]))
			self::loadItems($type);
		return isset(self::$_items[$type][$code]) ? self::$_items[$type][$code] : false;
	}

	private static function loadItems($type)
	{
		self::$_items[$type]=array();
		$models=self::model()->findAll(array(
			'condition'=>'type=:type',
			'params'=>array(':type'=>$type),
			'order'=>'position',
		));
		foreach($models as $model)
			self::$_items[$type][$model->code]=$model->name;
	}
}
~~~

Nasz kod głównie dostarcza dwóch metod statycznych: `Lookup::items()` oraz `Lookup::item()`. Pierwsza zwraca listę łańcuchów należących do określonego typu danych, gdy druga zwraca poszczególny łańcuch dla danego typu danych i wartości.

Nasza baza danych blogu jest wstępnie wypełniona przez dwa typy: status wiadomości `PostStatus` oraz status komentarza `CommentStatus`. Pierwszy przedstawia możliwe statusy wiadomości, drugi zaś komentarzy.

Aby ułatwić czytanie naszego kodu, zadeklarowaliśmy również zestaw stałych, do reprezentowania wartości całkowitych statusów. Powinniśmy używać tych stałych w naszym kodzie kiedy odnosimy się do odpowiedniej wartości statusu.

~~~
[php]
class Post extends CActiveRecord
{
	const STATUS_DRAFT=1;
	const STATUS_PUBLISHED=2;
	const STATUS_ARCHIVED=3;
	......
}
~~~

Dlatego też możemy wywołać metodę `Lookup::items('PostStatus')` aby uzyskać listę możliwych statusów wiadomości (łańcuchy tekstowe indeksowane przez odpowiadające im wartości całkowite) jak również wywoływać metodę `Lookup::item('PostStatus', Post::STATUS_PUBLISHED)` aby otrzymać łańcuch reprezentujący status "opublikowany".


<div class="revision">$Id: post.model.txt 3366 2011-08-03 21:24:26Z alexander.makarow $</div>