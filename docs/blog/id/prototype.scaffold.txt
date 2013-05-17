Scaffolding
===========

Membuat, membaca, update dan menghapus atau dalam bahasa Inggris create, read, update, delete yang disingkat CRUD) merupakan empat operasi dasar pada objek data dalam aplikasi. Karena tugas mengimplementasi CRUD sangat umum ketika mengembangkan aplikasi Web, Yii menyediakan beberapa tool yang membantu generate kode bernama *Gii* yang dapat mengotomatisasi proses ini (bisa dikenal sebagai *scaffolding*)

> Note|Catatan: Gii sudah tersedia semenjak versi 1.1.2. Sebelum itu, anda harus menggunakan [yiic shell tool](http://www.yiiframework.com/doc/guide/quickstart.first-app-yiic) untuk melakukan hal yang sama.

Berikut ini, kita akan melihat bagaimana menggunakan tool ini untuk mengimplementasikan operasi CRUD untuk post dan komentar di dalam aplikasi blog kita.

Instalasi Gii
--------------

Pertama-tama kita harus menginstal Gii. Buka file `/wwwroot/blog/protected/config/main.php` dan ikut aturan berikut:

~~~
[php]
return array(
	......
	'import'=>array(
		'application.models.*',
		'application.components.*',
	),

	'modules'=>array(
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>'pick up a password here',
		),
	),
);
~~~

Kode di atas menginstalasi sebuah module bernama `gii`, yang memungkinkan kita mengakses module Gii  lewat browser dengan membuka URL berikut ini:

~~~
http://www.example.com/blog/index.php?r=gii
~~~

Kita akan diminta untuk memasukkan sebuah password. Masukkan password yang sudah diset di `/wwwroot/blog/protected/config/main.php` tadi, dan seharusnya kita akan melihat sebuah halaman yang menampilkan seluruh tool generate kode.

> Note|Catatan: Kode di atas harus dibuang ketika terpasang di mesin produksi. Tool generate kode ini hanya digunakan di mesin pengembangan.


Membuat Model
---------------

Pertama-tama kita harus membuat sebuah kelas [model](http://www.yiiframework.com/doc/guide/basics.model) untuk setiap tabel database kita. Kelas-kelas model akan memungkinkan kita untuk mengakses database dengan gaya objek-oriented yang intuitif, seperti yang akan kita lihat nantinya di dalam tutorial ini.

Klik pada link `Model Generator` untuk memulai menggunakan tool generate model.

Pada halaman `Model Generator`, masukkan `tbl_user` (nama tabel user) di dalam field `Table Name`, dan kemudian tekan tombol `Preview`. Sebuah tabel preview akan muncul. Kita dapat mengklik link ini di dalam tabel untuk melihat kode yang akan dihasilkan. Jika semuanya sudah tidak bermasalah, kita dapat menekan tombol `Generate` untuk menghasilkan kode dan menyimpannya ke dalam file.

> Info: Karena kode generator perlu save kode yang di-generate ke dalam file, diharuskan proses Web memiliki hak untuk membuat dan memodifikasi file bersangkutan. Untuk sederhananya, kita dapat memberikan hak menulis kepada proses Web terhadap seluruh direktori `/wwwroot/blog`. Perhatikan bahwa langkah ini dilakukan hanya pada tahap pengembangan ketika menggunakan `Gii`.

Ulangi langkah yang sama untuk sisa tabel database, termasuk `tbl_post`, `tbl_comment`, `tbl_tag` dan `tbl_lookup`.

> Tip|Tips: Kita dapat memasukkan sebuah karakter asteriks `\*` ke dalam field `Table Name`. Dengan demikian akan menghasilkan sebuah kelas model untuk *setiap* tabel database dengan sekali jalan.

Sampai dengan tahap sini, kita telah memiliki beberapa file yang baru dibuat:

 * `models/User.php` berisi kelas `User` yang merupakan turunan dari [CActiveRecord] dan akan digunakan untuk mengakses tabel database `tbl_user`;
 * `models/Post.php` berisi kelas `Post` yang merupakan turunan dari [CActiveRecord] dan dapat digunakan untuk mengakses tabel database `tbl_post`;
 * `models/Tag.php` berisi kelas `Tag` yang merupakan turunan dari [CActiveRecord] dan akan digunakan untuk mengakses tabel database `tbl_tag`;
 * `models/Comment.php` berisi kelas `Comment` yang merupakan turunan dari [CActiveRecord] dan dapat digunakan untuk mengakses tabel database `tbl_comment`;
 * `models/Lookup.php` berisi kelas `Lookup` yang merupakan turunan dari [CActiveRecord] dan akan digunakan untuk mengakses tabel database `tbl_lookup`.


Implementasi Operasi CRUD
----------------------------

Setelah kelas-kelas model tadi dibuat, kita dapat menggunakan `Crud Generator` untuk membuat kode implementasi operasi CRUD terhadap model-model tersebut. Kita akan melakukan untuk model `Post` dan `Comment`.

Pada halaman `Crud Generator`, masukkan `Post` (nama kelas model post yang baru saja dibuat) di dalam field `Model Class`, dan menekan tombol `Preview`. Kita akan melihat lebih banyak file yang di-generate. Tekan tombol `Generate` untuk generate mereka.

Ulangi langkah yang sama untuk model `Comment`.

Mari lihat file-file yang dihasilkan oleh generator CRUD. Semua file yang dihasilkan berada di dalam `/wwwroot/blog/protected`. Untuk kemudahan, kita mengelompokkan mereka ke dalam file [controller](http://www.yiiframework.com/doc/guide/basics.controller) dan [view](http://www.yiiframework.com/doc/guide/basics.view):

 - file controller:
         * `controllers/PostController.php` berisi kelas `PostController` yang bertanggung jawab atas seluruh operasi CRUD pada post;
	 * `controllers/CommentController.php` berisi kelas `CommentController` yang bertanggung jawab atas seluruh operasi CRUD pada komentar;

 - file view::
	 * `views/post/create.php` adalah file view yang menampilkan sebuah form HTML untuk membuat post baru;
	 * `views/post/update.php` adalah file view yang menampilkan sebuah form HTML untuk mengupdate post yang sudah ada;
	 * `views/post/view.php` adalah file view yang menampilkan informasi lengkap pada sebuah post;
	 * `views/post/index.php` adalah file view yang menampilkan sebuah daftar post;
	 * `views/post/admin.php` adalah file view yang menampilkan sebuah tabel dengan perintah administrasi;
	 * `views/post/_form.php` adalah file view parsial yang di-embed ke dalam `views/post/create.php` dan `views/post/update.php`. File ini menampilkan form HTML untuk mengumpulkan informasi.
	 * `views/post/_view.php` adalah file view parsial yang digunakan oleh `views/post/index.php`. Ditampilkan sekilas isi sebuah post.
	 * `views/post/_search.php` adalah file view parsial yang digunakan oleh `views/post/admin.php`. File ini menampilkan sebuah form pencarian.
	 * sekelompok file yang memiliki file view yang mirip juga di-generate untuk comment.


Testing
-------

Kita dapat mencoba fitur yang diimplementasi oleh kode yang di-generate kita tadi dengan mengakses URL berikut:

~~~
http://www.example.com/blog/index.php?r=post
http://www.example.com/blog/index.php?r=comment
~~~

Perhatikan bahwa kode yang di-generate mengimplementasikan fitur post dan komentar terpisah satu sama lain. Perhatikan juga, ketika membuat post baru, kita diharuskan memasukkan informasi seperti `author_id` dan `create_time`, yang seharusnya pada aplikasi nyata ditentukan oleh program. Jangan khawatir. Kita akan memperbaiki masalah-masalah ini pada tahap selanjutnya. Untuk sekarang, kita sudah bisa cukup puas dengan prototype yang sudah tersedia untuk mengimplementasi aplikasi blog.


Guna memahami lebih baik bagaimana file-file di atas digunakan, kita tampilkan alur kerja yang terjadi pada aplikasi blog ketika menampilkan sebuah daftar post:

 0. User me-request URL `http://www.example.com/blog/index.php?r=post`;
 1. Server Web menjalankan [entry script](http://www.yiiframework.com/doc/guide/basics.entry) , yang  membuat dan menginisialisasi sebuah instance [aplikasi](http://www.yiiframework.com/doc/guide/basics.application) untuk menangani request tersebut;
 2. Aplikasi membuat sebuah instance `PostController` dan mejalankannya;
 3. Instance dari `PostController` menjalankan action `index` dengan memanggil method `actionIndex()`. Harap diperhatikan bawah `index` merupakan action default jika user tidak menentukan action apa yang dijalankan di URL;
 4. Method `actionIndex()` meng-query database untuk mendapatkan daftar post terbaru;
 5. Method `actionIndex()` me-render view `index` dengan data post.


<div class="revision">$Id: prototype.scaffold.txt 2258 2010-07-12 14:13:50Z alexander.makarow $</div>