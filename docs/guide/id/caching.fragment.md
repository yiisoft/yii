Caching Fragment
====================================

Caching fragmen merujuk pada melakukan caching pada sebuah bagian halaman. Sebagai
contoh, jika halaman menampilkan ringkasan penjualan tahunan berbentuk tabel,
kita dapat menyimpan tabel ini pada cache guna mengeliminasi waktu yang
dibutuhkan dalam membuatnya untuk setiap permintaan.

Untuk menggunakan caching fragmen, kita memanggil
[CController::beginCache()|CBaseController::beginCache()] dan
[CController::endCache()|CBaseController::endCache()] dalam skrip tampilan
controller. Masing-masing dari dua metode menandai awal dan akhir konten halaman 
yang harus di-cache. Seperti [caching data](/doc/guide/caching.data), kita memerlukan
ID guna mengidentifikasi fragmen yang sedang ditembolok.

~~~
[php]
...konten HTML lain...
<?php if($this->beginCache($id)) { ?>
...konten yang di-cache...
<?php $this->endCache(); } ?>
...konten HTML lain...
~~~

Dalam contoh di atas, jika [beginCache()|CBaseController::beginCache()] 
mengembalikan false, konten yang di-cache akan disisipkan secara otomatis;
sebaliknya, konten di dalam pernyataan-`if` yang akan dijalankan dan di-cache
saat [endCache()|CBaseController::endCache()] dipanggil.

Opsi Caching
----------

Ketika memanggil [beginCache()|CBaseController::beginCache()], kita dapat 
menyediakan array sebagai parameter kedua yang terdiri dari opsi cache untuk
mengkustomisasi cache fragmen. Bahkan pada dasarnya, metode
[beginCache()|CBaseController::beginCache()] dan 
metode [endCache()|CBaseController::endCache()] 
adalah pembungkus untuk widget [COutputCache]. Oleh karenanya, opsi
cache dapat bernilai awal untuk setiap properti [COutputCache].

### Durasi

Barangkali, opsi paling umum adalah [duration|COutputCache::duration]
yang menetapkan berapa lama konten tetap benar dalam cache. Opsi ini mirip
dengan parameter masa hidup [CCache::set()]. Kode berikut melakukan 
caching fragmen konten untuk satu jam:

~~~
[php]
...konten HTML lain...
<?php if($this->beginCache($id, array('duration'=>3600))) { ?>
...konten yang ditembolok...
<?php $this->endCache(); } ?>
...konten HTML lain...
~~~

Jika kita tidak menyetel durasi, standarnya adalah 60, berarti konten
di-cache akan disegarkan setelah 60 detik.

Semenjak versi 1.1.8, jika durasi di set menjadi 0, maka segala
konten yang ter-cache akan dihapus dari cache. Jika nilai durasi dalam bentuk negatif,
cache akan di-disable, namun konten yang di-cache tetap ada di dalam cache.
Sebelum versi 1.1.8, jika durasi bernilai 0 atau negatif, cache akan di-disable.

### Ketergantungan

Seperti halnya [caching data](/doc/guide/caching.data), fragmen konten yang
sedang di-cache juga bisa memiliki ketergantungan. Sebagai contoh, konten
tulisan yang sedang ditampilkan tergantung apakah tulisan dimodifikasi atau tidak.

Untuk menetapkan ketergantungan, kita menyetel opsi [dependency|COutputCache::dependency],
yang bisa berupa obyek yang mengimplementasi [ICacheDependency] atau array 
konfigurasi yang dapat dipakai guna menghasilkan obyek dependensi. Kode berikut 
menetapkan konten fragmen yang tergantung pada perubahan nilai kolom 
`lastModified`:

~~~
[php]
...konten HTML lain...
<?php if($this->beginCache($id, array('dependency'=>array(
		'class'=>'system.caching.dependencies.CDbCacheDependency',
		'sql'=>'SELECT MAX(lastModified) FROM Post')))) { ?>
...konten yang ditembolokan...
<?php $this->endCache(); } ?>
...konten HTML lain...
~~~

### Variasi

Konten yang sedang ditembolok dapat divariasikan berdasarkan pada beberapa parameter.
Sebagai contoh, profil personal bisa terlihat berbeda bagi pengguna yang berbeda. 
Untuk menembolok konten profil, kita ingin duplikat yang ditembolok divariasikan
berdasarkan ID pengguna. Pada dasarnya, kita harus menggunakan ID berbeda saat memanggil
[beginCache()|CBaseController::beginCache()].

Alih-alih meminta para pengembang untuk memvariasikan ID berdasarkan pada beberapa skema,
[COutputCache] adalah fitur built-in untuk hal itu. Di bawah ini ringkasannya.

   - [varyByRoute|COutputCache::varyByRoute]: dengan menyetel opsi ini
menjadi true, konten yang di-cache kan divariasikan berdasarkan
[rute](/doc/guide/basics.controller#route). Oleh karena itu, setiap kombinasi
controller dan aksi yang diminta akan memiliki konten di-cache terpisah.

   - [varyBySession|COutputCache::varyBySession]: dengan menyetel opsi ini
menjadi true, kita bisa membuat konten di-cache divariasikan berdasarkan ID
sesi. Oleh karena itu, setiap sesi pengguna dapat melihat konten secara berbeda 
dan semuanya dilayani dari cache.

   - [varyByParam|COutputCache::varyByParam]: dengan menyetel opsi ini
menjadi array nama, kita dapat membuat konten ditembolok divariasikan
berdasarkan nilai yang ditetapkan parameter GET. Sebagai contoh, jika halaman
menampilkan konten tulisan berdasarkan parameter GET `id`, kita bisa menetapkan
[varyByParam|COutputCache::varyByParam] menjadi `array('id')` dengan demikian 
kita dapat men-cache konten untuk setiap tulisan. Tanpa variasi seperti ini,
kita hanya bisa men-cache satu tulisan.

   - [varyByExpression|COutputCache::varyByExpression]: dengan menyetel opsi ini
menjadi ekspresi PHP, kita dapat membuat isi cache bervariasi sesuai dengan
hasil ekspresi PHP.

### Jenis Request

Ada kalanya kita menginginkan caching fragmen diaktifkan hanya untuk jenis
request tertentu. Sebagai contoh, untuk halaman yang menampilkan form,
kita hanya ingin men-cache form saat awal form diminta (melalui request GET).
Setiap tampilan selanjutnya (melalui request POST) terhadap form tidak harus
di-cache lagi karena form mungkin berisi input pengguna. Untuk melakukannya,
kita dapat menetapkan opsi [requestTypes|COutputCache::requestTypes]:

~~~
[php]
...konten HTML lain...
<?php if($this->beginCache($id, array('requestTypes'=>array('GET')))) { ?>
...konten yang di-cache...
<?php $this->endCache(); } ?>
...konten HTML lain...
~~~

Nested Caching
--------------

Caching fragmen dapat diulang. Maksudnya, fragmen yang di-cache disertakan dalam
fragmen yang lebih besar yang juga di-cache. Sebagai contoh, komentar di-cache
dalam fragmen cache lebih dalam, dan di-cache bersama dengan konten tulisan di
cache fragmen lebih luar.

~~~
[php]
...konten HTML lain...
<?php if($this->beginCache($id1)) { ?>
...konten lebih luar di-cache...
	<?php if($this->beginCache($id2)) { ?>
	...konten lebih dalam di-cache...
	<?php $this->endCache(); } ?>
...konten lebih luar di-cache...
<?php $this->endCache(); } ?>
...konten HTML lain...
~~~

Opsi cache yang berbeda dapat disetel untuk pengulangan caching. Sebagai contoh,
cache yang lebih dalam dan cache yang lebih luar dalam contoh di atas dapat disetel dengan
nilai durasi yang berbeda. Saat data di-cache masuk cache yang lebih luar
tidak valid, cache yang lebih dalam yang masih bisa menyediakan fragmen dalam yang valid.
Akan tetapi, tidak dapat dilakukan sebaliknya. Jika cache yang luar berisi
data yang benar, maka akan selalu menyediakan duplikat yang di-cache, meskipun konten
cache yang dalam sudah kadaluarsa.

<div class="revision">$Id: caching.fragment.txt 3315 2011-06-24 15:18:11Z qiang.xue $</div>