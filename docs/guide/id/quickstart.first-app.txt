Membuat Aplikasi Pertama Yii Anda
============================

Sebagai pengalaman pertama dengan Yii, di bagian ini kami akan menjelaskan bagaimana
membuat aplikasi Yii. Kita akan menggunakan `yiic` (tool command line)
untuk membuat aplikasi Yii baru dan `Gii` (sebuah code generator berbasis web)
untuk mengautomatisasi pembuatan code untuk tugas-tugas tertentu. Untuk kenyamanan,
kita akan berasumsi `YiiRoot` sebagai direktori di mana Yii diinstalasi, dan `WebRoot` 
adalah document root dari Web Server kita.

Jalankan `yiic` pada baris perintah seperti berikut:

~~~
% YiiRoot/framework/yiic webapp WebRoot/testdrive
~~~

> Note|Catatan: Saat menjalankan `yiic` pada Mac OS, Linux atau Unix, Anda harus mengubah
> perijinan file `yiic` agar bisa dijalankan.
> Alternatif lain, Anda bisa menjalankan piranti seperti berikut,
>
> ~~~
> % cd WebRoot/testdrive
> % php YiiRoot/framework/yiic.php webapp WebRoot/testdrive
> ~~~

Ini akan membuat kerangka aplikasi Yii di bawah direktori
`WebRoot/testdrive`. Aplikasi memiliki struktur direktori yang
diperlukan oleh umumnya aplikasi Yii.

Tanpa harus menulis satu baris kode pun, kita dapat menguji aplikasi pertama
Yii kita dengan mengakses URL berikut dalam Web browser:

~~~
http://hostname/testdrive/index.php
~~~

Seperti yang kita lihat, aplikasi memiliki empat halaman: halaman beranda, halaman tentang (about)
halaman kontak dan halaman masuk(login). Halaman kontak menampilkan sebuah form kontak
yang dapat diisi pengguna  dan mengirim pertanyaan mereka ke webmaster, sedangkan
halaman masuk memungkinkan pengunjung diotentikasi 
sebelum mengakses isi khusus bagi yang sudah login.

![Halaman beranda](first-app1.png)

![Halaman kontak](first-app2.png)

![Halaman dengan input error](first-app3.png)

![Halaman kontak dengan pesan sukses](first-app4.png)

![Halaman masuk](first-app5.png)


Diagram berikut memperlihatkan struktur direktori pada aplikasi kita
Silahkan lihat [Konvensi](/doc/guide/basics.convention#directory) utuk keterangan
lebih rinci mengenai struktur ini.

~~~
testdrive/
   index.php                 Web application entry script file
   index-test.php            entry script file for the functional tests
   assets/                   containing published resource files
   css/                      containing CSS files
   images/                   containing image files
   themes/                   containing application themes
   protected/                containing protected application files
      yiic                   yiic command line script for Unix/Linux
      yiic.bat               yiic command line script for Windows
      yiic.php               yiic command line PHP script
      commands/              containing customized 'yiic' commands
         shell/              containing customized 'yiic shell' commands
      components/            containing reusable user components
         Controller.php      the base class for all controller classes
         UserIdentity.php    the 'UserIdentity' class used for authentication
      config/                containing configuration files
         console.php         the console application configuration
         main.php            the Web application configuration
         test.php            the configuration for the functional tests
      controllers/           containing controller class files
         SiteController.php  the default controller class
      data/                  containing the sample database
         schema.mysql.sql    the DB schema for the sample MySQL database
         schema.sqlite.sql   the DB schema for the sample SQLite database
         testdrive.db        the sample SQLite database file
      extensions/            containing third-party extensions
      messages/              containing translated messages
      models/                containing model class files
         LoginForm.php       the form model for 'login' action
         ContactForm.php     the form model for 'contact' action
      runtime/               containing temporarily generated files
      tests/                 containing test scripts
      views/                 containing controller view and layout files
         layouts/            containing layout view files
            main.php         the base layout shared by all pages
            column1.php      the layout for pages using a single column
            column2.php      the layout for pages using two columns
         site/               containing view files for the 'site' controller
            pages/           containing "static" pages
               about.php     the view for the "about" page
            contact.php      the view for 'contact' action
            error.php        the view for 'error' action (displaying external errors)
            index.php        the view for 'index' action
            login.php        the view for 'login' action
~~~

Sambungan ke Database
---------------------

Umumnya aplikasi Web didukung oleh database. Aplikasi testdrive (pengujian) kita tidak
terkecuali. Untuk menggunakan database, pertama kita perlu memberitahu
aplikasi bagaimana untuk berhubungan dengannya. Ini dilakukan dengan mengubah file
konfigurasi aplikasi `WebRoot/testdrive/protected/config/main.php`, seperti terlihat di bawah ini:

~~~
[php]
return array(
	......
	'components'=>array(
		......
		'db'=>array(
			'connectionString'=>'sqlite:protected/data/source.db',
		),
	),
	......
);
~~~

Kode di atas akan menginstruksi Yii bahwa aplikasi harus terkoneksi dengan database SQLite 
`WebRoot/testdrive/protected/data/testdrive.db` ketika diperlukan. Perhatikan bahwa database
SQLite sudah di dalam kerangka aplikasi yang baru saja kita generate. Database mengandung
sebuah tabel bernama `tbl_user`:

~~~
[sql]
CREATE TABLE tbl_user (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	username VARCHAR(128) NOT NULL,
	password VARCHAR(128) NOT NULL,
	email VARCHAR(128) NOT NULL
);
~~~

Jika ingin mencoba database MySQL, Anda harus menyertakan file skema MySQL
 `WebRoot/testdrive/protected/data/schema.mysql.sql` untuk membuat database.

> Note|Catatan: Untuk menggunakan fitur database, kita harus mengaktifkan extension
PDO dan extension PDO driver-spesifik. Untuk aplikasi testdrive, kita perlu
mengaktifkan extension `php_pdo` dan `php_pdo_sqlite`


Mengimplementasikan Operasi CRUD
--------------------------------

Sekarang tiba saat yang menarik. Kita akan mengimplementasi operasi
CRUD (create, read, update dan delete) untuk tabel `tbl_user` yang baru saja kita buat. 
Biasanya hal seperti ini sering kita lakukan dalam aplikasi umumnya. Alih-alih harus
merepotkan diri menulis code, kita akan menggunakan `Gii` -- sebuah generator berbasis web.

> Info: Gii sudah tersedia semenjak versi 1.1.2. Sebelumnya, kita menggunakan `yiic` (yang baru saja disinggung) untuk melakukan hal yang sama. Untuk lebih detail silahkan merujuk ke [Mengimplementasi Operasi CRUD dengan yiic shell](/doc/guide/quickstart.first-app-yiic).


### Konfigurasi  Gii

Untuk menggunakan Gii, kita pertama harus mengubah file `WebRoot/testdrive/protected/config/main.php`, yang juga dikenal sebagai file [konfigurasi aplikasi](/doc/guide/basics.application#application-configuration):

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
			'password'=>'pilih password di sini',
		),
	),
);
~~~

Kemudian buka URL `http://hostname/testdrive/index.php?r=gii`. Kita akan diminta password, yang baru saja dimasukkan di konfigurasi aplikasi di atas.

### Menghasilkan User Model

Setelah login, klik ke link `Model Generator`. Menu ini akan membawa kita ke halaman penghasil model,

![Model Generator](gii-model.png)

Dalam field `Table Name`, masukkan `tbl_user`. Di dalam field `Model Class`, masukkan `User`. Kemudian klik tombol `Preview` yang akan memunculkan file kode baru yang akan dihasilkan. Klik tombol `Generate`. Sebuah nama file bernama `User.php` akan dihasilkan di `protected/models`. Kelas model `User` ini akan memungkinkan kita untuk berkomunikasi dengan tabel `tbl_user`  dalam gaya berorientasi objek, yang nanti akan dibahas lebih lanjut.

### Menghasilkan CRUD Code

Setelah membuat file kelas model, kita akan menghasilkan kode untuk mengimplementasi operasi CRUD tentang data user. Kita akan memilih `Crud Generator` di Gii, yang ditampilkan sebagai berikut,

![CRUD Generator](gii-crud.png)

Dalam field `Model Class`, masukkan `User`. Di dalam field `Controller ID`, masukkan `user` (dalam huruf kecil). Sekarang tekan tombol `Preview` diikuti tombol `Generate`. Kita telah menyelesaikan proses penghasilan kode CRUD.

### Mengakses halaman CRUD

Mari kita nikmati pekerjaan kita dengan melihatnya di URL berikut:

~~~
http://hostname/testdrive/index.php?r=user
~~~

Ini akan menampilkan sebuah daftar entri pengguna dalam tabel `User`. 

Klik tombol `Create User`. Kita akan dibawa ke halaman login
jika kita belum login sebelumnya. Setelah masuk, kita melihat form
input yang mengijinkan kita untuk menambah entri pengguna baru. Lengkapi form dan
klik tombol `Create`. Lengkapi form dan klik tombol `Create`. Jika terjadi kesalahan input,
sebuah tampilan error yang bagus akan muncul dan mencegah kita menyimpan inputan kita.
Kembali ke halaman daftar user, kita seharusnya melihat sebuah user baru sudah muncul di daftar.

Ulangi langkah di atas untuk menambah lebih banyak pengguna. Harap diingat bahwa halaman daftar pengguna
akan dipaginasi secara otomatis jika terlalu banyak pengguna yang harus ditampilkan
pada satu halaman.

Jika kita login sebagai administrator menggunakan `admin/admin`, kita dapat melihat halaman
pengguna admin dengan URL berikut:

~~~
http://hostname/testdrive/index.php?r=user/admin
~~~

Ini akan memperlihatkan tabel entri user yang bagus. Kita dapat mengklik sel header
tabel guna mengurut sesuai kolom terkait. Kita dapat mengklik tombol pada setiap baris
untuk melihat, meng-update atau menghapus baris data bersangkutan.
Kita dapat membuka halaman-halaman berbeda. Kita juga dapat memfilter dan mencari
data yang diinginkan.

Semua fitur bagus ini disediakan tanpa harus menulis satu baris 
kode pun!

![Halaman admin pengguna](first-app6.png)

![Halaman membuat pengguna baru](first-app7.png)



<div class="revision">$Id: quickstart.first-app.txt 3219 2011-05-13 03:03:35Z qiang.xue $</div>
