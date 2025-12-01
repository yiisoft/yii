Tema (Theme)
====

Tema adalah cara sistematis mengkustomisasi tampilan halaman dalam aplikasi
Web. Dengan menerapkan tema baru, penampilan aplikasi Web secara keseluruhan
bisa diubah secara instan dan secara dramatis.

Dalam Yii, setiap tema disajikan sebagai direktori yang terdiri dari file view,
file layout, dan file sumber daya relevan seperti file gambar, file CSS,
file JavaScript, dll.  Nama tema adalah nama direktorinya. Semua tema
berada di bawah dierktori yang sama `WebRoot/themes`. Hanya ada satu tema saja
yang bisa aktif.

> Tip: Direktori root standar tema `WebRoot/themes` bisa dikonfigurasi ke
direktori yang berbeda. Cukup konfigurasi properti
[basePath|CThemeManager::basePath] dan [baseUrl|CThemeManager::baseUrl]
pada komponen aplikasi [themeManager|CWebApplication::themeManager] 
ke direktori yang diinginkan.


Penggunaan Tema
-------------

Untuk mengaktifkan tema, setel properti [theme|CWebApplication::theme] 
aplikasi Web menjadi nama tema yang diinginkan. Ini bisa dikerjakan baik dalam
[konfigurasi
aplikasi](/doc/guide/basics.application#application-configuration) maupun selama berjalan
dalam aksi kontoler.

> Note|Catatan: Nama tema bersifat case-sensitive. Jika Anda mencoba
mengaktifkan tema yang tidak ada, `Yii::app()->theme` akan mengembalikan `null`.


Pembuatan Tema
----------------

Konten di bawah direktori theme harus diatur dengan cara yang sama seperti yang 
ada di bawah [basis path
aplikasi](/doc/guide/basics.application#application-base-directory). Sebagai contoh, semua file tampilan
harus ditempatkan di bawah `views`, file tampilan layout di bawah `views/layouts`, dan
file tampilan system di bawah `views/system`. Contoh lain, jika kita ingin mengganti
tampilan `create` pada `PostController` dengan tampilan pada tema `classic`,
kita harus menyimpan file tampilan baru sebagai `WebRoot/themes/classic/views/post/create.php`.

Untuk tampilan yang dimiliki controller dalam [module](/doc/guide/basics.module),
File tampilan tema terkait juga harus ditempatkan di bawah dierktori `views`.
Sebagai contoh, jika `PostController` ada dalam sebuah module bernama
`forum`, kita harus menyimpan file tampilan `create` sebagai `WebRoot/themes/classic/views/forum/post/create.php`. Jika module `forum` 
diulang dalam module lain bernama `support`, maka file tampilan seharusnya
`WebRoot/themes/classic/views/support/forum/post/create.php`.

> Note|Catatan: Karena direktori `views` mungkin berisi data yang sensitif, maka harus dikonfigurasi guna menjaga dari pengaksesan Web oleh pengguna.

Ketika kita memanggil [render|CController::render] atau
[renderPartial|CController::renderPartial] untuk menampilkan tampilan, file
tampilan terkait juga file tata letak akan dicari di
tema yang saat ini sedang aktif. Dan jika ditemukan, file-file tersebut akan dirender.
Sebaliknya, jika gagal akan kembali ke lokasi seperti yang ditetapkan oleh
[viewPath|CController::viewPath] dan
[layoutPath|CWebApplication::layoutPath].

> Tip: Di dalam tampilan tema, kita sering harus menghubungkan file sumber daya
> tema. Sebagai contoh, kita mungkin ingin memperlihatkan file gambar di bawah direktori
> `images` tema. Menggunakan properti [baseUrl|CTheme::baseUrl] property pada
> tema yang aktif saat ini, kita membuat URL untuk gambar seperti berikut,
>
> ~~~
> [php]
> Yii::app()->theme->baseUrl . '/images/FileName.gif'
> ~~~
>

Berikut ini merupakan contoh struktur direktori untuk aplikasi dengan dua tema, yakni `basic` dan `fancy`.

~~~
WebRoot/
	assets
	protected/
		.htaccess
		components/
		controllers/
		models/
		views/
			layouts/
				main.php
			site/
				index.php
	themes/
		basic/
			views/
				.htaccess
				layouts/
					main.php
				site/
					index.php
		fancy/
			views/
				.htaccess
				layouts/
					main.php
				site/
					index.php
~~~

Di dalam konfigurasi aplikasi jika kita mengatur

~~~
[php]
return array(
	'theme'=>'basic',
	......
);
~~~

maka tema `basic` akan aktif yang berarti tata letak(layout) aplikasi akan menggunakan
file yang terletak di direktori `themes/basic/views/layouts`. Selain itu, tampilan index dari situs
akan menggunakan file yang terletak di `themes/basic/views/site`. Misalnya file view(view) tidak ditemukan
di dalam tema tersebut, maka aplikasi akan menggunakan yang biasanya yakni di direktori `protected/views`


Pemberian Tema pada Widget (Theming Widgets)
---------------

Di mulai semenjak versi 1.1.5, view yang digunakan sebagai widget juga dapat diberi tema. Khususnya, ketika kita memanggil [CWidget::render()] untuk me-render sebuah view widget, Yii akan mencoba untuk mencari di folder theme dan juga folder view widget untuk mendapatkan file view yang diinginkan.

Guna memberikan tema pada view `xyz` untuk widget dengan nama kelas `Foo`, kita harus pertama membuat sebuah folder bernama `Foo` (sama dengan nama kelas widget) di dalam folder view tema aktif sekarang. Jika kelas widget di-namespace-kan (tersedia di PHP 5.3.0 atau ke atas), seperti `\app\widgets\Foo`, kita harus membuat sebuah folder bernama `app_widgets_Foo`. Kita kemudian menggantikan separator namespace dengan karakter garis bawah(underscore).

Kita kemudian membuat sebuah file view bernama `xyz.php` di dalam folder yang baru dibuat. Pada tahap ini, kita sudah memiliki sebuah file `themes/basic/views/Foo/xyz.php`, yang akan digunakan oleh widget untuk menggantikan view original, jika tema aktif sekang adalah `basic`.


Kustomisasi Widget Secara Global
--------------------------------

> Note|Catatan : Fitur ini sudah tersedia di versi 1.1.3

Kita sering perlu mengutak atik widget ketika menggunakannya baik yang disediakan
oleh pihak ketiga maupun yang disediakan Yii. Misalnya, kita mungkin ingin mengubah
nilai dari [ClinkPager::maxButtonCount] dari 10(secara default) menjadi 5. Kita bisa
saja memecahkan masalah ini dengan mengirim nilai properti awal ketika memanggil
[CBaseController::widget] untuk membuat sebuah widget. Namun, akan merepotkan
apabila kita harus melakukan hal yang sama di semua tempat yang menggunakan [ClinkPager].

~~~
[php]
$this->widget('CLinkPager', array(
	'pages'=>$pagination,
    'maxButtonCount'=>5,
    'cssFile'=>false,
));
~~~

Menggunakan fitur kustomisasi widget global, kita cukup menentukan nilai awal di satu tempat, misalnya
di konfigurasi aplikasi. Dengan cara demikian akan
membuat kustomisasi widget lebih gampang diatur.

Untuk menggunakan kustomisasi widget global, kita harus mengatur
[widgetFactory|CWebApplication::widgetFactory] sebagai demikian:

~~~
[php]
return array(
    'components'=>array(
        'widgetFactory'=>array(
            'widgets'=>array(
                'CLinkPager'=>array(
                    'maxButtonCount'=>5,
                    'cssFile'=>false,
                ),
                'CJuiDatePicker'=>array(
                    'language'=>'ru',
                ),
            ),
        ),
    ),
);
~~~

Kode di atas maksudnya kita menentukan widget [CLinkPager] dan [CJuiDatePicker]
dengan mengatur properti [CWidgetFactory::widgets]. Harap diperhatikan bahwa kustomisasi
setiap widget diwakilkan sebagai sebuah pasangan key-value dalam array, dengan key 
sebagai nama kelas widget sedangkan 
value merupakan array nilai properti awal.

Sekarang, setiap saat kita menciptakan sebuah widget [ClinkPager] di sebuah view, maka
nilai properti yang di atas akan dikirim ke widget, dan untuk membuat widget, kita hanya perlu menulis kode berikut
di dalam view:

~~~
[php]
$this->widget('CLinkPager', array(
	'pages'=>$pagination,
));
~~~

Tentunya, kita masih dapat meng-override nilai properti awal apabila diperlukan. Misalnya,
di beberapa view(view) kita ingin nilai `maxButtonCount` menjadi 2, maka kita cukup melakukan berikut:

~~~
[php]
$this->widget('CLinkPager', array(
	'pages'=>$pagination,
	'maxButtonCount'=>2,
));
~~~


Skin
----

Kita dapat menggunakan tema(theme) dapat mempercepat tampilan view. Sedangkan untuk dapat menyesuaikan tampilan [widgets](/doc/guide/basics.view#widget) secara sistematis, maka kita dapat menggunakan skin.

Sebuah skin merupakan sebuah array dengan pasangan nama-nilai(name-value) yang digunakan untuk menentukan nilai awal properti widget. Sebuah skin merupakan milik kelas widget dan sebuah kelas widget dapat memiliki beberapa skin yang dibedakan berdasarkan namanya. Misalnya kita dapat memiliki sebuah skin untuk widget [CLinkPager] dan skin dinamakan sebagai `classic`

Guna menggunakan fitur skin ini, pertama-tama kita harus memodifikasi konfigurasi aplikasi dengan mengatur properti [CWidgetFactory::enableSkin] menjadi true untuk komponen aplikasi `widgetFactory`:

~~~
[php]
return array(
    'components'=>array(
        'widgetFactory'=>array(
            'enableSkin'=>true,
        ),
    ),
);
~~~

Harap diketahui bahwa versi sebelum 1.1.3, Anda harus menggunakan konfigurasi berikut untuk mengaktifkan fungsi skin widget:

~~~
[php]
return array(
    'components'=>array(
        'widgetFactory'=>array(
            'class'=>'CWidgetFactory',
        ),
    ),
);
~~~

Kita kemudian membuat skin yang diperlukan. Skin yang merupakan milik kelas widget yang sama, akan disimpan di dalam sebuah file skrip PHP yang namanya berupa nama kelas widget. Semua file-file skin secara default akan disimpan di direktori `protected/views/skins`. Jika Anda ingin mengubahnya menjadi direktori lain, Anda bisa mengatur properti `skinPath` pada komponen `widgetFactory`. Misalnya, kita dapat membuat sebuah file bernama `CLinkPager.php` di direktori `protected/views/skins` yang isinya sebagai berikut,

~~~
[php]
<?php
return array(
    'default'=>array(
        'nextPageLabel'=>'&gt;&gt;',
        'prevPageLabel'=>'&lt;&lt;',
    ),
    'classic'=>array(
        'header'=>'',
        'maxButtonCount'=>5,
    ),
);
~~~

Di atas, kita membuat dua buah skin untuk widget [CLinkPager]: `default` dan `classic`. Skin `default` merupakan skin yang dikenakan pada widget [ClinkPager] yang kita tidak spesifikasi secara eksplisit properti `skin`-nya. Sedangkan skin `classic` merupakan skin yang diaplikasikan ke sebuah widget [CLinkPager] yang properti `skin` diisi `classic`. Misalnya pada kode view di bawah, pager pertama akan menggunakan skin `default` dan pager yang kedua akan menggunakan skin `classic`:

~~~
[php]
<?php $this->widget('CLinkPager'); ?>

<?php $this->widget('CLinkPager', array('skin'=>'classic')); ?>
~~~

Jika kita membuat widget dengan satu set nilai awal properti, maka nilai-nilai properti tersebut akan ambil yang sebelumnya dan digabungkan dengan skin yang dapat diaplikasikan. Misalnya, kode berikut akan membuat sebuah pager yang nilai awalnya berupa `array('header'=>'', 'maxButtonCount'=>6, 'cssFile'=>false)`, yang merupakan hasil nilai awal properti yang ditentukan di view digabung dengan skin `classic`.

~~~
[php]
<?php $this->widget('CLinkPager', array(
    'skin'=>'classic',
    'maxButtonCount'=>6,
    'cssFile'=>false,
)); ?>
~~~

Harap diperhatikan bahwa fitur skin TIDAK memerlukan tema(themes). Namun ketika sebuah tema sedang aktif, Yii juga akan mencari skin di direktori `skins` pada direktori view-nya tema (misalnya di `WebRoot/themes/classic/views/skins`). Misalnya sebuah skin dengan nama yang sama ternyata memang ada di dua tempat baik di tema maupun di direktori view aplikasi utama, maka skin tema akan ambil yang di tema.

Jika widget menggunakan skin yang tidak ada, Yii tetap akan membuat widget seperti biasanya tanpa ada galat(error) apapun.

> Info: Menggunakan skin bisa menurunkan performa karena Yii harus mencari file skin pada saat widget pertama kali dibuat.

Skin sangat mirip dengan fitur kustomisasi widget global.
Perbedaan utamanya adalah:

   - Skin lebih berhubungan dengan pengaturan nilai properti penampilan;
   - Sebuah widget bisa saja memiliki beberapa skin;
   - Skin dapat ditemakan(themeable);
   - Menggunakan skin lebih berat dibandingkan menggunakan kustomisasi widget global;

<div class="revision">$Id: topics.theming.txt 2890 2011-01-18 15:58:34Z qiang.xue $</div>