Mendefinisi Fixture
===================

Otomatisasi Tes perlu dijalankan banyak kali. Untuk memastikan bahwa proses uji coba dapat diulang-ulangi, kita akan menjalankan uji coba ini dengan beberapa keadaan yang dikenali (known state) yang biasa juga disebut *fixture*. Contohnya, kita ingin menguji apakah fitur pembuatan post dalam aplikasi blog, setiap kali menjalankan uji coba, maka tabel bersangkutan yang menyimpan data tentang post (misalnya tabel `Post`, tabel `Comment`) harus dikembalikan ke beberapa keadaan yang telah pasti. [Dokumentasi PHPUnit](http://www.phpunit.de/manual/current/en/fixtures.html) telah menjelaskan bagaimana mengatur/membuat fixture yang umum. Di bagian ini, kita akan mempelajari bagaimana mengatur fixture database, seperti yang sudah dijelaskan pada contoh.

Membuat sebuah fixture database mungkin merupakan bagian yang paling memakan waktu dalam menguji Web aplikasi yang didukung database. Yii memperkenalkan komponen aplikasi [CDbFixtureManager] untuk meringankan masalah ini. Pada dasarnya kelas ini melakukan beberapa hal ketika menjalankan rangkaian uji coba:

 * Sebelum seluruh uji coba berjalan, reset semua tabel yang berhubungan kembali ke keadaan yang dikenali.
 * Sebelum sebuah metode dijalankan, kembalikan tabel yang ditentukan ke keadaan yang dikenali.
 * Selama menjalankan metode uji coba, [CDBFixtureManager] akan menyediakan akses ke barisan data yang berhubungan dengan fixture.

Untuk menggunakan [CDbFixtureManager], kita akan mengkonfigurasinya dalam [konfigurasi aplikasi](/doc/guide/basics.application#application-configuration) sebagai berikut,

~~~
[php]
return array(
	'components'=>array(
		'fixture'=>array(
			'class'=>'system.test.CDbFixtureManager',
		),
	),
);
~~~

Kemudian kita akan menyediakan data fixture di dalam direktori `protected/tests/fixtures`. Direktori ini harus dapat dikustomisasi menjadi yang berbeda dengan mengkonfigurasi properti [CDbFixtureManager::basePath] dalam konfigurasi aplikasi. Data Fixture disusun sebagai kumpulan file PHP yang disebut file fixture. Setiap file fixture akan mengembalikan sebuah larik(array) yang mewakili baris data awal pada tabel tertentu. Nama filenya sama dengan nama tabel. Berikut ini merupakan sebuah contoh data fixture untuk tabel `Post` yang disimpan di dalma file bernama `Post.php`:

~~~
[php]
<?php
return array(
	'sample1'=>array(
		'title'=>'test post 1',
		'content'=>'test post content 1',
		'createTime'=>1230952187,
		'authorId'=>1,
	),
	'sample2'=>array(
		'title'=>'test post 2',
		'content'=>'test post content 2',
		'createTime'=>1230952287,
		'authorId'=>1,
	),
);
~~~

Seperti yang dapat dilihat, file ini mengembalikan dua baris data. Setiap baris mewakili sebuah asosiasi array dengan key-nya sebagai nama kolom dan value sebagai nilai kolom bersangkutan. Sebagai tambahan, setiap baris diindeks dengan sebuah string (misalnya `sample1`, `sample2`) yang dikenal dengan sebuatan *row alias*. Nantinya pada saat kita menulis skrip uji coba, kita menunjukkan baris bersangkutan cukup dengan alias saja. Kita akan mempelajarinya lebih detail pada bagian berikutnya.

Anda mungkin menyadarinya bahwa kita tidak menentukan nilai kolom `id` di fixture. Alasannya adalah kolom `id` sudah ditetapkan untuk menjadi primary key yang bersifat auto-increment sehinggal nilainya akan terisi dengan sendirinya ketika kita mengisi baris baru.

Ketika dirujukkan pertama kali, [CDBFixtureManager] akan melewati seluruh file fixture dan menggunakannya untuk mereset tabel bersangkutan. [CDBFixtureManager] akan mengosongkan tabel, mengembalikan nilai sekuens pada tabel yang memiliki primary key yang auto-increment dan memasukkan baris data dari file fixture ke dalam tabel.

Terkadang, kita tidak ingin mereset semua tabel yang memiliki file fixture sebelum kita menjalankan rangkaian uji coba, karena me-reset terlalu banya file fixture akan memakan waktu yang lama. Caranya, kita dapat menulis sebuah skrip PHP yang melakukan pekerjaan inisialisasi dengan cara yang disesuaikan. Skrip ini dapat disimpan dengan nama `init.php` di dalam direktori yang berisi file fixture. Ketika mendeteksi adanya skrip ini, maka [CDBFixtureManager] akan menjalankan skrip ini alih-alih me-reset seluruh tabel.

Juga ada kemungkinan kita tidak suka dengan cara [CDbFixtureManager] dalam me-reset sebuah tabel, misalnya mengosongkannya lalu memasukkan data dengan fixture data. Kalau begitu, kita dapat menulis sebuah skrip inisialisasi untuk file fixture tertentu. Nama skrip haruslah berupa nama tabel yang berakhiran `.init.php`. Misalnya kita ingin menginisialisasi tabel `Post` maka ditulis `Post.init.php`. Ketika melihat skrip ini, [CDBFixtureManager] akan menjalankan skrip ini alih-alih menggunakan cara biasa untuk me-reset tabel.

> Tip: Memiliki terlalu banyak file fixture akan meningkatkan waktu uji coba secara dramatis. Oleh karenanya, Anda diharapkan hanya menyediakan file fixture yang tabel-tabelnya mungkin berubah ketika terjadi uji coba. Tabel yang berfungsi sebagai sumber data(look-ups) dan tidak akan berubah, tidak perlu file fixture.

Di dua bab berikutnya, kita akan memahami bagaimana menggunakan fixture yang diatur oleh [CDbFixtureManager] dalam uji coba unit dan fungsional.

<div class="revision">$Id: test.fixture.txt 3039 2011-03-09 19:48:15Z qiang.xue $</div>