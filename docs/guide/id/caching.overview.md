Penembolokan(Caching)
=====================

Penembolokan(Caching) merupakan sebuah cara sederhana dan efektif guna meningkatkan performa aplikasi
Web. Dengan menyimpan data yang relatif statis dalam cache dan mengambilnya dari
cache bila diperlukan, maka kita dapat menghemat waktu yang diperlukan dalam menghasilkan data.

Pada utamanya, pemakaian cache dalam Yii mencakup pengaturan dan pengaksesan komponen aplikasi
cache. Konfigurasi aplikasi berikut merincikan komponen cache yang
menggunakan memcache dengan dua server cache.

~~~
[php]
array(
	......
	'components'=>array(
		......
		'cache'=>array(
			'class'=>'system.caching.CMemCache',
			'servers'=>array(
				array('host'=>'server1', 'port'=>11211, 'weight'=>60),
				array('host'=>'server2', 'port'=>11211, 'weight'=>40),
			),
		),
	),
);
~~~

Ketika aplikasi berjalan, komponen cache dapat diakses melalui
`Yii::app()->cache`.

Yii menyediakan berbagai komponen cache yang dapat menyimpan data cache dalam
berbagai media. Misalnya, komponen [CMemCache] mengenkapsulasi extension
PHP memcache dan menggunakan memori sebagai media penyimpanan cache; komponen
[CApcCache] mengenkapsulasi extension PHP APC; dan komponen
[CDbCache] menyimpan data cache di dalam database. Berikut ini adalah
komponen-komponen cache yang tersedia:

   - [CMemCache]: menggunakan [extension memcache](http://www.php.net/manual/en/book.memcache.php) PHP.

   - [CApcCache]: menggunakan [extension APC](http://www.php.net/manual/en/book.apc.php) PHP.

   - [CXCache]: menggunakan [extension XCache](http://xcache.lighttpd.net/) PHP.

   - [CEAcceleratorCache]: menggunakan PHP [EAccelerator extension](http://eaccelerator.net/).

   - [CDbCache]: menggunakan tabel database untuk menyimpan data cache. Secara default,
komponen ini akan membuat serta menggunakan database SQLite3 di direktori runtime. Anda
dapat menetapkan database yang ingin dipakai secara eksplisit dengan mengatur properti
[connectionID|CDbCache::connectionID].

   - [CZendDataCache]: menggunakan Zend Data Cacheuses [Zend Data Cache](http://files.zend.com/help/Zend-Server-Community-Edition/data_cache_component.htm)
 sebagai media pokok caching.

   - [CFileCache]: menggunakan file untuk menyimpan data cache. Komponen ini biasanya seringkali cocok untuk
menembolok potongan data yang besar(misalnya halaman).

   - [CDummyCache]: menyajikan cache tiruan(cache dummy) yang tidak melakukan caching sama sekali. Tujuan
komponen ini adalah menyederhanakan kode yang perlu memeriksa ketersediaan cache.
Misalnya, selama pengembangan atau jika server tidak memiliki dukungan cache yang sebenarnya, kita
dapat menggunakan komponen cache ini. Seandainya dukungan cache yang sebenarnya dihidupkan, kita dapat beralih
ke penggunaan komponen cache yang terkait. Dalam kedua kasus tersebut, kita dapat menggunakan kode yang sama
`Yii::app()->cache->get($key)` untuk mencoba mengambil bagian data tanpa perlu mencemaskan
apakah `Yii::app()->cache` mungkin bernilai `null`. 

> Tip: Karena semua komponen cache ini diturunkan dari basis kelas yang sama, yakni
[CCache], Anda bisa beralih untuk menggunakan tipe cache yang lain tanpa perlu mengubah
kode yang menggunakan cache.

Penembolokan dapat dipakai pada tingkat yang bebeda. Pada tingkat terendah, kita menggunakan cache
untuk menyimpan sebuah data, misalnya sebuah variabel, dan kita menyebutnya
*caching data(data caching)*. Pada tingkat berikutnya, kita menyimpan sebuah fragmen halaman
dalam cache yang dibuat oleh bagian skrip tilik(view script). Dan pada tingkat
tertinggi, kita menyimpan seluruh halaman ke dalam cache dan mengambil dari cache bila diperlukan.

Dalam beberapa subbab berikut, kita akan menguraikan bagaimana untuk menggunakan
cache pada tingkatan-tingkatan tersebut.

> Note|Catatan: Secara definisi, cache merupakan media penyimpanan yang tidak tetap alias volatile.
Cache tidak memastikan keberadaan data yang di-cache meskipun belum kadaluarsa.
Oleh karenanya, jangan menggunakan cache sebagai tempat penyimpanan persisten (misalnya, jangan
menggunakan cache untuk menyimpan session data).

<div class="revision">$Id: caching.overview.txt 2890 2011-01-18 15:58:34Z qiang.xue $</div>