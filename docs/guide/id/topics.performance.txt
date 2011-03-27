Penyesuaian Performa
=======================

Performa aplikasi Web dipengaruhi oleh banyak faktor. Akses database,
operasi sistem file, bandwidth jaringan adalah faktor pengaruh yang potensial.
Yii sudah mencoba di setiap aspek guna mengurangi pengurangan performa
yang disebabkan oleh framework. Akan tetapi, masih ada banyak tempat dalam
aplikasi pengguna yang bisa ditingkatkan untuk mempercepat performa.

Menghidupkan Extension APC
-------------------------

Menghidupkan [extension
PHP APC](http://www.php.net/manual/en/book.apc.php) barangkali merupakan
cara termudah untuk meningkatkan performa aplikasi secara keseluruhan.
extension men-cache dan mengoptimasi kode menengah PHP dan menghindari waktu
yang dipakai dalam penguraian skrip PHP untuk setiap permintaan yang masuk.

Mematikan Mode Debug
--------------------

Mematikan mode debug adalah cara mudah lain guna meningkatkan performa.
Aplikasi Yii yang berjalan dalam mode debug jika konstan `YII_DEBUG` didefinisikan sebagai
true. Mode debug berguna selama tahap pengembangan, tapi akan mempengaruhi
performa karena beberapa komponen menimbulkan beban ekstra dalam debug mode.
Sebagai contoh, pencatat pesan bisa merekam informasi debug tambahan untuk
setiap pesan yang sedang dicatat.

Menggunakan `yiilite.php`
-------------------------

Saat [extension PHP APC](http://www.php.net/manual/en/book.apc.php) dihidupkan,
kita dapat mengganti `yii.php` dengan file bootstrap Yii yang berbeda bernama
`yiilite.php` untuk lebih meningkatkan performa aplikasi berbasis-Yii.

File `yiilite.php` ada di setiap rilis Yii. Ini adalah hasil penggabungan
beberapa file kelas Yii yang umumnya dipakai. Baik komentar maupun pernyataan
trace dibuang dari file gabungan. Oleh karena itu, menggunakan
`yiilite.php` akan mengurangi jumlah file yang disertakan dan menghindari
eksekusi statement trace.

Harap dicatat, pemakaian `yiilite.php` tanpa APC sebenarnya dapat mengurangi performa,
karena `yiilite.php` berisi beberapa kelas yang tidak dipakai
dalam setiap permintaan dan akan memerlukan waktu penguraian tambahan. Juga sudah diobservasi bahwa
pemakaian `yiilite.php` lebih lambat pada beberapa konfigurasi server, bahkan saat
APC dihidupkan. Cara terbaik untuk menilai apakah menggunakan `yiilite.php` atau tidak
adalah dengan menjalankan benchmark menggunakan demo `hello world` yang disertakan.

Menggunakan Teknik Cache
------------------------

Seperti dijelaskan dalam seksi [Caching](/doc/guide/caching.overview), Yii
menyediakan beberapa solusi caching yang bisa meningkatkan performa aplikasi
Web secara signifikan. Jika pembuatan beberapa data memerlukan waktu lama,
kita dapat menggunakan pendekatan [cache data](/doc/guide/caching.data) untuk
mengurangi frekuensi pembuatan data; Jika bagian halaman relatif tetap statis,
kita bisa menggunakan pendekatan [cache
fragmen](/doc/guide/caching.fragment) untuk mengurangi frekuensi render;
Jika seluruh halaman relatif statis, kita dapat menggunakan pendekatan [cache
halaman](/doc/guide/caching.page) untuk menghemat waktu render seluruh
halaman.

Jika aplikasi menggunakan [Active Record](/doc/guide/database.ar), kita harus
menghidupkan cache skema untuk menghemat waktu penguraian skema database.
Ini bisa dikerjakan dengan mengkonfigurasi properti
[CDbConnection::schemaCachingDuration] ke nilai lebih besar dari 0.

Selain teknik cache tingkat aplikasi ini, kita juga bisa menggunakan solusi
cache tingkat server untuk meingkatkan performa aplikasi. Sebenarnya,
[Cache APC](/doc/guide/topics.performance#enabling-apc-extension) yang kita
jelaskan sebelumnya masuk ke kategori ini. Ada teknik server lain, seperti
[Zend Optimizer](http://Zend.com/ZendOptimizer),
[eAccelerator](http://eaccelerator.net/),
[Squid](http://www.squid-cache.org/), dan banyak lagi.

Optimasi Database
-----------------

Pengambilan data dari database sering menjadi hambatan utama performa dalam
aplikasi Web. Meskipun menggunakan caching tetap dapat mengurangi performa,
ini tidak sepenuhnya memecahkan masalah. Ketika database berisi data yang besar
dan data yang di-cache tidak benar, pengambilan besar data bisa sangat lambat
tanpa desain database dan query yang benar.

Desainlah indeks dengan benar dalam database. Mengindeks bisa menjadikan query `SELECT` jauh
lebih cepat, tapi dapat memperlambat query `INSERT`, `UPDATE` atau `DELETE`.

Untuk query yang kompleks, direkomendasikan membuat view database daripada
menggunakan query di dalam kode PHP dan meminta DBMS untuk menguraikannya
berulang kali.

Jangan berlebihan menggunakan [Active Record](/doc/guide/database.ar). Meskipun [Active
Record](/doc/guide/database.ar) baik pada pemodelan data dalam gaya OOP,
sebenarnya menurunkan performa karena harus membuat satu atau beberapa
objek untuk mewakili setiap baris dari hasil query. Untuk aplikasi intensif
data, penggunaan [DAO](/doc/guide/database.dao) atau
API database di tingkat lebih rendah bisa menjadi pilihan yang lebih baik.

Terakhir, gunakan `LIMIT` dalam query `SELECT` Anda. Ini akan menghindari
pengambilan data berlebihan dari database dan menghabiskan alokasi memori
untuk PHP.

Memperkecil File Skrip
-----------------------

Halaman yang kompleks sering harus menyertakan banyak file eksternal JavaScript dan CSS. Karena setiap file akan menyebabkan lalu lintas tambahan ke server dan sebaliknya, kita harus memperkecil jumlah file skrip dengan menggabungnya ke dalam file agar lebih sedikit jumlahnya. Kita juga harus mempertimbangkan pengurangan ukuran setiap file skrip guna mengurangi waktu transmisi jaringan. Ada banyak piranti untuk membantu dua aspek ini.

Untuk halaman yang dibuat oleh Yii, kenyataannya bahwa beberapa file skrip dirender oleh komponen yang tidak ingin kita ubah (misalnya komponen inti Yii, komponen pihak ketiga). Untuk memperkecil file skrip ini, kita memerlukan dua langkah.

Pertama, kita mendeklarasikan skrip yang diperkecil dengan mengkonfigurasi properti [scriptMap|CClientScript::scriptMap] pada komponen aplikasi [clientScript|CWebApplication::clientScript]. Ini bisa dikerjakan baik dalam konfigurasi aplikasi ataupun dalam kode. Sebagai contoh,

~~~
[php]
$cs=Yii::app()->clientScript;
$cs->scriptMap=array(
	'jquery.js'=>'/js/all.js',
	'jquery.ajaxqueue.js'=>'/js/all.js',
	'jquery.metadata.js'=>'/js/all.js',
	......
);
~~~

Apa yang dilakukan kode di atas adalah memetakan file-file JavaScript ke URL `/js/all.js`. Jika ada file JavaScript harus disertakan oleh beberapa komponen, Yii akan menyertakan URL (sekali) alih-alih file skrip secara individual.

Kedua, kita perlu menggunakan beberapa piranti untuk menggabung (dan mungkin memadatkan) file JavaScript ke dalam satu file dan menyimpannya sebagai `js/all.js`.

Trik yang sama juga berlaku untuk file CSS.

Kita juga dapat meningkatkan kecepatan pengambilan halaman dengan bantuan [Google AJAX Libraries API](http://code.google.com/apis/ajaxlibs/). Sebagai contoh, kita dapata menyertakan `jquery.js` dari server Google daripada server kita sendiri. Untuk melakukannya, pertama kita mengkonfigurasi `scriptMap` sebagai berikut,

~~~
[php]
$cs=Yii::app()->clientScript;
$cs->scriptMap=array(
	'jquery.js'=>false,
	'jquery.ajaxqueue.js'=>false,
	'jquery.metadata.js'=>false,
	......
);
~~~

Dengan memetakan file skrip ini menjadi false, kita melarang Yii untuk membuat kode penyertaan file-file ini. Sebaliknya, kita menulis kode berikut dalam halaman kita u8ntuk secara eksplisit menyertakan file skrip dari Google,

~~~
[php]
<head>
<?php echo CGoogleApi::init(); ?>

<?php echo CHtml::script(
	CGoogleApi::load('jquery','1.3.2') . "\n" .
	CGoogleApi::load('jquery.ajaxqueue.js') . "\n" .
	CGoogleApi::load('jquery.metadata.js')
); ?>
......
</head>
~~~

<div class="revision">$Id: topics.performance.txt 2890 2011-01-18 15:58:34Z qiang.xue $</div>