Pengembangan ke Depan
===================

Menggunakan Sebuah Theme
-------------

Tanpa menulis code apapun, aplikasi blog kita sudah [dapat di-theme-kan](http://www.yiiframework.com/doc/guide/topics.theming). Untuk menggunakan theme, kita cukup mengembangkan theme dengan menulis file view yang terkustomisasi. Misalnya, untuk menggunakan theme bernama `classic` yang menggunakan layout page yang berbeda, kita akan membuat sebuah file view layout `/wwwroot/blog/themes/classic/views/layouts/main.php`. Kita juga akan mengubah konfigurasi aplikasi untuk mengindikasikan pilihan kita untuk theme `classic`:
Without writing any code, our blog application is already [themeable](http://www.yiiframework.com/doc/guide/topics.theming). To use a theme, we mainly need to develop the theme by writing customized view files in the theme. For example, to use a theme named `classic` that uses a different page layout, we would create a layout view file `/wwwroot/blog/themes/classic/views/layouts/main.php`. We also need to change the application configuration to indicate our choice of the `classic` theme:

~~~
[php]
return array(
	......
	'theme'=>'classic',
	......
);
~~~


Internasionalisasi
--------------------

Kita juga bisa menginternasionalisasi aplikasi blog kita supaya halamannya dapat ditampilkan dalam berbagai bahasa. Untuk mencapainya kita harus melibatkan dua aspek.

Pertama, kita bisa membuat file view dalam berbagai bahasa. Misalnya, untuk halaman `index` dari `PostController`, kita dapat membuat sebuah file view `/wwwroot/blog/protected/views/post/zh_cn/index.php`. Ketika aplikasi dikonfigrasi untuk menggunakan Chinese sederhana (kode bahasanya `zh_cn`), Yii akan secara otomatis menggunakan file view baru alih-alih bahasa asal.

Kedua, kita dapat membuat message translation untuk pesan-pesan yang di-generate oleh code. Terjemahan message harus disimpan sebagai file di bawah direktori `/wwwroot/blog/protected/messages`. Kita juga dapat memodifikasikan code di mana kita menggunakan string teks dengan membungkusnya dengan method `Yii::t()`.

Untuk informasi lebih lanjut mengenai internasionalisasi, silahkan merujuk ke [Guide](http://www.yiiframework.com/doc/guide/topics.i18n).


Meningkatkan Performa dengan Cache
--------------------------------

Walau framework Yii sendiri [sangat efisien](http://www.yiiframework.com/performance/), aplikasi yang ditulis dalam Yii belum tentu efisien. Ada beberapa tempat di aplikasi blog kita yang dapat diimprovisasi performanya. Misalnya, tag cloud portlet bisa menjadi slah satu dari performance bottlenect dikarenakan melibatkan query database dan logika PHP yang kompleks.

Kita dapat membuat penggunakan [fitur caching](http://www.yiiframework.com/doc/guide/caching.overview) yang disediakan Yii untuk meningkatkan performa. Salah satu component yang sangat berguna di Yii adalah [COutputCache], yang akan men-cache sebuah fragmen dari halaman yang ditampilkan sehingga code yang bertugas mengenerate fragmen bersangkutan tidak perlu dijalankan setiap kali request. Misalnya di file layout `/wwwroot/blog/protected/views/layout/column2.php`, kita dapat membungkus tag cloud portlet dengan [COutputCache]:

~~~
[php]
<?php if($this->beginCache('tagCloud', array('duration'=>3600))) { ?>

	<?php $this->widget('TagCloud', array(
		'maxTags'=>Yii::app()->params['tagCloudCount'],
	)); ?>

<?php $this->endCache(); } ?>
~~~

Dengan kode di atas, tampilan tag cloud akan ditampilkan dari cache alih-alih di-generate setiap kali request. Isi cache akan tetap valid selama 3600 detik.


Menambah Fitur Baru
-------------------

Aplikasi blog kita masih hanya memiliki fungsi dasar. Untuk menjadi sebuah sistem blog yang lengkap, masih diperlukan berbagai fitur, misalnya calendar portlet, email notification, pengkategorian post, archieved post portlet, dan sebagainya. Kita akan membiarkan implementasi fitur ini kepada pembaca yang tertarik.

<div class="revision">$Id: final.future.txt 2017 2010-04-05 17:12:13Z alexander.makarow $</div>