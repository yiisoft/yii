Keamanan
========

Pecegahan Cross Site Scripting
----------------------------------------
Cross-site scripting atau penaskahan silang antar situs(juga dikenal sebagai XSS) terjadi saat aplikasi web
mengumpulkan data berbahaya dari pengguna. Penyerang sering akan menginjeksi JavaScript,
VBScript, ActiveX, HTML, atau Flash ke dalam aplikasi yang mudah diserang guna menipu
pengguna aplikasi lain dan mengumpulkan data darinya. Sebagai contoh, sistem forum yang
didesain asal mungkin menampilkan input pengguna dalam tulisan forum tanpa pemeriksaan apapun.
Penyerang bisa menginjeksi bagian kode JavaScript berbahaya ke dalam tulisan
dengan demikian ketika pengguna lain membaca tulisan ini, JavaScript berjalan tanpa terduga
pada komputernya.

Salah satu langkah penting untuk mencegah serangan XSS adalah memeriksa input pengguna
sebelum menampilkannya. Orang bisa melakukan enkode-HTML dengan input pengguna untuk
melaksanakan tujuan ini. Akan tetapi, dalam beberapa situasi, enkode-HTML mungkin tidak disukai
karena mematikan semua tag HTML.

Yii menyertakan produk [HTMLPurifier](http://htmlpurifier.org/)
dan menyediakan komponen berguna bagi pengembang bernama [CHtmlPurifier]
yang melapisi [HTMLPurifier](http://htmlpurifier.org/). Komponen ini 
mampu menghapus semua kode berbahaya dengan audit mendalam, aman namun memiliki list yang diperbolehkan
dan memastikan konten yang disaring sesuai standar.

Komponen [CHtmlPurifier] dapat dipakai baik sebagai [widget](/doc/guide/basics.view#widget)
ataupun [filter](/doc/guide/basics.controller#filter). Ketika dipakai sebagai widget,
[CHtmlPurifier] akan memurnikan konten yang ditampilkan dalam badan tampilannya. Sebagai contoh,

~~~
[php]
<?php $this->beginWidget('CHtmlPurifier'); ?>
...display user-entered content here...
<?php $this->endWidget(); ?>
~~~


Pencegahan Pemalsuan Permintaan Situs-silang
-------------------------------------------

Serangan Cross-Site Request Forgery (CSRF) atau Penjagaan Pemalsuan Permintaan Situs-silang terjadi saat
situs web berbahaya menyebabkan browser web pengguna untuk melakukan aksi
yang tidak diinginkan pada situs aman. Sebagai contoh, situs web
berbahaya mempunyai halaman yang berisi gambar di mana tag  `src` merujuk 
ka situs bank: `http://bank.example/withdraw?transfer=10000&to=seseorang`.
Jika pengguna yang memiliki cookie login untuk situs perbankan mengunjungi
halaman berbahaya ini, aksi pentransferan 10000 dollar
kepada seseorang akan dijalankan. Terbalik dengan situs-silang
yang mengeksploitasi kepercayaan yang dimiliki pengguna pada situs tertentu,
CSRF mengekploitasi kepercayaan yang dimiliki situs untuk pengguna tertentu.

Untuk mencegah serangan CSRF, penting untuk mematuhi aturan bahwa
permintaan `GET` hanya diijinkan untuk mengambil data alih-alih mengubah
data pada server. Dan untuk permintaan `POST`, harus menyertakan
nilai acak yang dapat dikenal oleh server guna memastikan
form dan hasil yang dikirimkan dan hasil yang dikembalikan ke
sumber yang sama.

Yii mengimplementasikan skema penjagaan CSRF untuk membantu mengalahkan serangan berbasis-`POST`.
Ia didasarkan pada penyimpanan nilai acak di dalam cookie dan membandingkan nilai ini
dengan nilai yang dikirimkan via permintaan `POST`.

Secara default, penjagaan CSRF dimatikan. Untuk menghidupkannya, konfigurasi
komponen aplikasi [CHttpRequest] dalam
[konfigurasi aplikasi](/doc/guide/basics.application#application-configuration)
seperti berikut,

~~~
[php]
return array(
	'components'=>array(
		'request'=>array(
			'enableCsrfValidation'=>true,
		),
	),
);
~~~

Dan untuk menampilkan form, panggil [CHtml::form] daripada menulis tag form HTML secara langsung
Metode [CHtml::form] akan menyertakan nilai acak tertentu
dalam field tersembunyi agar bisa dikirimkan untuk validasi CSRF.


Pencegahan Serangan Cookie
-------------------------
Melindungi cookie dari serangan adalah hal yang sangat penting, karena ID sesi
umumnya disimpan dalam cookie. Jika seseorang memegang ID sesi, pada dasarnya
dia memiliki semua informasi sesi relevan.

Ada beberapa langkah pencegahan guna menghadapi serangan terhadap cookie.

* Aplikasi bisa menggunakan SSL untuk membuat saluran komunikasi aman dan
  hanya mengoper cookie terotentikasi melalui koneksi HTTPS. Penyerang
  tidak bisa men-decipher isi dalam cookie yang ditransfer.
* Habiskan masa hidup sesi dengan benar, termasuk semua cookie dan token sesi
  untuk mengurangi serangan.
* Cegah cross site-scripting  yang menyebabkan kode berbahaya dijalankan dalam
  browser pengguna dan mengekspos cookie yang dimilikinya.
* Validasi data cookie dan deteksi apakah isinya diubah.

Yii mengimplementasikan skema validasi cookie yang mencegah serangan perubahan terhadap
cookie. Sebenarnya, ia melakukan pemeriksaan HMAC atas nilai cookie jika validasi cookie
dihidupkan.

Validasi cookie secara default dimatikan. Untuk menghidupkannya, konfigurasi komponen
aplikasi [CHttpRequest] dalam
[konfigurasi aplikasi](/doc/guide/basics.application#application-configuration)
seperti berikut,

~~~
[php]
return array(
	'components'=>array(
		'request'=>array(
			'enableCookieValidation'=>true,
		),
	),
);
~~~

Untuk menggunakan skema validasi cookie yang disediakan oleh Yii, kita juga harus
mengakses cookie melalui koleksi [cookies|CHttpRequest::cookies] collection, daripada
melalui `$_COOKIES` secara langsung:

~~~
[php]
// ambil cookie dengan nama yang ditetapkan
$cookie=Yii::app()->request->cookies[$name];
$value=$cookie->value;
......
// kirim cookie
$cookie=new CHttpCookie($name,$value);
Yii::app()->request->cookies[$name]=$cookie;
~~~


<div class="revision">$Id: topics.security.txt 2535 2010-10-11 08:28:08Z mdomba $</div>