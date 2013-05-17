Aplikasi
========

Objek aplikasi mengenkapsulasi konteks eksekusi di dalam request yang diproses. Tugas
utamanya adalah mengumpulkan informasi mengenai request, 
dan mengirimkannya ke controller yang bersangkutan untuk diproses lebih lanjut. Selain itu,
juga sebagai tempat utama menyimpan konfigurasi level aplikasi. Oleh karena
itulah, objek aplikasi disebut juga sebagai `front-controller(controller depan)`.

Aplikasi dibuat sebagai singleton(tunggal) oleh [entry script](/doc/guide/basics.entry).
Singleton aplikasi dapat diakses di mana saja melalui [Yii::app()|YiiBase::app].


Konfigurasi Aplikasi
--------------------

Secara default, objek aplikasi adalah instance dari [CWebApplication]. Untuk
mengkustomisasi, pada umumnya kita membuat sebuah file konfigurasi (atau array) untuk inisialisasi nilai
propertinya saat instance aplikasi dibuat. Cara lainnya
adalah dengan menurunkan [CWebApplication].

Konfigurasinya adalah array pasangan key-value. Setiap kunci mewakili nama
properti instance aplikasi, dan setiap nilai adalah nilai awal dari properti
tersebut. Sebagai contoh, konfigurasi berikut
mengeset aplikasi [name|CApplication::name] dan properti 
[defaultController|CWebApplication::defaultController] aplikasi.

~~~
[php]
array(
	'name'=>'Yii Framework',
	'defaultController'=>'site',
)
~~~

Biasanya kita menyimpan konfigurasi dalam skrip PHP terpisah (misal
`protected/config/main.php`). Di dalam skrip, kita mengembalikan
array konfigurasi sebagai berikut:

~~~
[php]
return array(...);
~~~

Untuk menerapkan konfigurasi, kita mengirim nama file konfigurasi sebagai
sebuah parameter bagi constructor aplikasi, atau ke [Yii::createWebApplication()]
, yang biasanya dikerjakan dalam [skrip  entri](/doc/guide/basics.entry), seperti berikut:

~~~
[php]
$app=Yii::createWebApplication($configFile);
~~~

> Tip: Jika konfigurasi aplikasi sangat kompleks, kita dapat memisahkannya
ke dalam beberapa file, masing-masing mengembalikan bagian array konfigurasi.
Selanjutnya, dalam file konfigurasi utama, kita dapat memanggil PHP `include()` guna
menyertakan file konfigurasi lainnya dan menggabungkannya ke dalam array
konfigurasi yang lengkap.


Direktori Basis Aplikasi
-----------------------

Direktori dasar aplikasi merujuk ke direktori root yang berisi semua
data dan script PHP yang sensitif. Secara default, direktori ini adalah subdirektori
bernama `protected` yang ditempatkan di bawah direktori yang berisi skrip
entri. Direktori ini dapat dikustomisasi melalui konfigurasi properti
[basePath|CWebApplication::basePath] dalam [konfigurasi aplikasi](#application-configuration).

Isi di dalam direktori basis aplikasi harus terlindung dari akses oleh
para pengguna Web. Dengan [Apache HTTP server](http://httpd.apache.org/), 
hal ini bisa dilakukan secara mudah dengan
menempatkan file `.htaccess` di bawah direktori basis. Adapun isi file `.htaccess` adalah sebagai berikut,

~~~
deny from all
~~~

Komponen Aplikasi
-----------------

Fungsionalitas objek aplikasi dapat dikustomisasi secara mudah dan diperkaya dengan 
arsitektur komponennya yang fleksibel. Objek tersebut mengatur satu set komponen
aplikasi, masing-masing mengimplementasi fitur tertentu.
Sebagai contoh, aplikasi menangani request pengguna dengan bantuan komponen [CUrlManager]
dan [CHttpRequest].

Dengan mengkonfigurasi properti [komponen|CApplication::components] aplikasi,
kita bisa mengkustomisasi kelas dan nilai properti setiap komponen
aplikasi yang dipakai dalam sebuah aplikasi. Sebagai contoh, kita dapat
mengkonfigurasi komponen [CMemCache] agar bisa menggunakan beberapa server memcache untuk caching,

~~~
[php]
array(
	......
	'components'=>array(
		......
		'cache'=>array(
			'class'=>'CMemCache',
			'servers'=>array(
				array('host'=>'server1', 'port'=>11211, 'weight'=>60),
				array('host'=>'server2', 'port'=>11211, 'weight'=>40),
			),
		),
	),
)
~~~

Dalam contoh di atas, kita menambahkan elemen `cache` pada array `components`. Elemen
`cache` menyatakan bahwa kelas komponennya adalah
`CMemCache` dan properti `servers` juga harus diinisialisasi.

Untuk mengakses komponen aplikasi, gunakan `Yii::app()->ComponentID`, dengan
`ComponentID` merujuk pada ID komponen (contoh `Yii::app()->cache`).

Komponen aplikasi dapat dinonaktifkan dengan menyetel `enabled` menjadi false
dalam konfigurasinya. Null dikembalikan saat kita mengakses komponen yang telah dinonaktifkan.

> Tip: Secara default, komponen aplikasi dibuat bila diperlukan. Ini berarti
komponen aplikasi mungkin tidak dibuat sama sekali jika tidak diakses
saat pengguna melakukan request. Hasilnya, performa aplikasi keseluruhan tidak akan menurun
walaupun dikonfigurasi dengan banyak komponen. Beberapa komponen
aplikasi (contoh [CLogRouter]) mungkin perlu dibuat tidak peduli apakah 
diakses atau tidak. Untuk melakukannya, daftarkan ID masing-masing dalam properti [preload|CApplication::preload]
aplikasi.

Komponen Aplikasi Inti
----------------------

Yii sudah mendefinisikan satu set komponen aplikasi inti guna menyediakan fitur
yang umum dalam aplikasi Web. Sebagai contoh, komponen
[request|CWebApplication::request] dipakai untuk mengumpulkan request pengguna
dan menyediakan informasi seperti URL yang di-request, cookies. Dengan mengkonfigurasi properti
komponen inti ini, kita dapat mengubah hampir segala aspek
perilaku standar Yii.

Berikut daftar komponen inti yang dideklarasikan oleh
[CWebApplication].

   - [assetManager|CWebApplication::assetManager]: [CAssetManager] -
mengatur publikasi file asset privat.

   - [authManager|CWebApplication::authManager]: [CAuthManager] - mengatur role-based access control (RBAC).

   - [cache|CApplication::cache]: [CCache] - menyediakan fungsionalitas
caching data. Catatan, Anda harus menetapkan kelas sebenarnya (misal
[CMemCache], [CDbCache]). Jika tidak, null akan dikembalikan saat Anda
mengakses komponen ini.

   - [clientScript|CWebApplication::clientScript]: [CClientScript] -
mengatur skrip klien (javascript dan CSS).

   - [coreMessages|CApplication::coreMessages]: [CPhpMessageSource] -
menyediakan terjemahan pesan inti yang dipakai oleh Yii framework.

   - [db|CApplication::db]: [CDbConnection] - menyediakan koneksi database.
Catatan, Anda harus mengkonfigurasi properti
[connectionString|CDbConnection::connectionString] untuk menggunakan
komponen ini.

   - [errorHandler|CApplication::errorHandler]: [CErrorHandler] - menangani
exception dan kesalahan PHP yang tidak tercakup.

   - [format|CApplication::format]: [CFormatter] - memformat nilai data untuk
sisi tampilan.

   - [messages|CApplication::messages]: [CPhpMessageSource] - menyediakan 
terjemahan pesan yang dipakai oleh aplikasi Yii.

   - [request|CWebApplication::request]: [CHttpRequest] - menyediakan 
informasi terkait dengan request penggguna.

   - [securityManager|CApplication::securityManager]: [CSecurityManager] -
menyediakan layanan terkait keamanan, seperti hashing dan enkripsi.

   - [session|CWebApplication::session]: [CHttpSession] - menyediakan
fungsionalitas terkait sesi.

   - [statePersister|CApplication::statePersister]: [CStatePersister] -
menyediakan metode persisten kondisi global(global state persistence).

   - [urlManager|CWebApplication::urlManager]: [CUrlManager] - menyediakan
fungsionalitas penguraian dan pembuatan URL.

   - [user|CWebApplication::user]: [CWebUser] - mewakili informasi identitas
pengguna saat ini.

   - [themeManager|CWebApplication::themeManager]: [CThemeManager] - mengatur tema.


Siklus Aplikasi
---------------

Ketika menangani request pengguna, aplikasi akan berada dalam siklus masa hidup
sebagai berikut:

   0. Pra-inisialisasi aplikasi dengan [CApplication::preinit()];

   1. Menyiapkan kelas autoloader dan penanganan kesalahan;

   2. Meregistrasi komponen inti aplikasi;

   3. Mengambil konfigurasi aplikasi;

   4. Menginisialisasi aplikasi dengan [CApplication::init()]
	   - Registrasi behavior aplikasi
	   - Mengambil komponen aplikasi statis;

   5. Menghidupkan event [onBeginRequest|CApplication::onBeginRequest];

   6. Mengolah request pengguna:
	   - Mengumpulkan request pengguna;
	   - Membuat controller;
	   - Menjalankan controller;

   7. Menghidupkan event [onEndRequest|CApplication::onEndRequest];

<div class="revision">$Id: basics.application.txt 3251 2011-06-01 00:24:06Z qiang.xue $</div>