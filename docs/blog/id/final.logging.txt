Pencatatan Error
==============

Sebuah aplikasi Web yangjadi sering membutuhkan pencatatan yang lengkap untuk berbagai event. Dalam aplikasi blog kita, kita ingin mencatata error-error yang terjadi ketika sedang digunakan. Error ini dapat berupa kesalahan pemograman atau penyalahgunaan sistem. Dengan mencatat sistem akan membantu kita untuk mengimprovisasi aplikasi blog.

Kita mengatifkan pencatatan dengan memngubah [konfigurasi aplikasi](http://www.yiiframework.com/doc/guide/basics.application#application-configuration) sebagai berikut,

~~~
[php]
return array(
	'preload'=>array('log'),

	......

	'components'=>array(
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
			),
		),
		......
	),
);
~~~

Dengan konfigurasi di atas, jika sebuah error atau warning terjadi, informasi detail akan dicatat dan disimpan di dalam sebuah file yang terletak di bawah direktori `/wwwroot/blog/protected/runtime`.

Komponen `log` menyediakan fitur maju lainnya, seperti mengirim pesan log ke daftar alamat email, menampilkan pesan log di dalam console JavaScript


<div class="revision">$Id: final.logging.txt 878 2009-03-23 15:31:21Z qiang.xue $</div>