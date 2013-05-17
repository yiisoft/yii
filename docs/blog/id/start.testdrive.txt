Melakukan Testdrive dengan Yii
====================

Dalam bagian ini, akan dijelaskan bagaimana membuat sebuah aplikasi kerangka yang berfungsi sebagai titik awal kita. Supaya sederhana, diasumsi bahwa root dokumen Web server kita adalah `/wwwroot` dan URL terkait adalah `http://www.example.com/`.


Melakukan Instalasi Yii
--------------

Pertama-tama kita melakukan instalasi framework Yii terlebih dahulu. Dapatkan sebuah copy file rilis Yii (versi 1.1.1 ke atas) dari [www.yiiframework.com](http://www.yiiframework.com/download) dan keluarkan filenya dari zip ke direktori `/wwwroot/yii`. Pastikan lagi bahwa terdapat sebuah direktori `/wwwroot/yii/framework`.

>Tip|Tips: Framework Yii dapat diinstalasi di mana saja dalam sistem file, tidak harus di folder Web. Direktori `framework`-nya mengandung seluruh kode framework dan hanya direktori framework yang diperlukan ketika melakukan pemasangan aplikasi Yii. Sebuah instalasi Yii tunggal dapat digunakan lebih dari satu aplikasi Yii.

Setelah selesai melakukan instalasi Yii, buka sebuah browser dan akses URL `http://www.example.com/yii/requirements/index.php`. Maka akan muncul sebuah requirement checker yang tersedia di Yii. Untuk aplikasi blog kita, selain kebutuhan minimal untuk Yii, kita juga perlu mengaktifkan ekstensi PHP `pdo` dan `pdo_sqlite` sehingga kita dapat mengakses database SQLite.


Membuat Aplikasi Kerangka
-----------------------------

Kita kemudian menggunakan tool bernama `yiic` untuk menciptakan aplikasi kerangka di dalam direktori `/wwwroot/blog`. `yiic` merupakan sebuat command line tool yang tersedia di dalam Yii. Tool ini dapat digunakan untuk menghasilkan kode untuk mengurangi pekerjaan menulis kode yang berulang-ulang.

Buka sebuah window command dan jalankan perintah berikut ini :

~~~
% /wwwroot/yii/framework/yiic webapp /wwwroot/blog
Create a Web application under '/wwwroot/blog'? [Yes|No]y
......
~~~

> Tip|Tips: Supaya dapat menggunakan `yiic` seperti tampilan di atas, program CLI PHP harus ada di command search path.Kalau tidak, ikuti langkah berikut ini :
>
>~~~
> path/to/php /wwwroot/yii/framework/yiic.php webapp /wwwroot/blog
>~~~

Untuk mencoba aplikasi yang baru saja dibuat kita, buka sebuah browser Web dan navigasi ke URL `http://www.example.com/blog/index.php`. Akan kelihatan aplikasi kerangka kita yang sudah memiliki empat halaman yang berfungsi penuh, homepage (halaman depan), halaman about (halaman tentang), halaman contact (halaman kontak), dan halaman login.

Berikut ini, kita akan menjelaskan secara singkat apa saja yang berada di dalam aplikasi kerangka.

###Skrip Entri

Kita memiliki sebuah [skrip entri](http://www.yiiframework.com/doc/guide/basics.entry) yakni file `/wwwroot/blog/indexphp` yang berisi :

~~~
[php]
<?php
$yii='/wwwroot/framework/yii.php';
$config=dirname(__FILE__).'/protected/config/main.php';

// remove the following line when in production mode
defined('YII_DEBUG') or define('YII_DEBUG',true);

require_once($yii);
Yii::createWebApplication($config)->run();
~~~

Ini merupakan skrip yang dapat diakses langsung oleh pengguna Web. Skrip file menyertakan file bootstrap Yii `yii.php`. Skrip entri membuat sebuah instance [aplikasi](http://www.yiiframework.com/doc/guide/basics.application) beserta konfigurasi tertentu dan menjalankannya.


###Direktori Aplikasi Dasar

Kita juga memilik sebuah [direktori dasar aplikasi](http://www.yiiframework.com/doc/guide/basics.application#application-base-directory) `/wwwroot/blog/protected`. Pada umumnya kode dan data kita akan diletakkan dalam direktori ini, dan harus terlindungi dari Web user yang ingin mengaksesnya. Untuk [Apache httpd Web server](http://httpd.apache.org/), kita meletakkan sebuah file `.htaccess` direktori dengan isinya:

~~~
deny from all
~~~

Untuk server Web yang lain, silahkan merujuk ke manual yang bersangkutan untuk mengetahui bagaimana melindungi direktori dari akses pengguna Web.


Alur Kerja Aplikasi
--------------------

Untuk membantu memahami cara kerja Yii, akan dijelaskan alur kerja utama aplikasi kerangka kita ketika seorang pengguna mengakses halaman contact:

 0. Pengguna me-request URL `http://www.example.com/blog/index.php?r=site/contact`;
 1. [Skrip entri](http://www.yiiframework.com/doc/guide/basics.entry) dieksekusi oleh server Web untuk memproses request;
 2. Sebuah instance [aplikasi](http://www.yiiframework.com/doc/guide/basics.application) dibuat dan dikonfigurasi dengan nilai properti awal yang ditentukan di dalam file konfigurasi `wwwroot/blog/protected/config/main.php`;
 3. Aplikasi tersebut menguraikan request ke sebuah [controller](http://www.yiiframework.com/doc/guide/basics.controller) dan sebuah [action controller](http://www.yiiframework.com/doc/guide/basics.controller#action). Untuk request halaman contact, diuraikan sebagai controller `site` dan action `contact` (metode `actionContact` di `/wwwroot/blog/protected/controllers/SiteController.php`);
 4. Aplikasi tersebut membuat controller `site` dalam istilah instance `SiteController` dan menjalankannya;
 5. Instance dari `SiteController` menjalankan action `contact` dengan memanggil metode `actionContact()`;
 6. Metode `actionContact` merender sebuah [view](http://www.yiiframework.com/doc/guide/basics.view) bernama `contact` kepada pengguna Web. Secara internal, ini dapat dicapai dengan menyertakan file view `/wwwroot/blog/protected/views/site/contact.php` dan menempelkan hasilnya ke dalam [layout](http://www.yiiframework.com/doc/guide/basics.view#layout) file `/wwwroot/blog/protected/views/layouts/column1.php`.


<div class="revision">$Id: start.testdrive.txt 1734 2010-01-21 18:41:17Z qiang.xue $</div>