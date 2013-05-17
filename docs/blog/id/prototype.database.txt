Memasang Database
===================

Setelah membuat sebuah aplikasi kerangka dan menyelesaikan rancangan database, di seksi ini kita akan membuat sebuah database blog dan membangun koneksi ke database dalam aplikasi kerangka.


Membuat Database
-----------------

Kita memilih membuat sebuah database SQLite. Karena dukungan database Yii dibangun di atas [PDO](http://www.php.net/manual/en/book.pdo.php), kita dapat dengan mudah mengganti jenis DBMS dengan gampan (misalnya MySQL, PostgreSQL) tanpa perlu mengubah kode komputer.

Kita membuat sebuah file database `blog.db` di bawah direktori `/wwwroot/blog/protected/data`. Perhatikan bahwa direktori dan file database haruslah dapat ditulis oleh proses server Web, seperti yang dperlukan oleh SQLite. Kita dapat meng-copy file database dari demo blog di dalam instalasi Yii di  `/wwwroot/yii/demos/blog/protected/data/blog.db`. Kita juga dapat membuat database dengan menjalankan statement di dalam file `/wwwroot/yii/demos/blog/protected/data/schema.sqlite.sql`.

> Tip|Tips: Untuk mengeksekusi statement SQL, kita bisa menggunakan command line `sqlite3`, yang dapat ditemukan di [website resmi SQLite](http://www.sqlite.org/download.html).


Membangun Koneksi Database
--------------------------------

Untuk menggunakan database blog di dalam aplikasi kerangka yang kita buat, kita perlu memodifikasi [konfigurasi aplikasi](http://www.yiiframework.com/doc/guide/basics.application#application-configuration)  yang di simpan di dalam skrip PHP `/wwwroot/blog/protected/config/main.php`. Skrip mengembalikan sebuah array yang berkaitan terdiri dari pasangan nama-nilai, masing-masing digunakan untuk menginisialisasi properti (yang dapat ditulisi) dari [instance aplikasi](http://www.yiiframework.com/doc/guide/basics.application).

Kami mengkonfigurasi komponen `db` sebagai berikut,

~~~
[php]
return array(
	......
	'components'=>array(
		......
		'db'=>array(
			'connectionString'=>'sqlite:/wwwroot/blog/protected/data/blog.db',
			'tablePrefix'=>'tbl_',
		),
	),
	......
);
~~~

Konfigurasi di atas menjelaskan bahwa kita memiliki sebuah [komponen aplikasi](http://www.yiiframework.com/doc/guide/basics.application#application-component) `db` yang properti `connectionString`-nya harus diinisialisasikan sebagai `sqlite:/wwwroot/blog/protected/data/blog.db` dan properti `tablePrefix` berupa `tbl_`.

Dengan konfigurasi ini, kita dapat mengakses objek koneksi DB dengan menggunakan `Yii::app()->db` di tempat manapun di dalam kode kami. Harap diingat `Yii::app()` mengembalikan instance aplikasi yang kita buat di dalam skrip entri. Jika anda tertarik pada metode dan properti apa saja yang ada di koneksi DB, anda bisa merujuk ke [referensi kelas|CDbConnection]-nya. Namun, pada kebanyakan kasus kita tidak akan menggunakan koneksi DB secara langsung. Kita akan menggunakan apa yang disebut [ActiveRecord](http://www.yiiframework.com/doc/guide/database.ar) untuk mengakses database.

Khusus properti `tablePrefix` yang diatur di konfigurasi akan dijelaskan lebih sedikit. Properti ini memberitahukan koneksi `db` bahwa dia harus memperhatikan fakta bahwa kita menggunakan `tbl_` sebagai prefiks nama tabel dalam database kita. Jika sebuah statement SQL ada token ditutup dengan kurung kurawal ganda (seperti `{{post}}`), maka koneksi `db` harus menerjemahkannya menjadi sebuah nama dengan prefiks tabel (misalnya `tbl_post`) sebelum mengirimnya ke DBMS untuk dieksekusi. Fitur ini sangat berguna khususnya apabila di masa yang akan datang kita perlu mengubah prefiks nama tabel tanpa mengubah kode. Misalnya, jika kita membuat sebuah content management system (CMS), kita bisa memanfaatkan fitur ini sehingga ketika diinstal di lingkungan baru, memungkinkan pengguna untuk memilih prefiks tabel yang diinginkan mereka.

> Tip|Tips: Jika anda ingin menggunakan MySQL alih-alih SQLite untuk menyimpan data, anda bisa membuat
> sebuah database bernama `blog` menggunakan statement SQL di dalam
> `/wwwroot/yii/demos/blog/protected/data/schema.mysql.sql`. Kemudian modifikasi konfigurasi aplikasi
> menjadi seperti berikut ini,
>
> ~~~
> [php]
> return array(
>     ......
>     'components'=>array(
>         ......
>         'db'=>array(
>             'connectionString' => 'mysql:host=localhost;dbname=blog',
>             'emulatePrepare' => true,
>             'username' => 'root',
>             'password' => '',
>             'charset' => 'utf8',
>             'tablePrefix' => 'tbl_',
>         ),
>     ),
> 	......
> );
> ~~~


<div class="revision">$Id: prototype.database.txt 2332 2010-08-24 20:55:36Z mdomba $</div>