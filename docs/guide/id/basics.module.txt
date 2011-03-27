Module
=====

Module adalah unit piranti lunak berdiri sendiri yang terdiri dari [model](/doc/guide/basics.model), [view(tampilan)](/doc/guide/basics.view), [controller](/doc/guide/basics.controller) dan komponen pendukung lainnya. Dalam banyak aspek, sebuah module mirip dengan [aplikasi](/doc/guide/basics.application). Perbedaan utamanya adalah bahwa module tidak bisa digunakan sendirian dan harus berada di dalam sebuah aplikasi. Pengguna dapat mengakses controller dalam sebuah module layaknya mengakses controller aplikasi biasa.

Module berguna dalam beberapa skenario. Untuk aplikasi berskala-besar, kita dapat membaginya ke dalam beberapa module, masing-masing dikembangkan dan di-maintain. secara terpisah. Beberapa fitur yang umum digunakan, seperti misalnya manajemen pengguna, manajemen komentar, dapat dikembangkan dalam bentuk module agar dapat dipakai kembali dengan mudah dalam proyek mendatang.


Membuat Module
-------------

Module diatur sebagai direktori yang namanya bertindak sebagai [ID|CWebModule::id] unik. Struktur direktori module mirip dengan [basis direktori aplikasi](/doc/guide/basics.application#application-base-directory). Contoh berikut memperlihatkan struktur umum direktori pada module bernama `forum`:

~~~
forum/
   ForumModule.php            file kelas module
   components/                berisi komponen yang bisa dipakai ulang
      views/                  berisi file tampilan untuk widgets
   controllers/               berisi file kelas controller
      DefaultController.php   file kelas controller standar
   extensions/                berisi extension pihak-ketiga
   models/                    berisi file kelas model
   views/                     berisi file tampilan controller dan tatat letak
      layouts/                berisi file tampilan tata letak
      default/                berisi file tampilan untuk DefaultController
         index.php            file tampilan indeks
~~~

Module harus memiliki kelas module yang diturunkan dari [CWebModule]. Nama kelas ditentukan menggunakan `ucfirst($id).'Module'`, dengan `$id` merujuk pada ID module (atau nama direktori module). Kelas module bertindak sebagai pusat tempat penyimpanan informasi berbagi diantara kode module. Sebagai contoh, kita dapat menggunakan [CWebModule::params] untuk menyimpan parameter module, dan menggunakan [CWebModule::components] untuk berbagi [komponen aplikasi](/doc/guide/basics.application#application-component) pada tingkat module.

> Tip: Kita dapat menggunakan generator module dalam Gii untuk membuat kerangka dasar module baru.



Menggunakan Module
-----------------

Untuk menggunakan module, pertama-tama tempatkan direktori module di bawah [basis direktori aplikasi](/doc/guide/basics.application#application-base-directory) `modules`. Kemudian deklarasikan ID module dalam properti [module|CWebApplication::modules] aplikasi. Sebagai contoh, agar bisa menggunakan module `forum` di atas, kita dapat menggunakan [konfigurasi aplikasi](/doc/guide/basics.application#application-configuration) berikut:

~~~
[php]
return array(
	......
	'modules'=>array('forum',...),
	......
);
~~~

Module juga bisa dikonfigurasi dengan nilai properti awal. Pemakaian ini mirip dengan mengkonfigurasi [komponen aplikasi](/doc/guide/basics.application#application-component). Sebagai contoh, module `forum` dapat memiliki properti bernama `postPerPage` dalam kelas modulnya yang bisa dikonfigurasi dalam [konfigurasi aplikasi](/doc/guide/basics.application#application-configuration) sebagai berikut:

~~~
[php]
return array(
	......
	'modules'=>array(
	    'forum'=>array(
	        'postPerPage'=>20,
	    ),
	),
	......
);
~~~

Instance module dapat diakses melalui properti [module|CController::module] pada controller yang aktif saat ini. Melalui instance module, selanjutnya kita dapat mengakses informasi yang dibagi pada tingkat module. Sebagai contoh, agar bisa mengakses informasi `postPerPage` di atas, kita dapat menggunakan ekspresi berikut:

~~~
[php]
$postPerPage=Yii::app()->controller->module->postPerPage;
// atau yang berikut jika $this merujuk pada turunan controller
// $postPerPage=$this->module->postPerPage;
~~~

Aksi controller dalam sebuah module dapat diakses menggunakan [rute](/doc/guide/basics.controller#route) `moduleID/controllerID/actionID`. Sebagai contoh, dengan mengganggap module `forum` di atas memiliki controller bernama `PostController`, kita dapat menggunakan [rute](/doc/guide/basics.controller#route) `forum/post/create` untuk merujuk pada aksi `create` dalam controller ini. URL terkait untuk rute ini adalah `http://www.example.com/index.php?r=forum/post/create`.

> Tip: Jika controller ada dalam sub-direktori `controllers`, kita masih dapat menggunakan format [rute](/doc/guide/basics.controller#route) di atas. Sebagai contoh, anggap `PostController` di bawah `forum/controllers/admin`, kita dapat merujuk pada aksi `create` menggunakan `forum/admin/post/create`.


Module Nested(Bersarang)
--------------

Module dapat bersarang(nested). Yaitu, sebuah module bisa berisi module lainnya. Kita menyebut module yang menampung sebagai *induk module* sementara yang ditampung dipanggil disebut *anak module*. Anak module harus ditempatkan di bawah direktori `modules` pada module induknya.

Untuk mengakses aksi controller dalam anak module, kita harus menggunakan rute `parentModuleID/childModuleID/controllerID/actionID`.

<div class="revision">$Id: basics.module.txt 2890 2011-01-18 15:58:34Z qiang.xue $</div>