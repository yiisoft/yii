Manajemen URL
=============

Manajemen URL yang lengkap pada aplikasi Web terdiri dari dua aspek. Pertama,
ketika permintaan pengguna berasal dari sebuah URL, aplikasi harus menguraikannya
ke dalam sebuah parameter yang dapat dimengerti. Kedua, aplikasi harus menyediakan
cara pembuatan URL agar URL yang dibuat dapat dimengerti oleh aplikasi
Untuk aplikasi Yii, ini dilakukan dengan bantuan
[CUrlManager].

Membuat URL
-----------

Meskipun URL bisa dibuat secara manual dalam view controller, seringkali lebih
jauh fleksibel jika membuatnya secara dinamis:

~~~
[php]
$url=$this->createUrl($route,$params);
~~~

di mana `$this` merujuk ke instance controller; `$route` menetapkan
[rute](/doc/guide/basics.controller#route) permintaan; dan `$params`
adalah daftar parameter `GET` yang akan ditambahkan ke URL.

Secara default, URL yang dibuat dengan  [createUrl|CController::createUrl] adalah
apa yang disebut dengan format `get`. Sebagai contoh, `$route='post/read'` dan
`$params=array('id'=>100)`, kita akan mendapatkan URL seperti berikut:

~~~
/index.php?r=post/read&id=100
~~~

di mana parameter terlihat dalam string query sebagai daftar `Nama=Nilai`
yang disambung dengan karakter ampersand (karakter &), dan parameter `r` menetapkan
permintaan [rute](/doc/guide/basics.controller#route). Format URL ini
tidak ramah-pengguna karena memakai beberapa karakter bukan-kata.

Kita ingin menjadikan URL di atas terlihat lebih bersih dan lebih jelas dengan
menggunakan apa yang disebut format `path` yang mengeliminir string query dan
menyimpan parameter GET ke dalam info path bagian dari URL:

~~~
/index.php/post/read/id/100
~~~

Untuk mengubah format URL, kita harus mengkonfigurasi komponen aplikasi
[urlManager|CWebApplication::urlManager] agar
[createUrl|CController::createUrl] bisa beralih secara otomatis ke format baru
dan aplikasi mengerti dengan benar URL baru tersebut:

~~~
[php]
array(
	......
	'components'=>array(
		......
		'urlManager'=>array(
			'urlFormat'=>'path',
		),
	),
);
~~~

Catatan bahwa kita tidak ingin menetapkan kelas komponen
[urlManager|CWebApplication::urlManager] karena ia adalah
pra-deklarasi sebagai [CUrlManager] dalam [CWebApplication].

> Tip: URL yang dihasilkan oleh metode [createUrl|CController::createUrl]
adalah URL relatif. Untuk mendapatkan URL absolut, kita dapat mengawalinya dengan
`Yii::app()->hostInfo`, atau memanggil [createAbsoluteUrl|CController::createAbsoluteUrl].

URL Ramah-Pengguna
------------------

Ketika `path` dipakai sebagai format URL, kita dapat menetapkan beberapa aturan URL untuk
membuat URL kita bahkan lebih ramah-pengguna. Sebagai contoh, kita dapat membuat URL sesingkat
`/post/100`, daripada
`/index.php/post/read/id/100` yang cukup panjang. Aturan URL dipakai oleh [CUrlManager] baik
untuk pembuatan URL maupun keperluan penguraian.

Untuk menetapkan aturan URL, kita harus mengkonfigurasi properti [rules|CUrlManager::rules]
pada komponen aplikasi
[urlManager|CWebApplication::urlManager]:

~~~
[php]
array(
	......
	'components'=>array(
		......
		'urlManager'=>array(
			'urlFormat'=>'path',
			'rules'=>array(
				'pattern1'=>'route1',
				'pattern2'=>'route2',
				'pattern3'=>'route3',
			),
		),
	),
);
~~~

Aturan ditetapkan sebagai array dengan pasangan pola-rute (pattern-route), masing-masing
berkaitan dengan satu aturan. Pola aturan harus ekspresi reguler yang benar
tanpa pemisah dan pembeda. Ini dipakai untuk menyamakan bagian info path
URL. Dan [rute](/doc/guide/basics.controller#route) harus merujuk ke controller rute yang valid.

Selain format pola-rute demikian, aturan juga dapat ditentukan
dengan opsi yang dikustomisasi, seperti berikut ini :

~~~
[php]
'pattern1'=>array('route1', 'urlSuffix'=>'.xml', 'caseSensitive'=>false)
~~~

Mulai versi 1.1.7, format berikut juga dapat digunakan (yakni
pola yang ditetapkan sebagai elemen array), yang memungkinkan menentukan beberapa
aturan dengan pola yang sama:

~~~
[php]
array('route1', 'pattern'=>'pattern1', 'urlSuffix'=>'.xml', 'caseSensitive'=>false)
~~~

Di atas, array tersebut berisi daftar opsi-opsi extra untuk aturan. Option yang memungkinkan
akan dijelaskan pada bawah ini:

   - [pattern|CUrlRule::pattern]: pola yang digunakan untuk mencocokkan serta membuat URL.
Opsi ini sudah tersedia pada versi 1.1.7.

   - [urlSuffix|CUrlRule::urlSuffix]: merupakan akhir dari URL yang dipakai pada atran. Secara default bernilai null
yang berarti menggunakan nilai [CUrlManager::urlSuffix].

   - [caseSensitive|CUrlRule::caseSensitive]: menentukan aturan bersifat case-sensitive atau tidak. Secara default bernilai null,
yang artinya mengikuti nilai [CUrlManager::caseSensitive].

- [defaultParams|CUrlRule::defaultParams]: Parameter GET default (name=>value) yang disediakan oleh aturan ini.
Ketika aturan ini digunakan untuk parsing permintaan yang masuk, nilai-nilai tersebut yang dideklarasi dalam properti ini
akan diinjeksi ke $_GET.

   - [matchValue|CUrlRule::matchValue]: menentukan apakah nilai parameter GET harus sesuai dengan sub-pola
dalam aturan ketika membuat sebuah aturan. Secara default bernilai null,
yang artinya menggunakan nilai [CUrlManager::matchValue]. Jika properti ini adalah false, itu artinya
sebuah aturan akan digunakan untuk membuat sebuah URL jika rute dan nama parameter pas dengan yang diberikan.
Jika properti ini di-ste true, maka nilai parameter yang diberikan juga harus sesuai dengan sub-pola parameter bersangkutan.
Harap dicatat bahwa jika mengeset properti ini menjadi true akan menurunkan kinerja.

- [verb|CUrlRule::verb]: verb HTTP (misalnya `GET`, `POST`, `DELETE`) jadi aturan harus cocok
dengan urutan yang digunakan untuk parsing request sekarang. Default-nya adalah null, yang artinya aturan bersangkutan akan cocok dengan verb HTTP apa saja.
Jika sebuah aturan bisa cocok lebih dari satu verb, maka verb-verb ini harus ditulis dengan dipisah oleh koma. Ketika sebuah aturan tidak cocok
dengan verb yang ditentukan, maka akan dilangkahi pada saat proses parsing request. Opsi ini hanya 
digunakan untuk parsing request. Tujuan utama terdapat opsi ini supaya mendukung URL RESTful.
Opsi ini sudah tersedia semenjak versi 1.1.7.

   - ]parsingOnly|CUrlRule::parsingOnly]: menentukan apakah aturan digunakan hanya untuk request saja.
Nilai default false, yang artinya sebuah rule digunakan untuk parsing dan juga pembuatan URL.
Opsi ini tersedia di versi 1.1.7.


Menggunakan Parameter Bernama
----------------------------------------------------------

Aturan dapat dikaitkan dengan beberapa parameter GET. Parameter GET ini
muncul dalam pola aturan sebagai token khusus dengan format sebagai berikut:

~~~
 	<ParamName:ParamPattern>
~~~

di mana `ParamName` menetapkan nama parameter GET, dan opsional
`ParamPattern` menetapkan ekspresi reguler yang harus dipakai untuk
menyamakan nilai parameter GET. Dalam hal ketika `ParamPattern` disertakan,
ini berarti parameter harus sesuai dengan setiap karakter kecuali garis miring `/`.
Ketika membuat URL, token parameter ini
akan diganti dengan nilai parameter terkait; saat penguraian
URL, parameter GET tersebut akan dipopulasi dengan hasil
penguraian.

Mari kita gunakan beberapa contoh untuk menjelaskan bagaimana aturan URL bekerja. Kita anggap bahwa
set aturan kita terdiri dari tiga aturan:

~~~
[php]
array(
	'posts'=>'post/list',
	'post/<id:\d+>'=>'post/read',
	'post/<year:\d{4}>/<title>'=>'post/read',
)
~~~

   - Memanggil `$this->createUrl('post/list')` menghasilkan `/index.php/posts`.
Aturan pertama diterapkan.

   - Memanggil `$this->createUrl('post/read',array('id'=>100))` menghasilkan
`/index.php/post/100`. Aturan kedua diterapkan.

   - Memanggil `$this->createUrl('post/read',array('year'=>2008,'title'=>'a
sample post'))` menghasilkan `/index.php/post/2008/a%20sample%20post`. Aturan
ketiga diterapkan.

   - Memanggil `$this->createUrl('post/read')` menghasilkan
`/index.php/post/read`. Tidak ada satupun aturan yang diterapkan.

Secara ringkas, saat menggunakan [createUrl|CController::createUrl] untuk membuat
URL, rute dan parameter GET mengopernya ke metode yang dipakai untuk
memutuskan aturan URL mana yang akan diterapkan. Jika setiap parameter dikaitkan
dengan sebuah aturan dan ditemukan dalam parameter GET yang dioper ke
[createUrl|CController::createUrl], dan jika rute aturan juga
sesuai dengan parameter rute, aturan akan dipakai untuk membuat URL.

Jika parameter GET dioper ke [createUrl|CController::createUrl] lebih
dari yang dibutuhkan oleh aturan, parameter tambahan akan muncul dalam
string query. Sebagai contoh, jika kita memanggil
`$this->createUrl('post/read',array('id'=>100,'year'=>2008))`, kita akan
mendapatkan `/index.php/post/100?year=2008`. Agar parameter tambahan ini
muncul dalam bagian info path, kita harus menambahkan `/*` pada aturan.
Oleh karena itu, dengan aturan `post/<id:\d+>/*`, kita bisa mendapatkan URL seperti
`/index.php/post/100/year/2008`.

Seperti yang sudah disebutkan, kegunaan lain aturan URL adalah untuk mengurai
permintaan URL. Secara alami, ini adalah proses terbalik pembuatan URL. Sebagai contoh,
ketika pengguna meminta `/index.php/post/100`, aturan kedua dalam contoh
di atas akan berlaku, menjadi solusi dalam rute `post/read` dan parameter
GET `array('id'=>100)` (accessible via `$_GET`).


> Note|Catatan: Menggunakan aturan URL akan menurunkan kinerja aplikasi. Ini
disebabkan saat mengurai URL yang diminta, [CUrlManager] akan mencoba mencari
setiap aturan sampai salah satunya diterapkan. Oleh karenanya, aplikasi Web dengan
lalu lintas tinggi harus meminimalisir pemakaian
aturan URL.


Parameterisasi Rute
---------------------------------

Kita dapat merujuk parameter bernama dalam bagian rute pada sebuah
aturan. Ini mengijinkan aturan untuk diterapkan pada multi rute berdasarkan pada kriteria
yang sesuai. Ini juga membantu mengurangi jumlah aturan yang diperlukan oleh aplikasi,
Selanjutnya tentunya meingkatkan kinerja secara keseluruhan.

Kita gunakan contoh berikut untuk menggambarkan bagaimana parameterisasi rute
dengan parameter bernama:

~~~
[php]
array(
	'<_c:(post|comment)>/<id:\d+>/<_a:(create|update|delete)>' => '<_c>/<_a>',
	'<_c:(post|comment)>/<id:\d+>' => '<_c>/read',
	'<_c:(post|comment)>s' => '<_c>/list',
)
~~~

Dalam contoh di atas, kita menggunakan parameter bernama dalam bagian rute pada sebuah aturan:
`_c` dan `_a`. Yang pertama sesuai dengan ID controller yang bisa berupa `post` ataupun `comment`,
sementara yang kedua sesuai dengan ID aksi yakni `create`, `update` atau `delete`.
Anda dapat menamai parameter secara berbeda selama tidak bertabrakan dengan parameter
GET yang terlihat dalam URL.

Menggunakan aturan di atas, URL `/index.php/post/123/create` akan diuraikan sebagai
rute `post/create` dengan parameter GET `id=123`.
Dan menghasilkan `comment/list` dan parameter GET `page=2`, kita dapat membuat URL
`/index.php/comments?page=2`.


Memparameterkan Hostname(Parameterizing Hostname)
------------------------------------------------------------------------------------------

Yii sudah memungkinkan untuk menyertakan hostname ke dalam
aturan penguraian dan pembuatan URL. Kita dapat mengeluarkan bagian dari hostname
untuk menjadi sebuah parameter GET. Misalnya, URL `http://admin.example.com/en/profile`
dapat diuraikan menjadi parameter GET `user=admin` dan `lang=en`. Di lain sisi, aturan dengan hostname
juga dapat digunakan untuk membuat URL dengan hostname terparameterisasi.

Untuk menggunakan hostname terparameterisasi, cukup mendeklarasi aturan URL dengan info host, seperti:

~~~
[php]
array(
	'http://<user:\w+>.example.com/<lang:\w+>/profile' => 'user/profile',
)
~~~

Dari contoh di atas, segmen pertama pada hostname akan diperlakukan sebagai parameter `user`
sedangkan segmen pertama pada info jalur berupa parameter `lang`.
Aturan menyesuaikan diri dengan rute `profil/pengguna`.

Harap diperhatikan bahwa [CUrlManager::showScriptName|CUrlManager::showScriptName] tidak akan berefek ketika
URL dibuat dengan menggunakan sebuah aturan hostname terparameter.

Juga dicatat bahwa aturan hostname berparameter TIDAK boleh mengandung sub-folder jika aplikasi
berada di bawah sebuah sub-folder Web root. Contohnya, jika sebuah aplikasi berada di `http://www.example.com/sandbox/blog`,
maka kita masih menggunakan aturan URL yang sama seperti
 yang dijelaskan di atas tanpa sub-folder sandbox/blog.

Menyembunyikan `index.php`
------------------------------------------------

Ada satu hal lagi yang dapat kita lakukan untuk membersihkan URL kita, misalnya
menyembunyikan entri skrip `index.php` dalam URL. Ini mengharuskan kita untuk
mengkonfigurasi server Web dan juga komponen aplikasi
[urlManager|CWebApplication::urlManager].

Pertama kita harus mengkonfigurasi server Web agar URL tanpa skrip entri
masih dapat ditangani oleh skrip entri. Untuk [Apache HTTP
server](http://httpd.apache.org/), ini bisa dikerjakan dengan menghidupkan URL
rewriting engine dan menetapkan beberapa aturan penulisannya. Kita dapat membuat
 file `/wwwroot/blog/.htaccess` dengan konten berikut.
Catatan bahwa konten yang sama juga bisa disimpan dalam file konfigurasi Apache
di dalam elemen `Directory` untuk `/wwwroot/blog`.

~~~
RewriteEngine on

# jika ia berupa direktori atau file sudah ada, gunakan langsung
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

# sebaliknya teruskan ia ke index.php
RewriteRule . index.php
~~~

Selanjutnya kita mengkonfigurasi properti [showScriptName|CUrlManager::showScriptName]
pada komponen [urlManager|CWebApplication::urlManager] menjadikannya
`false`.

Sekarang jika kita memanggil `$this->createUrl('post/read',array('id'=>100))`, kita akan
mendapatkan URL `/post/100`. Lebih penting lagi, URL ini bisa dikenal oleh
aplikasi Web kita dengan benar.

Memalsukan Sufiks URL
------------------------------------------

Kita juga bisa menambahkan beberapa sufiks ke URL kita. Sebagai contoh, kita dapat
memiliki `/post/100.html` daripada `/post/100`. Ini membuat URL lebih terlihat sebagai
halaman Web statis. Untuk melakukannya, cukup konfigurasi komponen
[urlManager|CWebApplication::urlManager] dengan menyetel properti
[urlSuffix|CUrlManager::urlSuffix] ke sufiks yang Anda inginkan.


Menggunakan Custom Class URL Rules (Kelas Aturan URL Buatan Sendiri)
-----------------------------

> Note|Catatan: Menggunakan kelas aturan URL buatan sendiri didukung mulai dari versi 1.1.8

Secara default, setiap aturan URL yang dibuat di [CUrlManager] diwakili sebagai
sebuah objek [CUrlRule] yang melakukan penguraian dan pembuatan URL-URL 
berdasarkan aturan yang ditetapkan. Walaupun [CUrlRule] cukup fleksibel untuk menangani
sebagian besar format URL, tetapi terkadang kita butuh jauh lebih dari itu.

Misalnya, dalam sebuah website dealer mobil, kita mungkin ingin format URL seperti 
demikian `/Manufacturer/Model`, dengan `Manufacturer` dan `Model` harus cocok dengan data
di dalam tabel database. Kelas [CUrlRule] tidak akan berjalan dikarenakan kelas ini bergantung
pada regular expressions yang dideklarasikan secara statis tanpa pengetahuan mengenai database.

Kita dapat menulis kelas aturan URL dengan menurunkan [CBaseUrlRule] dan menggunakannya
di dalam satu atau lebih aturan URL. Dengan contoh website dealer mobil tadi, kita dapat 
mendeklarasikan aturan URL berikut,

~~~
[php]
array(
	// Standar aturan mapping '/' ke action 'site/index' 
	'' => 'site/index',

	// sebuah aturan mapping standar '/login' ke 'site/login' dan lainnya 
	'<action:(login|logout|about)>' => 'site/<action>',

	// Aturan buatan sendiri untuk menangani '/Manufacturer/Model'
	array(
	    'class' => 'application.components.CarUrlRule',
	    'connectionID' => 'db',
	),

	// Aturan standar untuk menangani 'post/update' dan lainnya
	'<controller:\w+>/<action:\w+>' => '<controller>/<action>',
),
~~~

Pada contoh di atas, kita menggunakan kelas aturan URL buatan sendiri `CarUrlRule` untuk menangani
format URL `/Manufacturer/Model`. Kelas ini dapat ditulis sebagai berikut:

~~~
[php]
class CarUrlRule extends CBaseUrlRule
{
	public $connectionID = 'db';

	public function createUrl($manager,$route,$params,$ampersand)
	{
		if ($route==='car/index')
		{
			if (isset($params['manufacturer'], $params['model']))
				return $params['manufacturer'] . '/' . $params['model'];
			else if (isset($params['manufacturer']))
				return $params['manufacturer'];
		}
		return false;  // this rule does not apply
	}

	public function parseUrl($manager,$request,$pathInfo,$rawPathInfo)
	{
		if (preg_match('%^(\w+)(/(\w+))?$%', $pathInfo, $matches))
		{
			// check $matches[1] and $matches[3] to see
			// if they match a manufacturer and a model in the database
			// If so, set $_GET['manufacturer'] and/or $_GET['model']
			// and return 'car/index'
		}
		return false;  // this rule does not apply
	}
}
~~~

Kelas URL buatan sendiri ini harus mengimplementasi dua method abstrak yang dideklarasikan di dalam [CBaseUrlRule]:

* [createUrl()|CBaseUrlRule::createUrl()]
* [parseUrl()|CBaseUrlRule::parseUrl()]

Selain penggunaan di atas, kelas aturan URL custom juga dapat diimplementasikan
tujuan lainnya. Misalnya, kita dapat menulis sebuah kelas aturan yang melakukan pencatatan 
URL parsing dan request pembuatan. Biasanya fungsi ini kita perlukan pada saat tahap pengembangan. Kita juga dapat
menulis kelas aturan untuk menampilkan halaman error 404 khusus apabila aturan URL request gagal diuraikan.
Harap dicatat, khusus kasus ini, aturan dari kelas spesial ini harus 
dideklarasikan sebagai aturan terakhir.

<div class="revision">$Id: topics.url.txt 3329 2011-06-28 08:31:35Z mdomba $</div>
