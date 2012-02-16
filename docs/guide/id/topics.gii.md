Penghasil Kode Otomatis
=========================

Mulai dari versi 1.1.2, Yii sudah dilengkapi dengan sebuah code generator berbasis Web yang disebut *Gii*. Tool ini menggantikan tool sebelumnya yakni `yiic shell` yang berjalan di command line. Di bagian ini, kita akan melihat bagaimana menggunakan Gii dan cara meningkatkan(mengembangkan) kemampuan Gii untuk mendongkrak produktivitas pengembangan.

Penggunaan Gii
---------

Gii diimplementasi sebagai module dan harus digunakan di dalam aplikasi Yii. Untuk menggunakan Gii, kita pertama-tama memodifikasi konfigurasi aplikasi sebagai berikut:

~~~
[php]
return array(
	......
	'modules'=>array(
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>'masukkan password di sini',
			// 'ipFilters'=>array(...a list of IPs...),
			// 'newFileMode'=>0666,
			// 'newDirMode'=>0777,
		),
	),
);
~~~

Pada kode di atas, kita mendeklarasi sebuah module bernama `gii` yang merupakan instance dari kelas [GiiModule]. Kita juga menentukan sebuah password untuk module yang akan ditanyakan ketika mengakses Gii.

Secara default, Gii diatur supaya hanya dapat diakses di localhost saja demi alasan keamanan. Jika kita ingin membuatnya dapat diakses di komputer lain yang sudah dipercaya, kita dapat mengaturnya di properti [Gii::ipFilters] seperti yang ditunjukkan di kode di atas.

Karena Gii bisa dihasilkan dan menyimpan file kode baru di dalam aplikasi yang sudah ada, kita harus memastikan bahwa proses Web Server memiliki pengaturan izin(permission) yang benar. Properti [Gii:Module::newFileMode] dan [GiiModule::newDirMode] mengatur bagaimana file dan direktori baru harus dihasilkan.

> Note|Catatan: Gii disediakan hanya untuk sebagai tool untuk dalam tahap pengembangan. Oleh karena itu, harap diinstal di komputer tempat pengembangan(development). Dikarenakan Gii bisa menghasilkan file PHP baru di dalam aplikasi, kita harus lebih memperhatikan secara khusus mengenai penanganan masalah keamanan (misalnya password, IP Filter).

Kita dapat menggunakan Gii dengan URL `http://hostname/path/to/index.php?r=gii`. Di sini kita mengasumsi `http://hostname/path/to/index.php` merupakan URL untuk mengakses aplikasi Yii yang sudah ada.

Jika aplikasi Yii yang sudah ada menggunakan URL format `path` (lihat [URL management](/doc/guide/topics.url)), kita dapat mengakses Gii dengan URL `http://hostname/path/to/index.php/gii`. Kita perlu menambah aturan URL berikut di depan aturan URL yang sudah ada:

~~~
[php]
'components'=>array(
	......
	'urlManager'=>array(
		'urlFormat'=>'path',
		'rules'=>array(
			'gii'=>'gii',
			'gii/<controller:\w+>'=>'gii/<controller>',
			'gii/<controller:\w+>/<action:\w+>'=>'gii/<controller>/<action>',
			...existing rules...
		),
	),
)
~~~

Gii dilengkapi dengan beberapa code generator(kode penghasil). Setiap code generator bertanggung jawab untuk menghasilkan kode dengan jenis tertentu. Misalnya, untuk controller generator menghasilkan sebuah kelas controller dengan beberapa skrip aksi view(action view); model generator menghasilkan kelas ActiveRecord (rekaman aktif) sesuai tabel database yang sudah ditentukan.

Pada dasarnya urutan menggunakan generator sebagai berikut:

1. Masuk ke halaman generator;
2. Isi field yang menspesifikasi parameter penghasil kode. Misalnya untuk memakai module generator yang berfungsi menghasilkan module baru, Anda harus menspesifikasi ID module;
3. Tekan `Preview` untuk melihat kode yang dihasilkan. Anda akan melihat sebuah tabel menampilkan daftar file kode yang akan dihasilkan. Anda dapat mengklik salah satu dari daftar tersebut untuk melihat isi kode;
4. Tekan tombol `Generate` untuk menghasilkan file-file kode tersebut;
5. Perhatikan catatan proses penghasilan kode (code generation log).


Meningkatkan Gii
----------------

Walaupun secara default, code generator yang dari Gii dapat menghasilkan kode yang sangat powerful, kita sering perlu menyesuaikan code generator tersebut atau membuat yang baru supaya sesuai dengan keperluan dan selera kita. Misalnya, kita ingin menghasilkan kode yang menjadi gaya koding kita sendiri, atau kita ingin kode kita mendukung berbagai bahasa. Semuanya dapat dilakukan dengan Gii secara mudah.

Gii dapat ditingkatkan dengan dua cara: mengubah templat kode(code template) yang sudah ada, dan menulis code generator yang baru.

###Struktur dari Code Generator

Sebuah code generator disimpan dalam sebuah direktori yang namanya diperlakukan sebagai nama generator. Direktori biasanya terdiri dari isi berikut:

~~~
model/                       folder root dari model generator
   ModelCode.php             kode model yang digunakan untuk menghasilkan kode
   ModelGenerator.php        controller dari code generator
   views/                    berisi skrip tilik(view) untuk generator
      index.php              default dari skrip tilik
   templates/                berisi kumpulan templat(template) kode
      default/               kumpulan template kode 'default'
         model.php           kode templat untuk menghasilkan kode kelas model
~~~

###Jalur Pencarian Generator(Generator Search Path)

Gii mencari generator yang tersedia di dalam kumpulan direktori yang ditentukan oleh properti [GiiModule::generatorPaths]. Ketika diperlukan kustomisasi, kita dapat mengatur properti ini dalam konfigurasi aplikasi sebagai berikut,

~~~
[php]
return array(
	'modules'=>array(
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'generatorPaths'=>array(
				'application.gii',   // sebuah alias path
			),
		),
	),
);
~~~

Konfigurasi di atas menginstruksi Gii untuk mencari generator yang terletak di dalam direktori dengan alias `application.gii`, sebagai tambahan bagi lokasi default `system.gii.generators`.

Dimungkinkan untuk memiliki dua generator dengan nama yang sama namun memiliki jalur pencarian(search path) yang berbeda. Kalau terjadi, generator di dalam jalur yang sudah ditentukan pada waktu awal di [GiiModule::generatorPaths] akan didahulukan.


###Mengkustomisasi Template Kode

Ini merupakan langkah termudah dan paling umum untuk mengembangkan Gii. Kita gunakan sebuah contoh untuk memahami bagaimana mengkustomisasi templat kode. Misalkan kita ingin mengkustomisasi sebuah kode yang dihasilkan oleh model generator.

Pertama-tama kita membuat sebuah direktori dengan nama `protected/gii/model/templates/compact`. Di sini `model` artinya kita akan *meng-override* default model generator. Dan `templates/compatct` artinya kita akan menambah kumpulan templat kode bernama `compact`

Kemudian kita mengubah konfigurasi aplikasi dengan menambah `application.gii` ke [GiiModule::generatorPaths], seperti yang ditunjukkan pada sub-bagian sebelumnya.

Sekarang buka halaman code generator model. Klik di field `Code Template`. Kita akan melihat sebuah dropdown list yang berisi direktori templat yang baru kita buat, `compact`. Akan tetapi, jika kita memilih templat ini untuk menghasilkan kode, kita akan menjumpai sebuah error dikarenakan kita belum meletakkan kode apapun ke dalam file templat di `compact`.

Salin(copy) file `framework/gii/generators/model/templates/default/model.php` ke `protected/gii/model/templates/compact`. Jika kita mencoba menghasilkan koding dengan templat `compact`, seharusnya akan sukses. Tetapi, kode yang dihasilkan tidak akan berbeda dengan yang dihasilkan oleh `default`.

Saatnya kita melakukan kustomisasi sesungguhnya. Buka file `protected/gii/model/templates/compact/model.php` untuk meng-edit-nya. Ingat bahwa file ini akan digunakan seperti skrip tilik(view), yang artinya akan mengandung expression dan statement PHP. Sekarang kita ubah templat sehingga metode `attributeLabels()` di kode yang dihasilkan menggunakan `Yii::t()` untuk menerjemahkan label atribut:

~~~
[php]
public function attributeLabels()
{
	return array(
<?php foreach($labels as $name=>$label): ?>
			<?php echo "'$name' => Yii::t('application', '$label'),\n"; ?>
<?php endforeach; ?>
	);
}
~~~

Di masing-masing templat kode, kita dapat mengakses beberapa variabel yang sudah didefinisikan sebelumnya, seperti `$labels` di contoh di atas. Varaibel ini disediakan oleh generator kode yang bersangkutan. Generator kode yang berbeda akan menghasilkan variabel-variable yang berbeda di templat kode. Silahkan baca dengan teliti deskripsi di dalam templat kode default.


###Membuat Generator baru

Di dalam sub-bab beriktu, kita akan melihat bagaimana membuat sebuah generator baru dapat menghasilkan kelas widget yang baru.

Pertama kita membuat sebuah direktori bernama `protected/gii/widget`. Di dalam direktori, kita akan membuat file-file berikut:

* `WidgetGenerator.php`: berisi kelas kontroller `WidgetGenerator`. Kelas ini merupakan titik awal untuk generator widget.
* `WidgetCode.php`: berisi kelas model `WidgetCode`. Kelas ini memiliki logika utama untuk menghasilkan kode
* `views/index.php`: skrip tilik(view) untuk menampilkan input form dari code generator
* `templates/default/widget.php`: kode templat default untuk menghasilkan sebuah file kelas widget.


#### Membuat `WidgetGenerator.php`

File `WidgetGenerator.php` sebetulnya sangat sederhana. File ini hanya berisi kode berikut:

~~~
[php]
class WidgetGenerator extends CCodeGenerator
{
	public $codeModel='application.gii.widget.WidgetCode';
}
~~~

Di dalam kode di atas, kita menetapkan bahwa generator akan menggunakan kelas model yang alias pathnya adalah `application.gii.widget.WidgetCode`. Kelas `WidgetGenerator` diturunkan dari [CCodeGenerator] yang mengimplementasi berbagai fungsionalitas, termasuk aksi(action) dari kontroller yang diperlukan untuk mengkoordinasi dengan proses penghasilan kode.

#### Membuat `WidgetCode.php`

File `WidgetCode.php` berisi kelas model `WidgetCode` memiliki logika utama untuk menghasilkan sebuah kelas widget berdasarkan input dari pengguna. Dalam contoh ini, kita mengasumsi bahwa input yang akan diterima dari pengguna hanyalah nama kelas widget. Tampilan `WidgetCode` akan seperti ini :

~~~
[php]
class WidgetCode extends CCodeModel
{
	public $className;

	public function rules()
	{
		return array_merge(parent::rules(), array(
			array('className', 'required'),
			array('className', 'match', 'pattern'=>'/^\w+$/'),
		));
	}

	public function attributeLabels()
	{
		return array_merge(parent::attributeLabels(), array(
			'className'=>'Widget Class Name',
		));
	}

	public function prepare()
	{
		$path=Yii::getPathOfAlias('application.components.' . $this->className) . '.php';
		$code=$this->render($this->templatepath.'/widget.php');

		$this->files[]=new CCodeFile($path, $code);
	}
}
~~~

Kelas `WidgetCode` diturunkan dari [CCodeModel]. Seperti kelas model pada umumnya, sebuah kelas dapat dideklarasi `rules()` dan `attributeLabels()` untuk melakukan validasi input dari pengguna dan menyediakan label atribut. Harap diingat karena kelas dasar [CCodeModel] sudah mendefinisikan beberapa peraturan(rules) dan label atribut, kita harus menggabungkan menjadi aturan dan label baru di sini.

Metode `prepare()` akan menyiapkan kode untuk dihasilkan. Fungsi utamanya adalah menyiapkan daftar dari objek-objek [CCodeFile], yang masing-masing mewakili sebuah file kode yang akan dihasilkan. Di dalam contoh kita, kita hanya perlu membuat sebuah objek [CCodeFile] yang mewakili file kelas widget. Kelas widget yang baru akan dihasilkan di dalam direktori `protected/components`. Kita memanggil metode [CCodeFile::render] untuk menghasilkan kode sebenarnya. Metode ini menyertakan templat kode sebagai skrip PHP dan mengembalikan isi yang di-echo sebagai kode.


#### Membuat `views/index.php`

Setelah memiliki kontroller (`WidgetGenerator`) dan model (`WidgetCode`), maka selanjutnya adalah membuat tilik `views/index.php`.

~~~
[php]
<h1>Widget Generator</h1>

<?php $form=$this->beginWidget('CCodeForm', array('model'=>$model)); ?>

	<div class="row">
		<?php echo $form->labelEx($model,'className'); ?>
		<?php echo $form->textField($model,'className',array('size'=>65)); ?>
		<div class="tooltip">
			Widget class name must only contain word characters.
		</div>
		<?php echo $form->error($model,'className'); ?>
	</div>

<?php $this->endWidget(); ?>
~~~

Pada kode di atas, kita menampilkan sebuah form menggunakan widget [CCodeForm]. Di dalam form ini, kita menampilkan field untuk mendapatkan input dari atribut `className` di `WidgetCode`.

Ketika membuat form, kita dapat memanfaatkan dua fitur yang disediakan oleh widget[CCodeForm]. Yang satu untuk memasukkan tooltips, dan yang satu lagi untuk sticky input.

Jika pernah mencoba code generator default, Anda pasti akan menyadari bahwa ketika kita meletakkan fokus pada satu input field, sebuah tooltip yang bagus akan tampil di sebelah field. Efek seperti ini bisa didapatkan dengan mudah dengan menulis di sebelah input field sebuah `div` yang kelas CSS nya adalah `tooltip`.

Pada beberapa field input, kita mungkin ingin mengingat nilai valid sebelumnya sehingga pengguna tidak perlu memasukkannya lagi setiap kali ingin menggunakan generator untuk menghasilkan kode. Contohnya adalah input field yang meminta nama kelas dasar kontroller pada controller generator. Sticky field ini pada awalnya ditampilkan sebagai teks statik yang disorot. Jika kita mengkliknya, mereka akan berubah menjadi input field yang mengambil inputan pengguna.

Untuk mendeklarasi sebuah input field menjadi sticky, kita perlu melakukan dua hal.

Pertama, kita perlu mendeklarasi sebuah aturan validasi `sticky` untuk atribut model yang bersangkutan. Misalnya, controllger generator default memiliki aturan bahwa atribut `baseClass` dan `actions` menjadi sticky:

~~~
[php]
public function rules()
{
	return array_merge(parent::rules(), array(
		......
		array('baseClass, actions', 'sticky'),
	));
}
~~~

Kedua, kita akan menambah sebuah kelas CSS yang bernama `sticky` ke dalam `div` dari input field dalam di tilik, seperti berikut ini:

~~~
[php]
<div class="row sticky">
	...masukkan input field ke sini...
</div>
~~~

#### Pembuatan `templates/default/widget.php`

Terakhir, kita membuat sebuah templat kode `templates/default/widget.php`. Seperti yang dideskripsi pada awal tadi, file ini seperti sebuah skrip tilik yang dapat menampung expression dan statement PHP. Di dalam templat kode, kita akan selalu mengakses variabel `$this` yang ditujukan ke objek model kode. Di dalam contoh, `$this` merujuk ke objek `WidgetModel`. Kita dapat mengambil nilai nama kelas yang dimasukkan pengguna dengan cara `$this->className`.

~~~
[php]
<?php echo '<?php'; ?>

class <?php echo $this->className; ?> extends CWidget
{
	public function run()
	{

	}
}
~~~

Langkah ini mengakhiri proses pembuatan code generator yang baru. Kita dapat mengakses code generator ini dengan segera melakui URL `http://hostname/path/to/index.php?r=gii/widget`.

<div class="revision">$Id: topics.gii.txt 3223 2011-05-17 23:02:50Z alexander.makarow $</div>