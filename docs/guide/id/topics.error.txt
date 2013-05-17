Penanganan Error
================

Yii menyediakan kerangka kerja penanganan error yang lengkap berdasarkan
pada mekanisme exception PHP 5. Saat aplikasi dibuat untuk menangani permintaan
pengguna yang masuk, ia meregistrasi metode [handleError|CApplication::handleError]
untuk menangani peringatan dan pemberitahuan PHP; dan meregistrasi metode
[handleException|CApplication::handleException] guna menangani exception PHP
yang tidak tertangkap. Konsekuensinya, jika peringatan/pemberitahuan PHP atau exception
yang tidak tertangkap terjadi selama eksekusi aplikasi, salah satu pengendali
error akan mengambil alih kontrol dan memulai prosedur penanganan error
tertentu.

> Tip: Registrasi pengendali error dikerjakan dalam pembentuk aplikasi
dengan memanggil fungsi PHP
[set_exception_handler](http://www.php.net/manual/en/function.set-exception-handler.php)
dan [set_error_handler](http://www.php.net/manual/en/function.set-error-handler.php).
Jika Anda tidak menginginkan Yii menangani error dan exception, Anda dapat mendefinisikan
constant `YII_ENABLE_ERROR_HANDLER` dan `YII_ENABLE_EXCEPTION_HANDLER` menjadi
false dalam [skrip entri](/doc/guide/basics.entry).

Secara default, [handlerError|CApplication::handleError] (atau
[handleException|CApplication::handleException]) akan memunculkan event
[onError|CApplication::onError] (atau event
[onException|CApplication::onException]). Jika error (atau exception)
tidak ditangani oleh pengendali event manapun, ia akan memanggil bantuan dari
komponen aplikasi [errorHandler|CErrorHandler].

Memunculkan Exception
-------------------

Memunculkan exception dalam Yii tidak berbeda dengan memunculkan exception normal PHP.
Anda menggunakan sintaks berikut untuk memunculkan exception bila diperlukan:

~~~
[php]
throw new ExceptionClass('ExceptionMessage');
~~~

Yii mendefinisikan tiga kelas exception: [CException], [CDbException] dan 
[CHttpException]. [CException] adalah kelas exception generik. [CDbException] 
mewakiliki exception yang diakibatkan oleh operasi berhubungan dengan DB.
[CHttpException] merupakan exception yang harus ditampilkan kepada pengguna akhir
dan membawa  sebuah properti [statusCode|CHttpException::statusCode] mewakili status kode HTTP 
Kelas exception menentukan bagaimana ia harus ditampilkan, 
seperti yang akan dijelaskan nanti.

> Tip: Memunculkan exception [CHttpException] adalah cara mudah pelaporan
error yang disebabkan oleh error pengguna mengoperasikan. Sebagai contoh, jika pengguna menyediakan
ID tulisan tidak benar dalam URL, kita dapat melakukan hal berikut untuk menampilkan error 404
(halaman tidak ditemukan):
~~~
[php]
// jika ID tulisan tidak benar
throw new CHttpException(404,'The specified post cannot be found.');
~~~

Menampilkan error
---------------------

Ketika error dioperkan ke komponen aplikasi [CErrorHandler], ia
memilih tampilan yang sesuai untuk menampilkan error. Jika error bertujuan untuk
ditampilkan kepada pengguna akhir, seperti misalnya [CHttpException], ia akan menggunakan
tampilan bernama `errorXXX`, di mana `XXX` adalah kode status HTTP (misalnya
400, 404, 500). Jika error adalah error internal dan seharusnya hanya ditampilkan
kepada pengembang, ia akan menggunakan tampilan bernama `exception`. Jika kasus yang kedua,
informasi jejak panggilan juga baris error akan
ditampilkan.

> Info: Ketika aplikasi berjalan dalam [mode
produksi](/doc/guide/basics.entry#debug-mode), semua error termasuk yang internal
akan ditampilkan menggunakan tampilan `errorXXX`. Ini dikarenakan jejak panggilan
error mungkin berisi informasi yang sensitif. Dalam hal ini,
pengembang harus bergantung pada log error untuk menentukan penyebab error
sebenarnya.

[CErrorHandler] mencari file tampilan terkait untuk sebuah tampilan dengan urutan
sebagai berikut:

   1. `WebRoot/themes/ThemeName/views/system`: ini adalah direktori tampilan `system`
di bawah tema yang aktif saat ini.

   2. `WebRoot/protected/views/system`: ini adalah direktori tampilan `system` di
bawah aplikasi.

   3. `yii/framework/views`: ini adalah direktori tampilan sistem standar yang
disediakan oleh Yii framework.

Oleh karena itu, jika kita ingin mengkustomisasi tampilan error, kita cukup membuat
file tampilan error di bawah direktori tampilan sistem pada aplikasi atau tema Anda.
Setiap file tampilan adalah skrip PHP normal yang berisi kode HTML.
Untuk lebih jelasnya, silahkan merujuk ke file tampilan standar framework di bawah
direktori `view`.


Penanganan Error dengan Penggunaan Action
---------------------------------------------------------------------------------

Yii memungkinkan kita menggunakan sebuah [action dari controller](/doc/guide/basics.controller#action)
untuk menangani tugas penampilan error. Untuk itu, kita harus mengatur error handler(penanganan error)
dalam konfigurasi aplikasi:

~~~
[php]
return array(
	......
	'components'=>array(
		'errorHandler'=>array(
			'errorAction'=>'site/error',
		),
	),
);
~~~

Pada kode di atas, kita mengkonfigurasi properti [CErrorHandler::errorAction] untuk menjadi rute
`site/error` yang merujuk action `error` di `SiteController`. Kita akan menggunakan rute yang berbeda
apabila diperlukan

Kita dapat menulis action `error` seperti berikut:

~~~
[php]
public function actionError()
{
	if($error=Yii::app()->errorHandler->error)
		$this->render('error', $error);
}
~~~

Pada action, kita pertama mengambil informasi error yang detail dari [CErrorHandler::error].
Jika properti tersebut tidak kosong, kita akan menampilkan view `error` bersama dengan informasi error.
Informasi error yang dikembalikan dari [CErrorHandler::error] adalah sebuah larik(array) dengan field-field berupa:

 * `code`: kode status HTTP (misalnya 403, 500);
 * `type`: Jenis error (misalnya [CHttpException], `PHP Error`);
 * `message`: pesan error;
 * `file`: nama file skrip PHP yang mengalami error;
 * `line`: nomor baris yang menyebabkan error;
 * `trace`: call stack dari error tersebut;
 * `source`: kode sumber(source code) konteks di tempat error terjadi..

> Tip: Alasan kita mengecek apakah [CErrorHandler::error] kosong atau tidak dikarenakan
action `error` bisa saja di-request oleh end user secara langsung yang berarti tidak ada error.
Karena kita sedang melemparkan larik(array) `$error` ke view, kita akan memperluas variabel individu
secara otomatis. Sebagai hasilnya, kita dapat mengakses variabel-variabel `$code`, `$type`
secara langsung di view.


Pencatatan Message(Pesan)
----------------

Pesan tingkat `error` akan selalu dicatat bila terjadi kesalahan. Jika error
disebabkan oleh peringatan atau pemberitahuan PHP, pesan akan dicatat dengan
kategori `php`; jika error disebabkan oleh exception tidak tertangkap, kategori
akan menjadi `exception.ExceptionClassName` (untuk [CHttpException]
[statusCode|CHttpException::statusCode] juga akan ditambahkan ke
kategori). Selanjutnya Anda dapat mengeksploitasi fitur [pencatatan](/doc/guide/topics.logging)
untuk memonitor error yang terjadi selama eksekusi aplikasi.

<div class="revision">$Id: topics.error.txt 3374 2011-08-05 23:01:19Z alexander.makarow $</div>
