Data Caching
===========================

Data caching sebenarnya berhubungan dengan penyimpanan beberapa variabel PHP dalam cache dan mengambilnya
kemudian dari cache. Untuk keperluan ini, basis komponen cache [CCache]
menyediakan dua metode yang dipakai dari waktu ke waktu: [set()|CCache::set]
dan [get()|CCache::get].

Untuk menyimpan variabel `$value` dalam cache, kita memilih ID unik dan memanggil
[set()|CCache::set] untuk menyimpannya:

~~~
[php]
Yii::app()->cache->set($id, $value);
~~~

Data yang ditembolok akan tetap berada dalam cache selamanya kecuali ia dihapus
karena beberapa kebijakan caching (contoh, ruang cache penuh dan data terlama
dihapus). Untuk mengubah perilaku umum kita juga bisa menyediakan
parameter masa hidup(life-time) saat memanggil [set()|CCache::set] dengan demikian data
akan dihapus dari cache setelah periode waktu tertentu:

~~~
[php]
// perlihara nilai dalam cache paling lama 30 detik.
Yii::app()->cache->set($id, $value, 30);
~~~

Selanjutnya, saat kita perlu mengakses variabel ini (baik dalam permintaan Web 
yang sama atau berbeda), kita memanggil [get()|CCache::get] dengan ID untuk mengambilnya 
dari cache. Jika nilai yang dikembalikan false, ini berarti nilai
tidak tersedia dalam cache dan kita harus membuatnya kembali.

~~~
[php]
$value=Yii::app()->cache->get($id);
if($value===false)
{
	// buat ulang $value karena tidak ditemukan dalam cache
	// dan simpan dalam cache untuk dipakai nanti:
	// Yii::app()->cache->set($id,$value);
}
~~~

Ketika memilih ID untuk variabel yang ditembolok, pastikan ID unik di antara
semua variabel lain yang mungkin ditembolok dalam aplikasi. ID tidak perlu
unik di antara berbagai aplikasi karena komponen cache cukup pintar
untuk membedakan ID pada aplikasi yang 
berbeda.

Beberapa penyimpanan cache, seperti MemCache, APC, mendukung pengambilan
beberapa nilai yang ditembolok dalam mode tumpak(batch), ini dapat mengurangi beban terkait
pada pengambilan data cache. Terdapat metode bernama
[mget()|CCache::mget] disediakan guna mengeksploitasi fitur ini. Dalam hal penyimpanan
cache lapisan bawah tidak mendukung fitur ini, [mget()|CCache::mget] masih tetap akan
mensimulasikannya.

Untuk menghapus nilai yang ditembolok dari cache, panggil [delete()|CCache::delete]; dan
untuk menghapus semuanya dari cache, panggil [flush()|CCache::flush]. Harap
berhati-hati saat memanggil [flush()|CCache::flush] karena ia juga menghapus data 
yang ditembolok yang berasal dari aplikasi lain.

> Tip: Karena [CCache] mengimplementasikan `ArrayAccess`, komponen cache bisa
> dipakai layaknya sebuah. Berikut adalah beberapa contoh:
> ~~~
> [php]
> $cache=Yii::app()->cache;
> $cache['var1']=$value1;  // sama dengan: $cache->set('var1',$value1);
> $value2=$cache['var2'];  // sama dengan: $value2=$cache->get('var2');
> ~~~

Ketergantungan Tembolok
--------------------

Selain pengaturan masa hidup, data yang ditembolok juga bisa disegarkan berdasarkan
pada beberapa perubahan ketergantungan. Sebagai contoh, jika kita men-cache konten
beberapa file dan file berubah, kita harus menyegarkan duplikat yang ditembolok
dan membaca konten terbaru dari file alih-alih cache.

Kami menyajikan ketergantungan sebagai turunan dari [CCacheDependency] atau anak
kelasnya. Kami mengoper turunan ketergantungan bersamaan dengan data yang ditembolok
saat pemanggilan [set()|CCache::set].

~~~
[php]
// nilai akan berakhir dalam 30 detik
// ini juga akan disegarkan sebelumnya jika file dependen diubah
Yii::app()->cache->set($id, $value, 30, new CFileCacheDependency('FileName'));
~~~

Sekarang, jika kita mengambil `$value` dari cache dengan memanggil [get()|CCache::get],
ketergantungan akan dievaluasi dan jika ia berubah, kita akan mendapat nilai false 
yang menunjukan data perlu dibuat ulang.

Di bawah ini adalah ringkasan ketergantungan cache yang tersedia:

   - [CFileCacheDependency]: ketergantungan diubah jika waktu modifikasi 
   file terakhir diubah.

   - [CDirectoryCacheDependency]: ketergantungan diubah jika file di
   bawah direktori dan subdirektorinya berubah.

   - [CDbCacheDependency]: ketergantungan diubah jika hasil kueri
pernyataan SQL yang ditetapkan berubah.

   - [CGlobalStateCacheDependency]: ketergantungan diubah jika nilai
kondisi global yang ditetapkan berubah. Kondisi global adalah variabel
yang persisten pada beberapa permintaan dan beberapa sesi dalam aplikasi.
Ketergantungan ini didefinisikan melalui [CApplication::setGlobalState()].

   - [CChainedCacheDependency]: ketergantungan diubah jika salah satu 
   rantai berubah.

   - [CExpressionDependency]: ketergantungan berubah jika hasil yang 
   ditetapkan ekspresi PHP diubah. 


Query Caching
-------------

Mulai dari versi 1.1.7, Yii telah menambah fungsi query chacing.
Query Chacing dibuat di atas data caching, digunakan untuk menyimpan hasil query DB
ke dalam cache sehingga menghemat waktu eksekusi query DB jika query yang sama diminta
di masa mendatang, sehingga bisa langsung diambil dari cache.

> Info: Beberapa DBMS (contohnya [MySQL](http://dev.mysql.com/doc/refman/5.1/en/query-cache.html))
> juga mendukung query caching pada bagian server DB. Dibandingkan dengan query caching
> pada bagian server, fitur yang kami dukung disini lebih fleksibel dan
> berpotensi untuk lebih efisien.

### Mengaktifkan Query Caching

Untuk mengaktifkan query caching, pastikan

### Enabling Query Caching [CDbConnection::queryCacheID] merujuk ke ID component
aplikasi cache yang valid (secara default ke `cache`).

### Menggunakan Query Caching dengan DAO

Untuk menggunakan query caching, kita memanggil method [CDbConnection::cache()] ketika melakukan query DB.
Berikut contohnya:

~~~
[php]
$sql = 'SELECT * FROM tbl_post LIMIT 20';
$dependency = new CDbCacheDependency('SELECT MAX(update_time) FROM tbl_post');
$rows = Yii::app()->db->cache(1000, $dependency)->createCommand($sql)->queryAll();
~~~

Ketika menjalankan statement di atas, Yii pertama-tama akan mengecek cache berisi
konten statement SQL yang valid untuk dieksekusi. Untuk itu dilakukan pengecekan dengan 3 keadaan;

- Jika cache berisi sebuah entri yang diindeks oleh statement SQL.
- Jika entri belum kadaluarsa (krang dari 1000 deik semenjak disimpan ke cache)
- jika ketergantungannya tidak berubah (nilai maksimal `update_time` sama ketika
hasil query disimpan ke cache.

Jika ketiga kondisi itu terpenuhi, maka hasil cache akan dikembalikan dari cache.
Kalau tidak, maka statement SQL akan dikirim ke server DB untuk dijalankan, dan hasil bersangkutan
akan disimpan ke cache.


### Menggunakan Query Caching dengan ActiveRecord

Query caching dapat juga digunakan dengan [Active Record](/doc/guide/database.ar).
Untuk itu, kita memanggil fungsi [CActiveRecord::cache()] dengan cara berikut:

~~~
[php]
$dependency = new CDbCacheDependency('SELECT MAX(update_time) FROM tbl_post');
$posts = Post::model()->cache(1000, $dependency)->findAll();
// relational AR query
$posts = Post::model()->cache(1000, $dependency)->with('author')->findAll();
~~~

Method `cache()` di sini secara esensinya adalah shortcut untuk [CDbConnection::cache()].
Secara internal, ketika mengeksekusi statement SQL yang dihasilkan oleh ActiveRecord, Yii
akan mencoba menggunakan query caching seperti yang dijelaskan pada bagian sebelumnya.


### Caching Multiple Queries

Secara default, setiap kali kita memanggil method `cache()` (baik [CDbConnection] ataupun [CActiveRecord]),
akan menandai query SQL berikutnya untuk di-cache. Query SQL lainnya tidak akan di-cache
sampai kita memanggil `cache()` lagi. Misalnya,

~~~
[php]
$sql = 'SELECT * FROM tbl_post LIMIT 20';
$dependency = new CDbCacheDependency('SELECT MAX(update_time) FROM tbl_post');

$rows = Yii::app()->db->cache(1000, $dependency)->createCommand($sql)->queryAll();
// query caching TIDAK akan digunakan
$rows = Yii::app()->db->createCommand($sql)->queryAll();
~~~

Dengan menyediakan parameter ekstra `$queryCount` ke method `cache()`, kita memaksa
multiple query untuk menggunakan query caching. Di contoh berikut, ketika kita memanggil fungsi `cache()`,
kita akan menentukan query caching harus digunakan untuk 2 query berikutnya:

~~~
[php]
// ...
$rows = Yii::app()->db->cache(1000, $dependency, 2)->createCommand($sql)->queryAll();
// query caching WILL be used
$rows = Yii::app()->db->createCommand($sql)->queryAll();
~~~

Seperti yang diketahui, ketika melakukan query AR relasi, ada kemungkinan beberapa query SQL
akan dijalankan (silahkan cek di [log messages](/doc/guide/topics.logging)).
Misalnya, untuk relasi antara `Post` dan `Comment` yang `HAS_MANY`,
maka code berikut akan mengeksekusi dua query:

- Pertama select post sebanyak 20;
- Kemudian select comment untuk post yang di-select sebelumnya.

~~~
[php]
$posts = Post::model()->with('comments')->findAll(array(
	'limit'=>20,
));
~~~

Jika kita menggunakan query caching demikian, maka hanya query DB pertama yang di-cache.

~~~
[php]
$posts = Post::model()->cache(1000, $dependency)->with('comments')->findAll(array(
	'limit'=>20,
));
~~~

Supaya kedua-dua query DB di-cache, kita harus memberikan parameter ekstra berapa
banyak query DB yang akan di-cache berikutnya:

~~~
[php]
$posts = Post::model()->cache(1000, $dependency, 2)->with('comments')->findAll(array(
	'limit'=>20,
));
~~~


### Batasan

Query caching tidak bekerja pada hasil query yang mengandung pengatur resource. Misalnya,
ketika menggunakan kolom jenis `BLOB` di beberapa DBMS, hasil query akan mengembalikan resource
handle untuk kolom data.

Beberapa simpanan caching memiliki limitasi pada ukurannya. Misalnya, memcache membatasi
ukuran setiap entri hanya 1MB. Oleh karenanya, jika ukuran query melebihi batasan ini,
maka caching akan gagal.


<div class="revision">$Id: caching.data.txt 3125 2011-03-25 17:05:31Z qiang.xue $</div>