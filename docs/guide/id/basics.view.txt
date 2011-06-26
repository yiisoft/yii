View
========

View (tampilan) adalah skrip PHP yang berisi terutama elemen antar muka pengguna. View
bisa saja berisi code PHP, tetapi direkomendasikan code ini
tidak mengubah model data dan harus tetap relatif sederhana. Guna mempertahankan semangat
pemisahan logika dan penampilan, bagian besar dari logika harus ditempatkan
dalam controller ataupun model alih-alih view.

View  memiliki nama yang dipakai untuk mengidentifikasi file skrip tampilan
saat me-render. Nama view sama seperti nama file skrip view-nya.
Sebagai contoh, file view `edit` merujuk pada file skrip bernama
`edit.php`. Untuk me-render view, pangil [CController::render()] dengan
nama view. Metode tersebut akan mencari file view terkait di dalam direktori
`protected/views/ControllerID`.

Di dalam skrip view, kita dapat mengakses instance controller menggunakan
`$this`. Selanjutnya kita bisa `menarik` setiap properti controller dengan
mengevaluasi `$this->propertyName` dalam tampilan.

Kita juga bisa menggunakan pendekatan `dorong` berikut guna mengoper data ke tampilan:

~~~
[php]
$this->render('edit', array(
	'var1'=>$value1,
	'var2'=>$value2,
));
~~~

Dalam contoh di atas, metode [render()|CController::render] akan mengurai parameter array 
kedua ke dalam variabel. Hasilnya, dalam skrip view kita dapat mengakses
variabel lokal `$var1` dan `$var2`.

Layout
----------

Layout(tata letak) adalah view khusus yang dipakai untuk mendekorasi tampilan. Biasanya
berisi bagian antar muka pengguna yang umum diantara beberapa view.
Sebagai contoh, sebuah layout mungkin berisi sebuah header dan sebuah footer serta menyertakan
tampilan konten diantaranya,

~~~
[php]
......header di sini......
<?php echo $content; ?>
......footer di sini......
~~~

dengan `$content` menyimpan hasil render view konten.

Layout secara implisit diterapkan saat memanggil [render()|CController::render].
Secara default, skrip view `protected/views/layouts/main.php` dipakai sebagai
layout. Kita dapat mengubahnya dengan mengubah baik [CWebApplication::layout]
ataupun [CController::layout]. Sedangkan, untuk membuat tampilan tanpa menerapkan layout apapun,
panggil [renderPartial()|CController::renderPartial].

Widget
------

Widget adalah istance dari [CWidget] atau anak kelas dari [CWidget]. Komponen yang
terutama ditujukan guna keperluan penampilan. Widget biasanya disertakan dalam skrip
view untuk menghasilkan beberapa antar muka pengguna yang kompleks dan berdiri sendiri. Sebagai
contoh, widget kalender bisa dipakai untuk menyiapkan antar muka kalender pengguna yang 
kompleks. Widget memungkinkan reusability (pemakaian kembali) yang 
lebih baik dalam antar muka pengguna.

Untuk menggunakan widget, lakukan seperti berikut dalam skrip view:

~~~
[php]
<?php $this->beginWidget('path.to.WidgetClass'); ?>
...konten body yang dapat ditangkap oleh widget...
<?php $this->endWidget(); ?>
~~~

atau

~~~
[php]
<?php $this->widget('path.to.WidgetClass'); ?>
~~~

Contoh kedua dipakai saat widget tidak memerlukan konten body apapun.

Widget dapat dikonfigurasi untuk mengkustomisasi perilakunya. Ini dilakukan dengan
menyetel nilai properti awalnya ketika memanggil
[CBaseController::beginWidget] atau [CBaseController::widget]. Sebagai contoh,
ketika menggunakan widget [CMaskedTextField], kita ingin menetapkan pelapisan
agar dipakai. Kita dapat melakukannya dengan mengoper array nilai awal properti
itu sebagai berikut, di mana kunci array adalah nama properti dan nilai array
adalah nilai awal pada properti widget terkait:

~~~
[php]
<?php
$this->widget('CMaskedTextField',array(
	'mask'=>'99/99/9999'
));
?>
~~~

Untuk mendefinisikan widget baru, turunkan [CWidget] dan override metode
[init()|CWidget::init] dan [run()|CWidget::run]:

~~~
[php]
class MyWidget extends CWidget
{
	public function init()
	{
		// metode ini dipanggil oleh CController::beginWidget()
	}

	public function run()
	{
		// metode ini dipanggil oleh CController::endWidget()
	}
}
~~~

Sama seperti halnya dengan controller, widget juga bisa memiliki tampilan sendiri. Standarnya, file
tampilan widget ditempatkan di bawah subdirektori `views` pada direktori
yang berisi file kelas widget. View ini dapat di-render dengan memanggil
[CWidget::render()], mirip dengan pemanggilan controller. Perbedaannya adalah
tidak ada tata letak(layout) yang akan diterapkan pada tampilan widget. Selain itu,
`$this` dalam view juga merujuk ke instance widget alih-alih instance controller.


View Sistem
---------------

View(tampilan) sistem merujuk pada tampilan yang dipakai oleh Yii untuk menampilkan kesalahan dan pencatatan(logging)
informasi. Sebagai contoh, ketika permintaan pengguna untuk controller atau aksi 
yang tidak ada, Yii akan memunculkan exception yang menjelaskan kesalahan. Yii menampilkan
exception menggunakan tampilan sistem tertentu.

Penamaan view sistem mengikuti beberapa aturan. Nama seperti `errorXXX` merujuk pada
tampilan untuk menampilkan [CHttpException] dengan kode kesalahan `XXX`. Sebagai contoh, jika [CHttpException] dimunculkan dengan kode kesalahan 404, view `error404`
akan ditampilkan.

Yii menyediakan satu set tampilan sistem default yang ditempatkan di bawah
`framework/views`. View-view ini bisa dikustomisasi dengan membuat file tampilan 
yang sama dengan nama yang sama di bawah `protected/views/system`.

<div class="revision">$Id: basics.view.txt 3251 2011-06-01 00:24:06Z qiang.xue $</div>
