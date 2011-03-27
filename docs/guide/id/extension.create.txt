Membuat Extension
================

Karena tujuan extension adalah agar dipakai oleh pengembang pihak ketiga, diperlukan
beberapa usaha tambahan untuk membuatnya. Berikut ini adalah beberapa pedoman umum:

* Extension harus berdiri sendiri. Yakni, ketergantungan internalnya harus
  minimal. Ini akan memusingkan bagi para penggunanya jika sebuah extension masih harus
  menginstalasi paket, kelas atau file sumber tambahan.
* File yang dimiliki extension harus diatur pada direktori yang sama di mana
  namanya adalah nama extension.
* Kelas dalam extension harus diawali dengan huruf untuk menghindari konflik
  penamaan dengan kelas dalam extension lainnya.
* Extension harus disertai dengan rincian instalasi dan dokumentasi API.
  Tujuannya untuk mengurangi waktu dan usaha yang diperlukan oleh pengembang lain
  saat mereka menggunakan esktensi.
* Extension harus menggunakan lisensi yang sesuai. Jika Anda ingin menjadikan
  extension Anda dipakai baik oleh proyek open-source dan closed-source,
  Anda dapat menggunakan lisensi seperti BSD, MIT, dll., bukan GPL karena kalau GPL
  mengharuskan kode yang dipakainya juga open-source.

Berikut ini, kita akan melihat bagaimana untuk membuat sebuah extension baru, berdasarkan
pada pengkategorian dalam [tinjauan](/doc/guide/extension.overview).
Deskripsi ini juga berlaku saat Anda membuat komponen, terutama yang dipakai
dalam proyek Anda sendiri.

Komponen Aplikasi
-----------------

[Komponen aplikasi](/doc/guide/basics.application#application-component)
harus mengimplementasikan interface(antar muka) [IApplicationComponent] atau diperluas dari
[CApplicationComponent]. Metode utama yang perlu diimplementasikan ada di
[IApplicationComponent::init] komponen yang melakukan beberapa pekerjaan
inisialisasi. Metode ini dipanggil setelah komponen dibuat dan nilai properti awal
(yang ditetapkan dalam [konfigurasi aplikasi](/doc/guide/basics.application#application-configuration))
diterapkan.

Secara default, komponen aplikasi dibuat dan diinisialisasi hanya saat aplikasi
diakses untuk pertama kali saat penanganan permintaan. Jika komponen aplikasi
perlu segera dibuat begitu setelah instance aplikasi dibuat, maka aplikasinya akan
memerlukan pengguna untuk mendaftar ID-nya di dalam properti [CApplication::preload].


Behavior
--------

Untuk membuat sebuah behavior, kita harus mengimplementasikan interface(antar muka) [IBehavior]. Untuk alasan kemudahan,
Yii menyediakan kelas dasar [CBehavior] yang sudah mengimplementasikan antar muka ini dan
menyediakan tambahan beberapa metode lugas. Kelas-kelas turunan pada umumnya memerlukan implementasi
metode ekstra yang ditujukan agar tersedia bagi komponen yang terlampirkan.

Ketika mengembangkan behavior-behavior untuk [CModel] dan [CActiveRecord], kita juga dapat
memperluas [CModelBehavior] dan [CActiveRecordBehavior]. Kelas-kelas ini menawarkan
fitur tambahan yang khusus dibuat untuk [CModel] dan [CActiveRecord].
Sebagai contoh, kelas [CActiveRecordBehavior] mengimplementasikan satu set metode untuk merespon
event siklus yang terdapat di objek ActiveRecord. Selanjutnya kelas turunan bisa
meng-override metode-metode ini untuk menyimpan kode yang dikustomisasi yang akan berpartisipasi dalam siklus AR.

Kode berikut memperlihatkan contoh behavior ActiveRecord. Ketika dilampirkan
ke objek AR dan ketika objek AR sedang disimpan dengan memanggil `save()`, behavior akan menyetel
secara otomatis atribut `create_time` dan `update_time` dengan cap waktu saat ini.

~~~
[php]
class TimestampBehavior extends CActiveRecordBehavior
{
	public function beforeSave($event)
	{
		if($this->owner->isNewRecord)
			$this->owner->create_time=time();
		else
			$this->owner->update_time=time();
	}
}
~~~


Widget
------

[Widget](/doc/guide/basics.view#widget) harus diturunkan dari [CWidget] atau
anak kelasnya.

Cara termudah pembuatan widget baru adalah dengan menurunkan widget yang sudah ada dan
meng-override metodenya atau mengganti nilai standar propertinya. Sebagai contoh, jika
Anda ingin menggunakan gaya CSS lebih bagus untuk [CTabView], Anda dapat mengkonfigurasi
properti [CTabView::cssFile] saat menggunakan widget. Anda juga dapat memperluas [CTabView]
seperti berikut agar Anda tidak perlu lagi mengkonfigurasi properti saat menggunakan widget.

~~~
[php]
class MyTabView extends CTabView
{
	public function init()
	{
		if($this->cssFile===null)
		{
			$file=dirname(__FILE__).DIRECTORY_SEPARATOR.'tabview.css';
			$this->cssFile=Yii::app()->getAssetManager()->publish($file);
		}
		parent::init();
	}
}
~~~

Dalam contoh di atas, kita menimpa metode [CWidget::init] dan menempatkan
URL [CTabView::cssFile] ke gaya standar CSS baru kita jika properti
belum ditentukan. Kita menempatkan file gaya CSS baru di bawah direktori yang sama
dengan file kelas `MyTabView` agar bisa dipaketkan sebagai sebuah
extension. Karena file gaya CSS tidak bisa diakses oleh Web, kita perlu
mempublikasinya sebagai sebuah asset.

Untuk membuat widget baru dari awal, kita perlu mengimplementasikan dua metode:
[CWidget::init] dan [CWidget::run]. Metode pertama dipanggil saat kita
menggunakan `$this->beginWidget` untuk menyisipkan widget dalam sebuah view(tampilan), dan
metode kedua dipanggil saat kita memanggil `$this->endWidget`.
Jika kita ingin menangkap dan memproses konten yang ditampilkan diantara kedua
pemanggilan metode ini, kita dapat memulai [membufer output](http://us3.php.net/manual/en/book.outcontrol.php)
pada [CWidget::init] dan mengambil output yang di-buffer pada [CWidget::run]
guna pemrosesan selanjutnya.

Widget sering terkait dengan penyertaan CSS, JavaScript atau file sumber lain
dalam halaman yang menggunakan widget. Kita menyebut file-file ini *assets* karena
tempatnya bersama dengan file kelas widget dan biasanya tidak bisa diakses oleh
pengguna Web. Agar file-file ini bisa diakses oleh Web, kita perlu mempublikasikannya
dengan menggunakan [CWebApplication::assetManager], seperti yang ditampilkan dalam snippet kode di atas.
Selain itu, jika kita ingin menyertakan file CSS atau JavaScript dalam halaman saat ini,
kita perlu mendaftarkannya dengan menggunakan [CClientScript]:

~~~
[php]
class MyWidget extends CWidget
{
	protected function registerClientScript()
	{
		// ...terbitkan file CSS atau JavaScript di sini...
		$cs=Yii::app()->clientScript;
		$cs->registerCssFile($cssFile);
		$cs->registerScriptFile($jsFile);
	}
}
~~~

Widget juga dapat memiliki file tampilan sendiri. Jika seperti itu, buat direktori bernama
`views` di bawah direktori yang berisi file kelas widget, dan simpan semua file
tampilan di sana. Dalam kelas widget, untuk me-render tampilan widget, gunakan
`$this->render('NamaTampilan')`, yang mirip dengan apa yang dilakukan dalam sebuah controller.

Aksi
----

[Aksi](/doc/guide/basics.controller#action) harus diperluas dari [CAction]
atau turunan kelasnya. Metode utama yang perlu diimplementasikan untuk sebuah filter
adalah [IAction::run].

Filter
------
[Filter](/doc/guide/basics.controller#filter) harus diturunkan dari [CFilter]
atau anak kelasnya. Metode utama yang perlu diimplementasikan untuk sebuah filter
adalah [CFilter::preFilter] dan [CFilter::postFilter]. Yang pertama dipanggil sebelum
aksi dijalankan sementara yang filter kedua setelahnya.

~~~
[php]
class MyFilter extends CFilter
{
	protected function preFilter($filterChain)
	{
		// logika diterapkan sebelum aksi dijalankan
		return true; // false jika aksi seharusnya tidak dijalankan
	}

	protected function postFilter($filterChain)
	{
		// logika diterapkan setelah aksi dijalankan
	}
}
~~~

Parameter `$filterChain` adalah tipe [CFilterChain] yang berisi informasi
tentang aksi yang saat ini disaring.


Controller
---------
[Controller](/doc/guide/basics.controller) yang didistribusikan sebagai extension
harus diturunkan dari [CExtController], bukan dari [CController]. Alasan utama
karena [CController] menganggap file tampilan controller ditempatkan di bawah
`application.views.ControllerID`, sementara [CExtController] menganggap file tampilan
ditempatkan di bawah direktori `views` yang tidak lain adalah subdirektori
dari direktori yang berisi file kelas controller. Oleh karena itu, lebih mudah
dalam menditribusikan kembali controller karena file tampilan tetap bersama dengan
file kelas controller.


Validator
---------
Validator harus diturunkan dari [CValidator] dan mengimplementasikan metode
[CValidator::validateAttribute].

~~~
[php]
class MyValidator extends CValidator
{
	protected function validateAttribute($model,$attribute)
	{
		$value=$model->$attribute;
		if($value has error)
			$model->addError($attribute,$errorMessage);
	}
}
~~~

Perintah Konsol
---------------
[Perintah konsol](/doc/guide/topics.console) harus diperluas dari
[CConsoleCommand] dan mengimplementasikan metode [CConsoleCommand::run].
Secara opsional, kita dapat menimpa [CConsoleCommand::getHelp] untuk menyediakan
beberapa informasi bantuan menarik mengenai perintah.

~~~
[php]
class MyCommand extends CConsoleCommand
{
	public function run($args)
	{
		// $args berisi array argumen baris perintah untuk perintah ini
	}

	public function getHelp()
	{
		return 'Usage: how to use this command';
	}
}
~~~

Module
-----
Silahkan merujuk ke seksi tentang [module](/doc/guide/basics.module#creating-module) bagaimana membuat sebuah module.

Petunjuk umum untuk mengembangkan module adalah bahwa ia harus berdiri sendiri. File sumber (seperti CSS, JavaScript, gambar) yang dipakai oleh module harus didistribusikan bersamaan dengan module. Dan module harus menerbitkannya agar bisa diakses oleh Web.


Komponen Generik
----------------
Mengembangkan extension komponen generik mirip dengan pembuatan sebuah kelas. Sekali lagi, komponen
juga harus berdiri sendiri agar dapat dipakai dengan mudah oleh pengembang yang lain.


<div class="revision">$Id: extension.create.txt 1423 2009-09-28 01:54:38Z qiang.xue $</div>