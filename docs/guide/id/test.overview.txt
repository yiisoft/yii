Testing
========

Uji coba merupakan proses pengembangan piranti lunak yang tidak bisa diabaikan. Disadari atau tidak, kita selalu melakukan uji coba setiap saat ketika mengembangkan aplikasi Web. Misalnya, ketika kita menulis sebuah kelas di PHP, kita mungkin menggunakan beberapa `echo` atau `die` untuk memastikan implementasi kita benar. Ketika kita mengimplementasi ke halaman Web yang berisi form HTML yang kompleks, kita juga mungkin mencoba menginput beberapa data untuk memastikan halaman web berinteraksi sesuai harapan. Pengembang yang lebih ahli akan menulis beberapa kode untuk mengotomatisasi proses ini sehingga setiap kali ingin melakukan uji coba, cukup memanggil kodenya dan komputer yang akan mengerjakannya. Proses ini dikenal dengan sebutan *otomatisasi test*, yang merupakan topik utama dalam bagian ini.

Uji coba yang disediakan oleh Yii adalah *uji coba unit(unit testing)* dan *uji coba fungsional(functional testing)*.

Sebuah uji coba unit akan memeriksa apakah kode dari sebuah unit tunggal bekerja sesuai keinginan. Dalam pemograman berbasis objek, unit kode yang paling dasar adalah sebuah kelas. Sehingga, tujuan utama sebuah uji coba unit adalah menguji apakah setiap metode antarmuka kelas bekerja dengan benar. Dengan memberikan parameter input yang berbeda, uji coba ini memeriksa apakah metode bersangkutan memberikan nilai yang diharapkan. Uji coba unit biasanya dikembangkan oleh orang yang menulis kelas yang bersangkutan.

Sebuah uji coba fungsional adalah uji coba yang memeriksa sebuah fitur (seperti manajamen post dalam sistem blog) bekerja sesuai harapan. Dibandingkan dengan uji coba unit, sebuah uji coba fungsional berada di posisi lebih atas, karena umumnya sebuah fitur yang akan diperiksa, melibatkan beberapa kelas. Uji coba fungsional biasanya dikembangkan oleh orang yang sangat memahami persyaratan sistem (dapat berupa developer atau quality engineer).


Test-Driven Development
---------------------------------------

Berikut ini merupakan siklus pengembangan yang dikenal dengan [test-driven development (TDD)](http://en.wikipedia.org/wiki/Test-driven_development):

 1. Buat sebuah test yang melingkupi sebuah fitur yang ingin diimplementasi. Uji coba ini diharapkan fail(gagal) pada eksekusi pertama karena fitur bersangkutan masih belum diimplementasikan.
 2. Jalankan seluruh test dan pastikan test baru fail.
 3. Tulis sebuah kode yang membuat test baru ini pass (lolos).
 4. Jalankan semua uji coba dan pastikan semuanya pass.
 5. Refactor kode yang baru ditulis dan pastikan bahwa test masih tetap pass.

Ulangi langkah 1 sampai 5 guna meningkatkan fungsionalitas dalam implementasi.


Test Environment Setup
---------------------------------------

Fitur uji coba yang disediakan Yii memerlukan [PHPUnit](http://www.phpunit.de/) 3.5+ dan [Selenium Remote Control](http://seleniumhq.org/projects/remote-control/) 1.0+. Oleh karenanya, silahkan melihat dokumentasi masing-masing bagaimana melakukan instalasi PHPUnit dan Selenium Remote Control.

Ketika kita menggunakan perintah konsol `yiic webapp` untuk membuat aplikasi Yii yang baru, Yii akan menghasilkan beberapa file dan direktori berikut supaya kita dapat menulis dan menguji percobaan:

~~~
testdrive/
   protected/                berisi file-file aplikasi yang terproteksi
      tests/                 berisi uji coba-uji coba untuk aplikasi
         fixtures/           berisi fixture database
         functional/         berisi uji coba fungsional
         unit/               berisi uji coba unit
         report/             berisi laporan code-coverage
         bootstrap.php       script yang dijalankan pada saat paling awal
         phpunit.xml         file konfigurasi PHPUnit
         WebTestCase.php     kelas dasar untuk uji coba fungsional berbasis Web
~~~

Seperti yang tertampil diatas, kode uji coba kita akan diletakkan dalam 3 direktori: `fixtures`, `functional` dan `unit`. Direktori `report` akan digunakan untuk menyimpan laporan code-coverage yang dihasilkan.

Untuk menjalankan uji coba (baik unit maupun fungsional), kita dapat menjalankan perintah berikut ini di dalam konsol:

~~~
% cd testdrive/protected/tests
% phpunit functional/PostTest.php    // menjalankan uji coba individual
% phpunit --verbose functional       // menjalankan seluruh uji coba di `functional`
% phpunit --coverage-html ./report unit
~~~

Di atas, perintah paling terakhir akan menjalankan seluruh uji coba di direktori `unit` dan menghasilkan sebuah laporan code-coverage di dalam direktori `report`. Harap diperhatikan bahwa [xdebug extension](http://www.xdebug.org/) harus terinstalasi dan diaktifkan guna menghasilkan laporan code-coverage.


Uji Coba Skrip Bootstrap
---------------------------------------

Mari perhatikan apa yang terdapat dalam file `bootstrap.php`. File ini cukup spesial karena mirip dengan [skrip entri(entry script)](/doc/guide/basics.entry) dan merupakan titik awal ketika kita memulai rangkaian uji coba.

~~~
[php]
$yiit='path/to/yii/framework/yiit.php';
$config=dirname(__FILE__).'/../config/test.php';
require_once($yiit);
require_once(dirname(__FILE__).'/WebTestCase.php');
Yii::createWebApplication($config);
~~~

Di atas, kita pertama-tama menyertakan file `yiit.php` dari framework Yii, kemudian menginisialisasi beberapa konstan global dan menyertakan kelas dasar yang diperlukan. Kita kemudian membuat sebuah instance Web aplikasi dengan menggunakan konfigurasi file `test.php`. Jika cek `test.php`, kita akan menemukan bahwa file ini diturunkan dari file konfigurasi `main.php` dan menambah sebuah komponen aplikasi `fixture` yang kelasnya adalah [CDbFixtureManager]. Kita akan mempelajari fixtures lebih detail pada bab berikutnya.

~~~
[php]
return CMap::mergeArray(
	require(dirname(__FILE__).'/main.php'),
	array(
		'components'=>array(
			'fixture'=>array(
				'class'=>'system.test.CDbFixtureManager',
			),
			/* hilangkan komen di bawah untuk mendapatkan koneksi database uji coba
			'db'=>array(
				'connectionString'=>'DSN for test database',
			),
			*/
		),
	)
);
~~~

Ketika menjalankan uji coba yang berhubungan dengan database, kita harus menyediakan sebuah database uji coba sehingga apabila melakukan uji coba tidak akan mengganggu pengembangan normal atau kegiatan produksi. Untuk melakukannya, cukup hilangkan comment pada bagian konfigurasi `db` di bagian atas dan masukkan property `connectionString` dengan DSN (data source name) ke database uji coba.

Dengan sebuah skrip bootstrap ini, ketika kita menjalankan uji coba unit, kita akan memiliki sebuah instance aplikasi yang hampir sama dengan aplikasi yang umumnya, yakni untuk melayani Web request. Perbedaan utamanya adalah bootstrap memiliki manager fixture dan menggunakan database uji coba.


<div class="revision">$Id: test.overview.txt 2997 2011-02-23 13:51:40Z alexander.makarow $</div>
