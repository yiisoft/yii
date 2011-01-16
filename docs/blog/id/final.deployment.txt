Tune-up dan Pemasangan Terakhir
============================

Kita sudah mendekati tahap akhir aplikasi blog kita. Sebelum pemasangan, kita lakukan beberapa tune-up terlebih dahulu.


Mengubah Home Page
------------------

Kita ubah halaman list post sebagai home page. Kita modifikasi [konfigurasi aplikasi](http://www.yiiframework.com/doc/guide/basics.application#application-configuration) menjadi berikut,

~~~
[php]
return array(
	......
	'defaultController'=>'post',
	......
);
~~~

> Tip|Tips: Karena `PostController` sudah mendeklarasikan `index` sebagai action default, ketika kita mengakses home page aplikasi, kita akan melihat hasil yang dihasilkan oleh action `index` dari controller post.


Mengaktifkan Schema Caching
-----------------------

Karena ActiveRecord tergantung pada metadata tentang tabel untuk menentukan informasi kolom, maka diperlukan waktu untuk membaca metadata dan menganalisanya. Sebenarnya ini tidak menjadi masalah selama tahap pembuatan, tetapi ketika sudah di tahap produksi, maka sebetulnya proses ini benar-benar merupakan buang-buang waktu jika skema database tidak berubah. Oleh karenanya, kita harus mengaktifkan schema caching dengan memodifikasikan konfigurasi aplikasi sebagai berikut,

~~~
[php]
return array(
	......
	'components'=>array(
		......
		'cache'=>array(
			'class'=>'CDbCache',
		),
		'db'=>array(
			'class'=>'system.db.CDbConnection',
			'connectionString'=>'sqlite:/wwwroot/blog/protected/data/blog.db',
			'schemaCachingDuration'=>3600,
		),
	),
);
~~~

Pada code di atas, pertama-tama kita menambah sebuah component `cache` yang menggunakan database default SQLite sebagai penyimpan caching. Jika server kita dilengkapi dengan extension caching lain, seperti APC, kita dapat ubah konfigurasi untuk menggunakannya. Kita juga dapat memodifikasikan komponen `db` dengan mengatur properti [schemaCachingDuration|CDbConnection::schemaCachingDuration] menjadi 3600, yang artinya data skema database yang di-parse akan tetap valid selama 3600 detik.


Mematikan Debugging Mode
------------------------

Kita memodifikasikan file skrip `/wwwroot/blog/index.php` dengan menghilangkan baris yang mendefinisikan `YII_DEBUG`. Konstan ini sangat berguna pada saat tahap pengembangan karena memungkinkan Yii untuk menampilkan lebih banyak informasi debug ketika terjadi error. Namun, ketika aplikasi sudah berjalan di tahap produksi, maka menampilkan informasi debug bukanlah sebuah ide yang bagus karena bisa saja berisi informasi yang sensitif seperti di mana file skrip terletak, dan isi dari file, dan sebagainya.


Memasang Aplikasi
-------------------------

Proses pemasangan final pada umumnya melibatkan peng-copy-an direktori `/wwwroot/blog` ke direktori target. Ceklis berikut menampilkan setiap langkah yang diperlukan:

 1. Install Yii di tempat target jika tidak memungkinkan;
 2. Copy seluruh direktori `/wwwroot/blog` ke tempat target;
 3. Edit file skrip entri `index.php` dengan menunjukkan variabel `$yii` ke file bootstrap Yii yang baru;
 4. Edit file `protected/yiic.php` dengan mengatur variabel `$yiic` menjadi file `yiic.php` Yii yang baru;
 5. Ubah permission dari direktori `assets` dan `protected/runtime` supaya dapat ditulis oleh proses Web server.


<div class="revision">$Id: final.deployment.txt 2017 2010-04-05 17:12:13Z alexander.makarow $</div>