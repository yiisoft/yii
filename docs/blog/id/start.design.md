Perancangan Keseluruhan
==============

Berdasarkan analisis kebutuhan, kita memutuskan menggunakan tabel database berikut untuk menyimpan data yang permanen untuk aplikasi blog:

 * `tbl_user` menyimpan informasi user, termasuk username dan password.
 * `tbl_post` menyimpan informasi post blog. Utamanya terdiri dari kolom-kolom berikut:
	 - `title`: wajib diperlukan, merupakan judul dari post;
	 - `content`: wajib diperlukan, isi dari post yang menggunakan [format Markdown](http://daringfireball.net/projects/markdown/syntax);
	 - `status`: wajib diperlukan, status dari post, dapat berupa nilai-nilai berikut ini:
		 * 1, artinya post masih dalam tahap draf dan tidak terlihat oleh publik;
		 * 2, artinya post dipublikasi ke publik;
		 * 3, artinya post sudah kadaluarsa dan tidak kelihatan di dalam daftar post (tetapi masih dapat diakses secara individu).
	 - `tags`: opsional, sebuah daftar kata-kata yang dipisahkan koma untuk mengkategorikan post.
 * `tbl_comment` menyimpan informasi komentar post. Setiap komentar berhubungan dengan sebuah post dan memiliki kolom-kolom sebagai berikut:
	 - `name`: wajib diperlukan, nama sang pembuat;
	 - `email`: wajib diperlukan, email sang pembuat;
	 - `website`: opsional, URL website milik sang pembuat;
	 - `content`: wajib diperlukan, isi komentar dalam format teks polos.
	 - `status`: wajib diperlukan, status komentar, yang menunjukkan apakah komentar disetujui (nilai 2) atau tidak (nilai 1).
 * `tbl_tag` menyimpan informasi frekuensi tag yang diperlukan untuk mengimplementasi fitur tag cloud. Tabel ini memiliki kolom-kolom:
 	 - `name`: wajib diperlukan, nama tag yang unik;
 	 - `frequency`: wajib diperlukan, jumlah berapa kali tag muncul dalam post
 * `tbl_lookup` menyimpan informasi pencarian (lookup) yang umum. Pada dasarnya merupakan pemetaan antara nilai integer dan string teks. Nilai integer merupakan data representasi di dalam kode kita, sedangkan string teks berkaitan dengan tampilan ke pengguna. Misalnya, kita menggunakan integer 1 untuk mewakili status post draf dan kata `Draft` ditampilkan ke pengguna akhir (end-user). Tabel ini terdiri dari kolom :
 	 - `name`: representasi tekstual dari item data yang akan ditampilkan ke user;
 	 - `code`: nilai integer yang representasikan dengan itema data;
 	 - `type`: jenis item data;
 	 - `position`: Urutan menampilkan item data di antara item lain yang berjenis sama.


Diagram entity-relation (ER) berikut menunjukkan struktur tabel dan hubungan antar tabel-tabel di atas.

![Entity-Relation Diagram of the Blog Database](schema.png)


Statement SQL yang lengkap berkaitan dengan diagram ER di atas dapat ditemukan dalam [demo blog](http://www.yiiframework.com/demos/blog/). Di dalam instalasi Yii, statement SQL tersebut berada di file `/wwwroot/yii/demos/blog/protected/data/schema.sqlite.sql`.



> Info: Kita menamakan seluruh nama tabel dan kolom dalam huruf kecil. Dikarenakan perbedaan DBMS sering sekali memiliki perbedaan perlakuan case-sensitif dan kita ingin menghindari masalah ini.
>
> Kita juga mengawali seluruh nama tabel dengan `tbl_`. Terdapat dua tujuan. Pertama, prefiks memperkenalkan namespace untuk tabel-tabel ini jika terdapat tabel lain di dalam database yang sama, yang sering terjadi ketika di lingkungan shared hosting di mana sebuah database digunakan oleh lebih dari satu aplikasi. Kedua, menggunakan prefiks tabel mengurangi kemungkinan untuk menamakan tabel dengan kata kunci yang sudah ada di DBMS.


Kita membagi tahap pengembangan menjadi beberapa.

 * Tahap 1: membuat sebuah sistem blog prototype.Sistem harus terdiri dari hampir semua fungsi kebutuhan.
 * Tahap 2: menyelesaikan manajemen post. Termasuk membuat, menampilkan daftar, menunjukkan, mengubah dan menghapus post.
 * Tahap 3: manajemen komentar yang lengkap. Termasuk membuat, menampilkan daftar, menyutujui, mengubah dan menghapus.
 * Tahap 4: mengimplementasi portlet. Termasuk portlet user menu, login, tag cloud dan komentar terbaru.
 * Tahap 5: tune-up terakhir dan pemasangan

<div class="revision">$Id: start.design.txt 1677 2010-01-07 20:29:26Z qiang.xue $</div>