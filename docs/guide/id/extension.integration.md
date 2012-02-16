Menggunakan Pustaka Pihak Ketiga
================================

Yii didesain secara hati-hati agar pustaka pihak-ketiga(third-party library) dapat dengan mudah
diintegrasikan untuk lebih memperluas fungsionalitas Yii.
Ketika menggunakan pustaka pihak ketiga dalam sebuah projek, para pengembang
sering menghadapi masalah mengenai penyertaan penamaan kelas dan file.
Karena semua kelas Yii diawali dengan huruf `C`, maka masalah penamaan
kelas akan jarang terjadi; dan karena Yii tergantung pada
[SPL autoload](http://us3.php.net/manual/en/function.spl-autoload.php)
untuk melakukan penyertaan file kelas, Yii akan sejalan dengan pustaka lain
jika mereka menggunakan fitur autoloading yang sama atau PHP include path untuk
menyertakan file kelas.


Di bawah ini kami menggunakan sebuah contoh guna menggambarkan bagaimana memakai
komponen [Zend_Search_Lucene](http://www.zendframework.com/manual/en/zend.search.lucene.html)
dari [Zend framework](http://www.zendframework.com) dalam aplikasi Yii.

Pertama, kita mengurai file rilis Zend framework ke sebuah direktori di
`protected/vendors`, menganggap bahwa direktori `protected` adalah
[direktori basis aplikasi](/doc/guide/basics.application#application-base-directory).
Pastikan bahwa file `protected/vendors/Zend/Search/Lucene.php` ada di sana.

Kedua, pada awal file kelas controller, sisipkan baris berikut:

~~~
[php]
Yii::import('application.vendors.*');
require_once('Zend/Search/Lucene.php');
~~~

Kode di atas menyertakan file kelas `Lucene.php`. Karena kita menggunakan
path relatif, kita perlu mengubah path include PHP agar file bisa ditempatkan
dengan benar. Ini dilakukan dengan memanggil `Yii::import` sebelum `require_once`.

Setelah kode di atas siap, kita dapat menggunakan kelas `Lucene` dalam aksi controller,
seperti berikut:

~~~
[php]
$lucene=new Zend_Search_Lucene($pathOfIndex);
$hits=$lucene->find(strtolower($keyword));
~~~

Menggunakan Pustaka Pihak Ketiga yang Ber-namespace
------------------------------------

Untuk menggunakan pustaka (library) yang memiliki namespace sesuai standar
[PSR-0](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md)
(seperti Zend Framework 2 or Symfony2), Anda harus mendaftarkan root-nya sebagai path alias.

Sebagai contoh kita akan menggunakan [Imagine](https://github.com/avalanche123/Imagine).
Jika kita meletakkan direktori `Imagine` di dalam `protected/vendors` maka kita akan 
mampu menggunakannya seperti berikut ini:

~~~
[php]
Yii::setPathOfAlias('Imagine',Yii::getPathOfAlias('application.vendors.Imagine'));

// Kemudian mulai menulis code sesuai panduan Imagine:
// $imagine = new Imagine\Gd\Imagine();
// dan lain-lain.
~~~

Pada code di atas, nama dari alias yang sudah kita definisikan harus cocok 
dengan namespace pertama yang digunakan di dalam pustaka.

Menggunakan Yii di Sistem Pihak Ketiga
------------------------------

Yii juga bisa dijadikan sebagai pustaka tersendiri untuk mendukung pengembangan dan menyokong
sistem pihak ketiga, seperti Wordpress, Joomla dan lain-lain. Untuk melakukannya, sertakan
kode berikut di bootsrap code dari sistem pihak ketiga:

~~~
[php]
require_once('path/to/yii.php');
Yii::createWebApplication('path/to/config.php');
~~~

Kode di atas sangat mirip dengan bootstrap code yang digunakan aplikasi Yii
pada umumnya kecuali satu hal, dia tidak memanggil method `run()` setelah membuat
instance aplikasi web.

Sekarang kita dapat menggunakan fitur yang ditawari Yii ketika mengembangkan aplikasi pihak ketiga. Misalnya,
kita dapat menggunakan `Yii::app()` untuk mengakses instance aplikasi; kita dapat menggunakan fitur database
seperti DAO dan ActiveRecord; kita dapat menggunakan model dan fitur validasi dan lain-lain.


<div class="revision">$Id: extension.integration.txt 3431 2011-11-03 00:53:44Z alexander.makarow@gmail.com $</div>