Alias Path dan Namespace
========================

Yii menggunakan alias secara luas. Alias path dikaitkan dengan
direktori atau path file. Ditetapkan dalam sintaks dot (titik), mirip dengan
format namespace yang diadopsi secara luas:

~~~
RootAlias.path.ke.target
~~~

dengan `RootAlias` sebagai alias dari beberapa direktori yang sudah ada.

Dengan memanggil [YiiBase::setPathOfAlias()], sebuah alias dapat diterjemahkan ke path
yang bersangkutan. Misalnya, `system.web.CController` akan diterjemahkan sebagai
`yii/framework/web/CController`.

Kita juga dapat menggunakan [YiiBase::setPathOfAlias()] untuk mendefinisikan alias path root yang baru.


Alias Root
----------

Untuk kemudahan, Yii mendefinisikan beberapa alias root berikut : 

 - `system`: merujuk pada direktori Yii framework;
 - `zii` : merujuk pada direktori [pustaka Zii] (/doc/guide/extension.use#zii-extensions);
 - `application`: merujuk pada [basis direktori](/doc/guide/basics.application#application-base-directory) aplikasi;
 - `webroot`: merujuk pada direktori yang berisi file [skrip entri](/doc/guide/basics.entry).
 - `ext`: merujuk ke direktori yang berisi semua [extensions|extension](/doc/guide/extension.overview) pihak ketiga.

Sebagai tambahan, jika suatu aplikasi menggunakan [module](/doc/guide/basics.module), maka setiap module akan
memiliki root alias yang sudah di-define. Root alias ini memiliki nama yang sama dengan ID module dan merujuk ke base path module. Misalnya,
jika sebuah aplikasi menggunakan module yang memiliki ID `users`, maka sebuah root alias bernama `users` akan di-define.


Mengimpor Kelas
-----------------------

Menggunakan alias, sangat nyaman untuk mengimpor definisi sebuah kelas.
Sebagai contoh, jika kita ingin menyertakan definisi kelas [CController], kita dapat memanggil seperti berikut:

~~~
[php]
Yii::import('system.web.CController');
~~~

Metode [import|YiiBase::import] berbeda dengan `include` dan `require`
karena metode ini lebih efisien. Definisi kelas yang sedang diimpor
sebenarnya tidak disertakan (di-include) sampai ia dirujuk untuk pertama kalinya. Mengimpor
namespace yang sama berkali-kali juga lebih cepat daripada `include_once`
dan `require_once`.

> Tip: Ketika merujuk pada kelas yang didefinisikan oleh Yii framework, kita tidak
perlu mengimpor atau menyertakannya. Semua kelas inti Yii sudah di-import pada awalnya.


###Menggunakan Class Map

Dimulai dari versi 1.1.5, Yii memungkinkan kelas-kelas user di pra-impor melalui
sebuah mekanisme pemetaan kelas yang juga digunakan oleh kelas utama Yii. Kelas
pra-impor dapat digunakan di mana saja dalam sebuah aplikasi Yii tanpa perlu 
di-impor atau di-include-kan secara eksplisit. Fitur ini paling berguna untuk 
sebuah framework atau pustaka yang dibuat di atas Yii.

Untuk melakukan pre-impor sekumpulan kelas, kode berikut ini harus dijalankan terlebih dahulu
sebelum [CWebApplication::run()] dijalankan :

~~~
[php]
Yii::$classMap=array(
	'ClassName1' => 'path/to/ClassName1.php',
	'ClassName2' => 'path/to/ClassName2.php',
	......
);
~~~


Mengimpor Direktori
--------------------------

Kita juga dapat menggunakan sintaks berikut untuk mengimpor seluruh direktori agar
file kelas di bawah direktori tersebut secara otomatis disertakan saat
diperlukan.

~~~
[php]
Yii::import('system.web.*');
~~~

Selain [import|YiiBase::import], alias juga dipakai di banyak tempat
lain untuk merujuk pada kelas. Sebagai contoh, alias dapat dioper ke
[Yii::createComponent()] guna membuat instance kelas terkait,
meskipun file kelas tidak disertakan sebelumnya.


Namespace
--------------

Namespace merujuk pada pengelompokkan
logis beberapa nama kelas agar dapat dibedakan dari
nama kelas lainnya jika namanya sama. Jangan menyamakan antara alias path dengan namespace.
Sebuah alias path dipakai untuk merujuk pada file kelas atau direktori.
Alias path tidak ada hubungannya dengan namespace.

> Tip: Karena PHP sebelum versi 5.3.0 tidak mendukung namespace secara
langsung, Anda tidak dapat membuat instance dari dua kelas yang memiliki
nama yang sama dengan definisi yang berbeda. Untuk alasan ini, semua kelas Yii 
framework diawali dengan huruf 'C' (berarti 'class') agar bisa dibedakan dari
kelas yang didefinisikan pengguna. Direkomendasikan bahwa prefiks 'C' khusus dipakai hanya untuk pemakaian Yii framework saja,
dan kelas yang didefinisikan-pengguna diawali
dengan huruf lainnya.


Kelas Ber-Namespace
-----------------------------

Sebuah kelas ber-namespace merujuk pada sebuah kelas yang dideklarasikan di dalam sebuah namespace non-global.
Misalnya, kelas `application\components\GoogleMap` dideklarasikan di dalam namespace `application\components`.
Menggunakan kelas ber-namespace memerlukan PHP 5.3.0 atau ke atas.

Mulai dari versi 1.1.5, dimungkinkan untuk menggunakan kelas ber-namespace tanpa 
perlu meng-include-nya secara eksplisit. Misalnya, kita dapat membuat sebuah instance baru 
dari `application\components\GooleMap` tanpa perlu meng-include-kan file kelas koresponden 
secara eksplisit. Ini memungkinkan dengan meningkatkan mekanisme autoloading kelas.

Untuk bisa melakukan autoload sebuah kelas ber-namespace, namespace harus dinamakan
dengan cara yang mirip dengan alias path. Misalnya, sebuah kelas `application\components\GoogleMap`
harus disimpan ke dalam sebuah file yang dapat dialiaskan sebagai `application.components.GoogleMap`.


<div class="revision">$Id: basics.namespace.txt 3086 2011-03-15 00:04:53Z qiang.xue $</div>