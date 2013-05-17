Logging
==================

Yii menyediakan fitur logging yang fleksibel dan bisa ekstensibel. Logging message
dapat diklasifikasikan berdasarkan level log atau kategori message. Menggunakan
filter tingkat dan kategori, message yang dipilih selanjutnya bisa diarahkan ke
tujuan yang berbeda, misalnya file, email, jendela browser, dll.

Logging Message
----------------

Message dapat dicatat dengan memanggil [Yii::log] atau [Yii::trace]. Perbedaan
di antara kedua metode ini adalah bahwa [Yii::trace] hanya mencatat message
saat aplikasi dalam [mode debug](/doc/guide/basics.entry#debug-mode).

~~~
[php]
Yii::log($message, $level, $category);
Yii::trace($message, $category);
~~~

Ketika mencatat message, kita harus menetapkan tingkat dan kategorinya.
Kategori adalah string dalam bentuk `xxx.yyy.zzz` yang mirip dengan
[alias path](/doc/guide/basics.namespace). Contoh, jika message dicatat
dalam [CController], kita bisa menggunakan kategori `system.web.CController`.
Level message harus salah satu dari nilai berikut:

   - `trace`: ini adalah tingkat yang dipakai oleh [Yii::trace]. kegunaannya untuk melacak
alur eksekusi aplikasi selama pengembangan.

   - `info`: ini untuk pencatatan informasi umum.

   - `profile`: ini untuk profil performa (yang akan segera
dijelaskan).

   - `warning`: ini untuk message peringatan.

   - `error`: ini untuk message kesalahan fatal.

Message Routing
---------------------

Message yang dicatat menggunakan [Yii::log] or [Yii::trace] disimpan dalam memori. Kita
biasanya harus menampilkannya dalam browser, atau menyimpannya dalam beberapa
media penyimpan yang menetap seperti file, email. Ini disebut *messages routing(mengirimkan
pesan)*, misalnya mengirimkan message ke tujuan yang berbeda.

Dalam Yii, message routing diatur oleh komponen aplikasi [CLogRouter].
[CLlogRouter] mengatur satu kumpulan yang disebut *log routes(rute log)*. Tiap log route
mewakili satu tujuan log. Message yang dikirimkan bersamaan dengan log route
bisa disaring berdasarkan pada tingkat dan kategorinya.

Untuk menggunakan message routing, kita harus menginstalasi dan mengaktifkan komponen
aplikasi [CLogRouter]. Kita juga harus mengkonfigurasi properti
[routes|CLogRouter::routes] dengan log route yang kita inginkan. Berikut ini
ditampilkan contoh [konfigurasi
aplikasi](/doc/guide/basics.application#application-configuration) yang dibutuhkan:

~~~
[php]
array(
	......
	'preload'=>array('log'),
	'components'=>array(
		......
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'trace, info',
					'categories'=>'system.*',
				),
				array(
					'class'=>'CEmailLogRoute',
					'levels'=>'error, warning',
					'emails'=>'admin@example.com',
				),
			),
		),
	),
)
~~~

Dalam contoh di atas, kita memiliki dua log route. Rute pertama adalah
[CFileLogRoute] yang menyimpan message dalam sebuah file di bawah direktori
runtime aplikasi. Hanya message-message yang tingkatnya adalah `trace` atau `info` dan
kategorinya dimulai dengan `system.` yang disimpan. Rute kedua adalah
[CEmailLogRoute] yang mengirimkan message ke alamat email yang sudah ditetapkan.
Hanya message yang tingkatannya `error` atau `warning` yang dikirimkan.

Berikut Log Route yang tersedia dalam Yii:

   - [CDbLogRoute]: menyimpan message dalam tabel database.
   - [CEmailLogRoute]: mengirimkan message ke alamat email yang ditetapkan.
   - [CFileLogRoute]: menyimpan message dalam sebuah file di bawah direktori runtime aplikasi.
   - [CWebLogRoute]: menampilkan message di akhir halaman Web saat ini.
   - [CProfileLogRoute]: menampilkan message profil di akhir halaman Web saat ini.

> Info: Pengiriman message terjadi di akhir siklus permintaan saat ini
saat event [onEndRequest|CApplication::onEndRequest] dimunculkan. Untuk mengakhiri
pemrosesan permintaan saat ini secara eksplisit, panggil [CApplication::end()] alih-alih
`die()` atau `exit()`, karena [CApplication::end()] akan memunculkan event
[onEndRequest|CApplication::onEndRequest] dan message bisa dicatat
dengan benar.

Penyaringan Message
----------------------------------

Seperti yang telah dijelaskan, message dapat difilter berdasarkan tingkatan dan kategori
nya sebelum lama dikirim ke log route. Hal ini dapat dicapai dengan melakukan
pengaturan pada properti [levels|CLogRoute::levels] dan [categories|CLogRoute::categories]
pada log route yang bersangkutan. Diharuskan memberikan titik koma jika
ingin menggabungkan beberapa tingkatan dan kategori.

Karena kategori message dalam bentuk `xxx.yyy.zzz`, kita bisa memperlakukannya
sebagai hirarki kategori. Khususnya, kita katakan `xxx` adalah induk dari
`xxx.yyy` yang merupakan induk dari `xxx.yyy.zzz`. Selanjutnya kita bisa menggunakan
`xxx.*` untuk mewakili kategori `xxx` dan semua turunan
kategori.

Pencatatan Informasi Konteks
-----------------------------------------------

Kita bisa menetapkan log informasi konteks tambahan,
seperti variabel pradefinisi PHP (misal `$_GET`, `$_SERVER`), session ID, nama pengguna, dll.
Ini dilakukan dengan menetapkan properti [CLogRoute::filter] pada rute log agar sesuai dengan penyaringan log.

Framework dilengkapi dengan [CLogFilter] yang bisa dipakai sebagai filter log yang paling 
dibutuhkan dalam banyak hal. Secara standar, [CLogFilter] akan mencatat message dengan variabel seperti
`$_GET`, `$_SERVER` yang seringkali berisi informasi konteks sistem yang berharga.
[CLogFilter] juga dapat dikonfigurasi untuk mengawali setiap message yang dicatat dengan ID sesi, nama pengguna, dll.,
yang secara menyeluruh menyederhanakan pencarian global saat memeriksa berbagai message tercatat.

Konfigurasi berikut memperlihatkan bagaimana untuk mengaktifkan informasi konteks. Catatan bahwa setiap
rute log dapat memiliki filter lognya sendiri. Dan distandarkan, rute log tidak memiliki filter log.

~~~
[php]
array(
	......
	'preload'=>array('log'),
	'components'=>array(
		......
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error',
					'filter'=>'CLogFilter',
				),
				...other log routes...
			),
		),
	),
)
~~~


Mulai dari versi 1.0.7, Yii mendukung pencatatan pemanggilan informasi stack dalam message yang dicatat
dengan memanggil `Yii::trace`. Fitur ini dimatikan secara default karena memperlambat kinerja.
Untuk menggunakan fitur ini, cukup definisikan konstan bernama `YII_TRACE_LEVEL` di awal entri 
skrip (sebelum menyertakan `yii.php`) ke integer lebih besar daripada 0. Kemudian Yii akan menambahkan
setiap pelacakan message dengan nama file dan nomor baris pada stack panggilan yang dimiliki kode
aplikasi. Jumlah `YII_TRACE_LEVEL` menentukan berapa banyak lapisan dari setiap stack panggilan harus disimpan.
Informasi ini adakalanya berguna selama tahap pengembangan karena dapat membantu kita mengidentifikasi
tempat yang memicu pelacakan message.


Pengukuran Performa
-------------------

Pengukuran performa adalah jenis pencatatan message khusus. Pengukuran
performa bisa dipakai untuk mengukur waktu yang dibutuhkan untuk blok kode
yang ditetapkan dan mencari tahu hambatan(bottleneck) apa pada performa.

Untuk menggunakan pengukuran performa, kita harus mengidentifikasi blok kode
yang akan diukur. Kita tandai setiap awal dan akhir blok kode dengan menyisipkan
metode berikut:

~~~
[php]
Yii::beginProfile('blockID');
...blok kode sedang diukur...
Yii::endProfile('blockID');
~~~

dengan `blockID` adalah ID yang secara unik mengidentifikasi blok kode.

Harap diketahui, blok kode harus di-nested dengan benar. Yakni, blok kode tidak bisa
bersinggungan(intersect) dengan yang lain. Blok kode harus antara dalam tingkat paralel atau dikurung
seluruhnya oleh blok kode lain.

Untuk memperlihatkan hasil pengukuran, kita harus menginstalasi komponen aplikasi [CLogRouter]
dengan rute log [CProfileLogRoute]. Hal ini sama seperti yang kita lakukan dengan
message routing biasa. Rute [CProfileLogRoute] akan menampilkan hasil
pengukuran di akhir halaman tersebut.


Mengukur Eksekusi SQL
---------------------------------------

Pengukuran sangat berguna terutama saat bekerja dengan database karena eksekusi SQL
sering menjadi sumber bottleneck kinerja yang utama pada aplikasi. Sementara kita dapat menyisipkan
pernyataan `beginProfile` dan `endProfile` secara manual di tempat yang sesuai guna mengukur
waktu yang diperlukan dalam setiap eksekusi SQL, Yii menyediakan
pendekatan yang lebih sistematis untuk memecahkan masalah ini.

Dengan menyetel [CDbConnection::enableProfiling] menjadi true dalam konfigurasi aplikasi,
setiap pernyataan SQL yang sedang dijalankan akan diukur. Hasilnya bisa ditampilkan menggunakan
[CProfileLogRoute] yang sudah disebutkan sebelumnya, yang dapat memperlihatkan berapa lama waktu yang dibutuhkan
dalam menjalankan pernyataan SQL. Kita juga dapat memanggil [CDbConnection::getStats()] untuk mengambil
jumlah pernyataan SQL dan total waktu eksekusi.


<div class="revision">$Id: topics.logging.txt 2890 2011-01-18 15:58:34Z qiang.xue $</div>