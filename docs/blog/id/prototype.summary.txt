Kesimpulan
=======

Kita telah menyelesaikan tahap pertama. Marilah kita menyimpulkan apa yang sudah kita lakukan sejauh ini:

 1. Kita mengidentifikasi keperluan yang harus dicapai
 2. Kita menginstalasi framework Yii;
 3. Kita membuat sebuah aplikasi kerangka;
 4. Kita merancang dan membuat database blog;
 5. Kita memodifikasi konfigurasi aplikasi dengan menambah koneksi database;
 6. Kita men-generate kode yang mengimplementasi operasi CRUD dasar untuk post dan komentar;
 7. Kita memodifikasi method otentikasi untuk mengecek pada tabel `tbl_user`.

Untuk projek baru, waktu yang dihabiskan paling lama adalah langkah pertama dan keempat untuk tahap pertama ini.

Walaupun tool `gii` men-generate kode yang mengimplementasikan operasi CRud untuk tabel database, sering sekali harus dilakukan perubahan pada aplikasi. Dikarenakan itulah, pada kedua tahap berikutnya, tugas kita adalah mengkustomisasi kode CRUD post dan komentar yang sudah di-generate sehingga tercapai requirement awal kita.

Pada umumnya, kita pertama-tama memodifikasi kelas [model](http://www.yiiframework.com/doc/guide/basics.model) dengan menambahkan aturan [validasi](http://www.yiiframework.com/doc/guide/form.model#declaring-validation-rules) yang sesuai dan mendeklarasikan [relational object](http://www.yiiframework.com/doc/guide/database.arr#declaring-relationship). Kita kemudian memodifikasi kode [controller action](http://www.yiiframework.com/doc/guide/basics.controller) dan [view](http://www.yiiframework.com/doc/guide/basics.view) untuk setiap operasi CRUD individu.


<div class="revision">$Id: prototype.summary.txt 2333 2010-08-24 21:11:55Z mdomba $</div>