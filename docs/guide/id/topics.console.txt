Aplikasi Konsole
================

Aplikasi konsol dipakai terutama oleh aplikasi web untuk melakukan pekerjaan
offline, seperti pembuatan kode, pencarian kompilasi indeks, pengiriman
email, dll. Yii menyediakan sebuah kerangka kerja untuk penulisan aplikasi konsol
secara sistematis dan berorientasi-objek. Yii memungkinkan aplikasi konsol mengakses
sumber daya (seperti koneksi DB) yang digunakan oleh aplikasi Web online.


Tinjauan
---------

Yii mewakilkan setiap tugas console dengan istilah [command|CConsoleCommand].
Sebuah command/perintah console merupakan kelas turunan dari [CConsoleCommand].

Ketika kita menggunakan tool `yiic webap` untuk membuat kerangka awal aplikasi Yii,
kita mungkin menemukan dua file di dalam direktori `protected`:

* `yiic`: ini merupakan sebuah skrip yang dapat dieksekusikan pada Linux/Unix;
* `yiic.bat`: ini merupakan sebuah file batch yang dapat dieksekusikan di Windows.

Di dalam window console, kita dapat memasukkan command berikut:

~~~
cd protected
yiic help
~~~

Ini akan menampilkan daftar command console yang tersedia. Secara default, command
yang tersedia termasuk yang disediakan oleh framework Yii (disebut **system commands/perintah sistem**)
dan yang dibuat oleh user untuk aplikasi individu (disebut **user commands/perintah user**).

Untuk melihat bagaimana menggunakan sebuah command, kita dapat mengeksekusi

~~~
yiic help <command-name>
~~~

Dan mengeksekusikan sebuah command, kita dapat menggunakan format perintah berikut:

~~~
yiic <command-name> [parameters...]
~~~


Membuat Command :
-----------------

Command console disimpan sebagai file kelas di dalam direktor yang ditentukan
[CConsoleApplication::commandPath]. Secara default, properti ini merujuk ke
`protected/commands`.

Sebuah command console harus berupa turunan dari [CConsoleCommand]. Nama kelas
harus dalam format `XyzCommand`, dengan `Xyz` merujuk ke nama command dengan
nama depan berupa huruf kapital. Misalnya, sebuah command `sitemap` harus menggunakan
nama kelas `SitemapCommand`. Nama command console bersifat case-sensitive.

> Tip: Dengan mengkonfigurasi [CConsoleApplication::commandMap], seseorang juga dapat
> memiliki kelas command dengan konvensi penamaan yang berbedan dan terletak di
> direktori yang berbeda.

Untuk membuat command baru, kadang-kadang diperlukan meng-override [CConsoleCommand::run()]
atau membuat satu atau beberapa action command (seperti yang dijelaskan pada bagian berikutnya).

Ketika menjalankan console command, method [CConsoleCommand::run()] akan
dipanggil oleh aplikasi console. Parameter console command apapun akan di-pass
ke method juga, sesuai dengan signature dari method:

~~~
[php]
public function run($args) { ... }
~~~

di mana `$args` merupakan parameter ekstra yang diberikan dalam command line.

Di dalam console command, kita dapat menggunakan `Yii::app()` untuk mengakses instance aplikasi
console, melalui ini juga kita dapat mengakses sumber daya seperti koneksi database
(misalnya `Yii::app()->db`). Seperti yang bisa kita lihat, penggunaan ini sangat mirip
dengan yang dilakukan pada aplikasi Web.

> Info|Catatan: Mulai dari versi 1.1.1, kita juga dapat membuat perintah global 
yang dapat dibagi oleh *semua* aplikasi Yii pada mesin yang sama. Untuk melakukannya,
tentukan variabel environment bernama `YII_CONSOLE_COMMANDS` untuk menunjuk ke
direktori yang ada. Maka kemudian kita dapat meletakkan file kelas command gobal kita
ke dalam direktori itu.


Action Command Console
------------------------

> Note|Catatan: Fitur action command console sudah tersedia semenjak versi 1.1.5

Sebuah command console kadang diperlukan untuk mengatur parameter baris command yang berbeda, ada yang wajib diisi,
ada juga yang opsional. Sebuah command console juga mungkin perlu memberikan beberapa sub-command untuk mengurus
sub tugas yang berbeda. Hal-hal seperti ini dapat disederhanakan dengan menggunakan console command action.

Sebuah console command action adalah method di dalam kelas console command.
Sebuah nama metode harus berupa format `actionXyz`, dengan `Xyz` merujuk pada nama action
dengan huruf depan sebagai huruf kapital. Misalnya, sebuah metode bernama `actionIndex`
mendefinisikan sebuah action bernama `index`.

Untuk mengeksekusi action tertentu, kita menggunakan format command console berikut:

~~~
yiic <command-name> <action-name> --option1=value --option2=value2 ...
~~~

Sebuah pasangan opsi-nilai juga akan di-pass sebagai parameter bernama ke metode action.
Nilai sebuah opsi `xyz` akan di-pass ke parameter `$xyz` pada metode action.
Misalnya, jika kita mendefinisikan kelas command berikut:

~~~
[php]
class SitemapCommand extends CConsoleCommand
{
    public function actionIndex($type, $limit=5) { ... }
    public function actionInit() { ... }
}
~~~

Kemudian, command console berikut semuanya akan menjadi memanggil `actionIndex('News',5)`:

~~~
yiic sitemap index --type=News --limit=5

// $limit mengambil nilai default
yiic sitemap index --type=News

// $limit mengambil nilai default
// karena 'index' merupakan nilai default, kita akan menghilangkan nama aksi
yiic sitemap --type=News

// urutan opsi tidak berpengaruh apa-apa
yiic sitemap index --limit=5 --type=News
~~~

Jika sebuah opsi diberikan tanpa nilai (misalnya `--type` alih-alih `--type=News`), maka nilai parameter
aksi bersangkutan akan diasumsi sebagai `true`.

> Note|Catatan: Kita tidak mendukung format opsi alternatif seperti
> `--type News`, `-t News`.

Sebuah parameterdapat mengambil nilai array dengan mendeklarasi isyarat jenis array:

~~~
[php]
public function actionIndex(array $types) { ... }
~~~

Untuk memberikan nilai array, kita cukup mengulangi opsi yang sama dalam command line :

~~~
yiic sitemap index --types=News --types=Article
~~~

Command di atas akan memanggil `actionIndex(array('News', 'Article'))`.


Mulai dari versi 1.1.6, Yii juga mendukung parameter action anonim dan opsi global.

Parameter anonim maksudnya adalah parameter command line yang tidak ada dalam format opsi.
Misalnya, dalam sebuah command `yiic sitemap index --limit=5 News`, kita memiliki sebuah parameter anonim yang nilainya
adalah `News` sedangkan parameter bernama adalah `limit` berisi nilai 5.

Untuk menggunakan parameter anonim, sebuah action command harus dideklarasikan sebagai `$args`. Misalnya,

~~~
[php]
public function actionIndex($limit=10, $args=array()) {...}
~~~

Array `$args` akan memegang seluruh nilai parameter anonim.

Opsi global merujuk pada opsi command line yang dibagikan ke semua action dalam sebuah command.
Misalnya, di dalam sebuah command yang memiliki beberapa action, kita mungkin ingin semua action mengenali
opsi bernama `verbose`. Kita bisa saja mendeklarasikan parameter `$verbose` pada semua method action,
namun cara yang lebih baik adalah mendeklarasikannya sebagai **public variabel member** dari kelas command, yang akan
membuat `verbose` menjadi opsi global:

~~~
[php]
class SitemapCommand extends CConsoleCommand
{
	public $verbose=false;
	public function actionIndex($type) {...}
}
~~~

Kode di atas akan memungkinkan kita mengeksekusi sebuah opsi `verbose`:

~~~
yiic sitemap index --verbose=1 --type=News
~~~


Mengkustomisasi Aplikasi Konsole
--------------------------------

Secara default, jika sebuah aplikasi dibuat dengan menggunakan tool `yiic webapp`, maka konfigurasi
untuk aplikasi console akan menjadi `protected/config/console.php`. Seperti file konfigurasi aplikasi Web,
ini merupakan skrip PHP yang akan mengembalikan nilai awal properti
untuk instance aplikasi console. Oleh karenanya, setiap properti publik dari
[CConsoleApplication] dapat dikonfigurasi file ini

Karena command console sering dibuat untuk membantu aplikasi Web, mereka perlu
mengakses sumber daya (seperti koneksi DB) yang digunakan oleh aplikasi Web. Kita dapat
melakukan begini di file konfigurasi:

~~~
[php]
return array(
	......
	'components'=>array(
		'db'=>array(
			......
		),
	),
);
~~~

Seperti yang Anda lihat, format konfigurasi sangat mirip dengan 
apa yang kita lakukan pada konfigurasi aplikasi Web. Ini dikarenakan kelas [CConsoleApplication] dan [CWebApplication]
berbagi kelas dasar yang sama.


<div class="revision">$Id: topics.console.txt 2867 2011-01-15 10:22:03Z haertl.mike $</div>
