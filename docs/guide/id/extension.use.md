Menggunakan Extension
====================

Menggunakan extension biasanya berkaitan dengan tiga langkah berikut:

  1. Download extension dari
     [repositori extension](http://www.yiiframework.com/extensions/) Yii.
  2. Urai extension di bawah subdirektori `extensions/xyz` pada
     [direktori basis aplikasi](/doc/guide/basics.application#application-base-directory),
     di mana `xyz` adalah nama extension.
  3. Impor, konfigurasi dan gunakan extension.

Setiap extension memiliki nama yang secara identitas unik diantara semua extension.
Extension diberi nama `xyz`, kita dapat menggunakan alias path `ext.xyz` untuk 
menempatkannya pada basis direktori yang berisi semua file `xyz`.

Extension yang berbeda memiliki persyaratan mengenai pengimporan,
konfigurasi dan pemakaian. Selanjutnya, kita meringkas skenario pemakaian umum
mengenai extension, berdasarkan pada kategorisasinya seperti dijelaskan dalam
[tinjauan](/doc/guide/extension.overview).


Extension Zii
--------------

Sebelum kita mulai melihat penggunaan extension pihak ketiga, kami akan memperkenalkan
pustaka extension Zii, yang merupakan kumpulan extension yang dikembangkan oleh tim developer Yii
dan disertakan dalam setiap rilis.

Ketika menggunakan sebuah ektensi Zii, kita harus merujuk ke kelas bersangkutan dengan menggunakan
alias path dalam bentuk `zii.path.ke.NamaKelas`. Di sini akar alias `zii` ditentukan oleh Yii. Dia akan dirujukkan
ke direktori pustaka Zii. Misalnya, untuk menggunakan [CGridView], kita akan menggunakan
kode berikut dalam skrip view ketika merujuk ke extension:

~~~
[php]
$this->widget('zii.widgets.grid.CGridView', array(
	'dataProvider'=>$dataProvider,
));
~~~


Komponen Aplikasi
-----------------

Untuk menggunakan [komponen aplikasi](/doc/guide/basics.application#application-component),
kita perlu mengubah [konfigurasi aplikasi](/doc/guide/basics.application#application-configuration)
lebih dulu dengan menambahkan entri baru pada properti `components`, seperti berikut:

~~~
[php]
return array(
    // 'preload'=>array('xyz',...),
    'components'=>array(
        'xyz'=>array(
            'class'=>'ext.xyz.XyzClass',
            'property1'=>'value1',
            'property2'=>'value2',
        ),
        // konfigurasi komponen lainnya
    ),
);
~~~

Selanjutnya, kita dapat mengakses komponen di mana saja mengunakan `Yii::app()->xyz`. Komponen
ini akan dibuat secara lazy (yakni, dibuat saat diakses untuk pertama kali)
kecuali kita mendaftar properti `preload`.


Behavior
--------

[Behavior](/doc/guide/basics.component#component-behavior) bisa dipakai dalam semua komponen.
Pemakaiannya mencakup dua langkah. Dalam langkah pertama, behavior dilampirkan ke sasaran komponen.
Dalam langkah kedua, metode behavior dipanggil melalui sasaran komponen. Sebagai contoh:

~~~
[php]
// $name secara unik mengidentifikasi behavior dalam komponen
$component->attachBehavior($name,$behavior);
// test() adalah metode $behavior
$component->test();
~~~

Seringkali sebuah behavior dilampirkan ke komponen menggunakan cara konfiguratif alih-alih
memanggil metode `attachBehavior`. Sebagai contoh, untuk melampirkan behavior ke sebuah
[komponen aplikasi](/doc/guide/basics.application#application-component), kita dapat
menggunakan
[konfigurasi aplikasi](/doc/guide/basics.application#application-configuration) berikut:

~~~
[php]
return array(
	'components'=>array(
		'db'=>array(
			'class'=>'CDbConnection',
			'behaviors'=>array(
				'xyz'=>array(
					'class'=>'ext.xyz.XyzBehavior',
					'property1'=>'value1',
					'property2'=>'value2',
				),
			),
		),
		//....
	),
);
~~~

Kode di atas melampirkan behavior `xyz` ke komponen aplikasi `db`. Kita dapat melakukannya
karena [CApplicationComponent] mendefinisikan properti bernama `behaviors`. Dengan menyetel properti ini
dengan sebuah daftar konfigurasi behavior, komponen akan melampirkan behavior terkait
saat ia diinisialisasi.

Untuk kelas [CController], [CFormModel] dan [CActiveModel] yang biasanya harus diturunkan,
melampirkan behaviors dikerjakan dengan menimpa metode `behaviors()`. Kelas-kelas tersebut akan
terpasang secara otomatis behavior-behavior yang dideklarasi dalam metode ini ketika inisialisasi. Sebagai contoh,

~~~
[php]
public function behaviors()
{
	return array(
		'xyz'=>array(
			'class'=>'ext.xyz.XyzBehavior',
			'property1'=>'value1',
			'property2'=>'value2',
		),
	);
}
~~~


Widget
------

[Widget](/doc/guide/basics.view#widget) dipakai terutama dalam [tampilan](/doc/guide/basics.view).
Kelas widget yang diberiktan `XyzClass` dimiliki oleh extension `xyz`, kita bisa menggunakannya dalam
sebuah tampilan seperti berikut,

~~~
[php]
// widget yang tidak memerlukan konten body
<?php $this->widget('application.extensions.xyz.XyzClass', array(
    'property1'=>'value1',
    'property2'=>'value2')); ?>

// widget yang dapat berisi konten body
<?php $this->beginWidget('application.extensions.xyz.XyzClass', array(
    'property1'=>'value1',
    'property2'=>'value2')); ?>

...konten body widget...

<?php $this->endWidget(); ?>
~~~

Action (Aksi)
----

[Action](/doc/guide/basics.controller#action) dipakai oleh [controller](/doc/guide/basics.controller)
untuk merespon permintaan spesifik pengguna. Kelas aksi `XyzClass` dimiliki oleh extension
`xyz`, kita dapat menggunakannya dengan meng-override metode [CController::actions] dalam
kelas controller  kita:

~~~
[php]
class TestController extends CController
{
	public function actions()
	{
		return array(
			'xyz'=>array(
				'class'=>'application.extensions.xyz.XyzClass',
				'property1'=>'value1',
				'property2'=>'value2',
			),
			// aksi lainnya
		);
	}
}
~~~

Selanjutnya, aksi dapat diakses via [rute](/doc/guide/basics.controller#route)
`test/xyz`.

Filter
------
[Filter](/doc/guide/basics.controller#filter) juga dipakai oleh [controller](/doc/guide/basics.controller).
Terutama pre- dan post-process permintaan pengguna saat ditangani oleh sebuah
[aksi](/doc/guide/basics.controller#action).
Kelas filter `XyzClass` dimiliki oleh
extension `xyz`, kita dapat menggunakannya dengan meng-override metode [CController::filters]
dalam file controller kita:

~~~
[php]
class TestController extends CController
{
	public function filters()
	{
		return array(
			array(
				'application.extensions.xyz.XyzClass',
				'property1'=>'value1',
				'property2'=>'value2',
			),
			// filter lainnya
		);
	}
}
~~~

Dalam contoh di atas, kita dapat menggunakan operator plus dan minus dalam elemen pertama array
untuk menerapkan filter ke action dalam jumlah terbatas saja. Untuk lebih jelasnya, silahkan merujuk ke
dokumentasi [CController].

Controller
----------
[Controller](/doc/guide/basics.controller) menyediakan satu set action yang dapat di-request
oleh pengguna. Untuk menggunakan extension controller, kita perlu mengkonfigurasi
properti [CWebApplication::controllerMap] dalam
[konfigurasi aplikasi](/doc/guide/basics.application#application-configuration):

~~~
[php]
return array(
	'controllerMap'=>array(
		'xyz'=>array(
			'class'=>'application.extensions.xyz.XyzClass',
			'property1'=>'value1',
			'property2'=>'value2',
		),
		// controller lainnya
	),
);
~~~

Kemudian, action `a` dalam controller dapat diakses via
[rute](/doc/guide/basics.controller#route) `xyz/a`.

Validator
---------
Validator dipakai terutama dalam kelas [model](/doc/guide/basics.model)
(salah satu yang diperluas baik dari [CFormModel] ataupun [CActiveRecord]).
Kelas validator `XyzClass` dimiliki oleh
extension `xyz`, kita bisa menggunakannya dengan menimpa metode [CModel::rules]
dalam kelas model kita:

~~~
[php]
class MyModel extends CActiveRecord // atau CFormModel
{
	public function rules()
	{
		return array(
			array(
				'attr1, attr2',
				'application.extensions.xyz.XyzClass',
				'property1'=>'value1',
				'property2'=>'value2',
			),
			// aturan validasi lainnya
		);
	}
}
~~~

Perintah Konsol
---------------
Extension [perintah konsol](/doc/guide/topics.console) biasanya meningkatkan
piranti `yiic` dengan perintah tambahan. Perintah konsol
`XyzClass` dimiliki oleh extension `xyz`, kita bisa menggunakannya dengan mengatur
file konfigurasi untuk aplikasi konsol:

~~~
[php]
return array(
	'commandMap'=>array(
		'xyz'=>array(
			'class'=>'application.extensions.xyz.XyzClass',
			'property1'=>'value1',
			'property2'=>'value2',
		),
		// perintah lainnya
	),
);
~~~

Selanjutnya, kita dapat menggunakan piranti `yiic` yang disertai dengan perintah
tambahan `xyz`.

> Note|Catatan: Aplikasi konsol biasanya menggunakan file konfigurasi
yang berbeda dari yang dipakai oleh aplikasi Web. Jika aplikasi dibuat
menggunakan perintah `yiic webapp`, maka file konfigurasi untuk aplikasi
konsol `protected/yiic` adalah `protected/config/console.php`,
sementara file konfigurasi untuk aplikasi Web adalah `protected/config/main.php`.


Module
-----
Silahkan merujuk ke seksi mengenai [module](/doc/guide/basics.module#using-module) bagaimana menggunakan module.


Komponen Generik
----------------
Untuk menggunakan [komponen](/doc/guide/basics.component) generik, pertama
kita perlu menyertakan file kelasnya dengan menggunakan

~~~
Yii::import('ext.xyz.XyzClass');
~~~

Selanjutnya, kita dapat membuat turunan kelas, mengkonfigurasi propertinya,
dan memanggi metodenya. Kita juga bisa menurunkannya untuk membuat anak kelas baru.


<div class="revision">$Id: extension.use.txt 2890 2011-01-18 15:58:34Z qiang.xue $</div>