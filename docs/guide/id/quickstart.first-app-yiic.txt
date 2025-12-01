Menghasilkan Kode dengan Tool Command Line (Sudah Ditinggalkan)
============================================================

> Note|Catatan: Code generator dalam `yiic shell` sudah mulai ditinggalkan semenjak versi 1.1.2. Silahkan menggunakan generator berbasis Web yang lebih powerful dan dapat diperluas dengan [Gii](/doc/guide/topics.gii).

Buka sebuah window command line dan eksekusi perintah-perintah berikut,

~~~
% cd WebRoot/testdrive
% protected/yiic shell
Yii Interactive Tool v1.1
Please type 'help' for help. Type 'exit' to quit.
>> model User tbl_user
   generate models/User.php
   generate fixtures/tbl_user.php
   generate unit/UserTest.php

The following model classes are successfully generated:
    User

If you have a 'db' database connection, you can test these models now with:
    $model=User::model()->find();
    print_r($model);

>> crud User
   generate UserController.php
   generate UserTest.php
   mkdir D:/testdrive/protected/views/user
   generate create.php
   generate update.php
   generate index.php
   generate view.php
   generate admin.php
   generate _form.php
   generate _view.php

Crud 'user' has been successfully created. You may access it via:
http://hostname/path/to/index.php?r=user
~~~

Berdasarkan contoh di atas, kita menggunakan perintah `yiic shell` untuk berinteraksi
dengan kerangka aplikasi kita. Di prompt(command line), kita mengeksekusi dua buah sub-perintah:
`model User tbl_user` dan `crud User`. Perintah yang pertama tujuannya untuk menghasilkan sebuah kelas model
bernama `User` untuk tabel `tbl_user`. Perintah yang kedua menganalisa model `User` dan menghasilkan kode
untuk yang mengoperasikan CRUD.

> Note|Catatan: Anda mungkin akan menemukan error seperti "...could not find driver", walaupun
> ketika melihat pengecek persyaratan PDO dan driver PDO yang bersangkutan sudah diaktifkan
> Apabila demikian mungkin Anda dapat mencoba menjalankan tool `yiic` dengan ini,
>
> ~~~
> % php -c path/to/php.ini protected/yiic.php shell
> ~~~
>
> dengan `path/to/php.ini` merupakan jalur ke file .ini si PHP.


Sekarang kita dapat melihat hasilnya dengan menggunakan URL:

~~~
http://hostname/testdrive/index.php?r=user
~~~

URL ini akan menampilkan daftar user yang terdapat dalam tabel `tbl_user`.

Klik tombol `Create User` akan membawa ke halaman login jika sebelumnya
belum melakukan login. Setelah login, kita akan melihat sebuah form input yang memungkinkan
kita untuk memasukkan user baru. Isi form dan klik tombol `Create`. Jika ada penginputan yang salah
maka akan muncul sebuah pesan error yang mencegah kita untuk menyimpan user. Kembali ke halaman list,
kita seharusnya akan melihat sebuah user baru yang baru saja ditambah.

Ulangi langkah di atas untuk menambah lebih banyak user. Perhatikan bahwa list user akan otomatis
diberi pagination jika daftar user terlalu banyak untuk dimasukkan ke dalam satu halaman.

Jika kita login sebagai administrator dengan menggunakan `admin/admin`, kita dapat melihat
halaman user admin dengan URL:

~~~
http://hostname/testdrive/index.php?r=user/admin
~~~

Halaman ini akan menampilkan daftar user dalam format tabel. Jika kita klik header-nya
maka kolom yang bersangkutan akan disusun. Jika kita klik tombol setiap baris untuk view,update atau
delete maka akan dilakukan operasi sesuai dengan tombol yang diklik. Kita dapat menjelajah halaman-halaman
yang berbeda. Kita juga dapat memfilter dan mencari data yang kita inginkan.

Semua fitur-fitur menarik ini didapatkan tanpa perlu kita menulis satu baris kode sama sekali!

![User admin page](first-app6.png)

![Create new user page](first-app7.png)



<div class="revision">$Id: quickstart.first-app-yiic.txt 2098 2010-05-05 19:49:51Z qiang.xue $</div>