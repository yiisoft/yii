Controller
====================

Sebuah `controller` adalah instance dari [CController] atau kelas turunan [CController].
Controller dibuat oleh aplikasi saat pengguna me-request-nya. Ketika berjalan, controller
melakukan action yang di-request yang biasanya memerlukan model
dan membuat view yang sesuai. Sebuah `action`, dalam bentuk paling sederhana sebenarnya
hanyalah metode kelas controller yang namanya dimulai dengan kata `action`.

Controller memiliki action standar. Ketika permintaan pengguna tidak menetapkan
action mana yang dijalankan, action standar yang akan dijalankan. Biasanya
action default dinamai sebagai `index`. Action default bisa diubah dengan mengeset
variabel instance publik, [CController::defaultAction].

Berikut merupakan code untuk mendefinisikan controller `site`, sebuah action `index` (action
default), dan sebuah action `contact`:

~~~
[php]
class SiteController extends CController
{
	public function actionIndex()
	{
		// ...
	}

	public function actionContact()
	{
		// ...
	}
}
~~~


Route
-----------

Controller dan action diidentifikasi oleh ID. ID Controller dalam
format `path/ke/xyz` sesuai dengan letak file kelas controller di
`protected/controllers/path/ke/XyzController.php`. `xyz`
harus diganti dengan nama sebenarnya (contoh, `post` pada
`protected/controllers/PostController.php`). ID Action adalah nama metode
action tanpa berawalan `action`. Sebagai contoh, jika kelas controller berisi
sebuah metode bernama `actionEdit`, ID dari action tersebut adalah
`edit`.

Request pengguna terhadap controller dan action tertentu sesuai dengan aturan route.
Route dibentuk dengan menggabungkan ID controller dan ID action yang dipisahkan
dengan garis miring. Sebagai contoh, route `post/edit` merujuk ke `PostController`
dan action `edit`. Dan secara default, URL
`http://hostname/index.php?r=post/edit` akan meminta controller post dan action edit.

>Note|Catatan: Secara default, route bersifat case-sensitive. Kita bisa saja
>menjadikan route tidak bersifat case-sensitive dengan mengatur [CUrlManager::caseSensitive]
>menjadi false dalam konfigurasi aplikasi. Ketika dalam mode tidak bersifat case-sensitive,
>pastikan Anda mengikuti konvensi bahwa direktori yang berisi file kelas controller
>dalam huruf kecil, dan [peta controller|CWebApplication::controllerMap]
>serta [peta action|CController::actions] keduanya menggunakan kunci dalam huruf kecil.

Sebuah aplikasi bisa berisi [module](/doc/guide/basics.module). Route action controller
di dalam sebuah module yakni dalam format `moduleID/controllerID/actionID`.
Untuk lebih rinci, lihat [bagian mengenai module](/doc/guide/basics.module).


Instansiasi Controller
-------------------

Instance controller dibuat ketika [CWebApplication] menangani request
yang masuk. Berdasarkan ID controller yang diberikan, aplikasi akan
menentukan kelas controller apa dan di mana file kelas ditempatkan, dengan menggunakan
aturan berikut.

   - Jika [CWebApplication::catchAllRequest] ditetapkan, controller
akan dibuat berdasarkan properti ini, dam ID controller yang ditetapkan pengguna
akan diabaikan. Ini dipakai terutama untuk menyimpan aplikasi dalam maintenance
mode dan menampilkan halaman statis pemberitahuan.

   - Jika ID ditemukan dalam [CWebApplication::controllerMap], konfigurasi
controller terkait akan dipakai dalam membuat turunan
controller.

   - Jika ID ada dalam format `'path/ke/xyz'`, nama kelas controller
diasumsikan adalah `XyzController` dan file kelas terkait adalah
`protected/controllers/path/ke/XyzController.php`. Sebagai contoh, ID controller
`admin/user` akan diuraikan sebagai kelas controller `UserController`
dan file kelas `protected/controllers/admin/UserController.php`.
Jika file kelas tidak ada, 404 [CHttpException] akan dimunculkan.

Apabila [module](/doc/guide/basics.module) dipakai ,
proses di atas akan cukup berbeda. Dalam keadaan tertentu, aplikasi akan memeriksa apakah ID
merujuk ke controller di dalam sebuah module, jika demikian, instance module akan dibuat lebih
dulu diikuti dengan instance controller.


Action
------------

Seperti yang telah disinggung, action dapat didefinisikan sebagai metode yang namanya dimulai
dengan kata `action`. Cara lebih advance adalah dengan mendefinisikan kelas action
dan meminta controller untuk menurunkannya apabila diperlukan. Dengan demikian memungkinkan sebuah action
untuk dipakai ulang.

Untuk mendefinisikan kelas action baru, lakukan hal berikut:

~~~
[php]
class UpdateAction extends CAction
{
	public function run()
	{
		// tempat logika action di sini
	}
}
~~~

Agar controller menyadari adanya action ini, kita override metode
[actions()|CController::actions] pada kelas controller kita:

~~~
[php]
class PostController extends CController
{
	public function actions()
	{
		return array(
			'edit'=>'application.controllers.post.UpdateAction',
		);
	}
}
~~~

Di atas, kita menggunakan alias path
`application.controllers.post.UpdateAction` untuk menetapkan apakah file kelas
action adalah `protected/controllers/post/UpdateAction.php`.

Dengan menulis action berbasis-kelas, kita dapat mengatur aplikasi dalam gaya
modular. Sebagai contoh, struktur direktori berikut dapat dipakai untuk
mengatur kode controller:

~~~
protected/
    controllers/
        PostController.php
        UserController.php
        post/
            CreateAction.php
            ReadAction.php
            UpdateAction.php
        user/
            CreateAction.php
            ListAction.php
            ProfileAction.php
            UpdateAction.php
~~~

Action Parameter Binding
---------------------------------------------------
Semenjak versi 1.1.4, Yii telah menambah dukungan binding (pengikatan) parameter action otomatis,
yakni sebuah action controller dapat mendefinisikan parameter yang nilainya akan secara
otomatis diisi oleh Yii dari nilai `$_GET`.

Untuk mengilustrasi bagaimana cara kerjanya, mari kita asumsi bahwa kita perlu membuat sebuah action `create`
pada `PostController`. Action ini memerlukan dua buah parameter :

   * `category`: sebuah integer yang menunjukkan ID kategori ke berapa post baru yang
akan dibuat.
   * `language`: sebuah string yang mewakili kode bahasa pada post baru.

Kita mungkin akan menggunakan coding berikut ini untuk mendapatkan nilai yang
diinginkan dari parameter `$_GET`:

~~~
[php]
class PostController extends CController
{
    public function actionCreate()
    {
        if(isset($_GET['category']))
            $category=(int)$_GET['category'];
        else
            throw new CHttpException(404,'invalid request');

        if(isset($_GET['language']))
            $language=$_GET['language'];
        else
            $language='en';

        // ... mulai koding di sini...
    }
}
~~~

Sekarang, dengan menggunakan fitur parameter action, kita bisa mendapatkan hasil sama dengan cara lebih gampang :

~~~
[php]
class PostController extends CController
{
    public function actionCreate($category, $language='en')
    {
        $category=(int)$category;

        // ... mulai koding di sini...
    }
}
~~~

Perhatikan bahwa kita menambahkan dua buah parameter ke metode action `actionCreate`.
Nama parameter ini haruslah sama persis dengan nama yang ingin didapatkan dari `$_GET`. Parameter
`$language` mengambil nilai default `en` apabila ternyata pengguna tidak
memberikan nilai pada parameter. Karena `$category` tidak memiliki nilai default,
maka jika pengguna tidak memberikan parameter `category` di `$_GET`
sebuah [CHttpException](kode kesalahan 400) akan dikeluarkan secara otomatis.

Mulai dari versi 1.1.5, Yii juga akan mendukung pendeteksian jenis array untuk parameter action.
Ini dapat dilakukan dengan PHP type hinting (pemberian petunjuk jenis PHP) dengan menggunakan sintaks berikut :

~~~
[php]
class PostController extends CController
{
	public function actionCreate(array $categories)
	{
		// Yii akan memastikan $categories adalah sebuah array
	}
}
~~~

Demikianlah, kita menambah kata kunci `array` di depan `$categories` dalam deklarasi
parameter metode tersebut. Dengan melakukan demikian, jika `$_GET['categories']` adalah
sebuah string sederhana, akan diubah menjadi sebuah array yang terdiri dari string tersebut.

> Note|Catatan: Jika sebuah parameter dideklarasi tanpa petunjuk jenis `array`, itu artinya parameter tersebut
> haruslah skalar (seperti non-array). Dalam kasus ini, mem-pass sebuah parameter array dengan
> `$_GET` akan mengakibatkan HTTP exception.

Mulai dari versi 1.1.7, binding parameter otomatis akan bekerja juga pada
action yang menggunakan class. Ketika method `run()` didefinisikan 
beberapa parameter, maka parameter-parameter ini akan diisi sesuai dengan
nilai parameter request yang diberi nama. Contohnya,

~~~
[php]
class UpdateAction extends CAction
{
	public function run($id)
	{
		// $id akan diisi dengan $_GET['id']
	}
}
~~~


Filter
------

Filter adalah code yang dikonfigurasikan supaya berjalan sebelum dan/atau
setelah action controller dijalankan. Misalnya, filter kontrol akses
dijalankan guna memastikan bahwa pengguna diotentikasi sebelum menjalankan
action yang diminta; filter kinerja bisa dipakai untuk mengukur waktu
yang diperlukan dalam menjalankan action.

Action bisa memiliki lebih dari satu filter. Filter dijalankan dalam urutan seperti
yang terlihat dalam daftar filter. Filter bisa menjaga eksekusi action dan filter
lain yang tidak dieksekusi.

Filter bisa didefinisikan sebagai metode kelas controller. Nama metode harus
dimulai dengan `filter`. Sebagai contoh, keberadaan metode
`filterAccessControl` mendefinisikan sebuah filter bernama `accessControl`. 
Metode filter harus bertanda:

~~~
[php]
public function filterAccessControl($filterChain)
{
	// panggil $filterChain->run() untuk melanjutkan penyaringan dan eksekusi action
}
~~~

di mana `$filterChain` adalah instance [CFilterChain] yang menggambarkan daftar
filter yang dikaitkan dengan action yang diminta. Di dalam metode filter, kita
dapat memanggil `$filterChain->run()` untuk melanjutkan penyaringan dan jalannya action.

Filter juga dapat berupa turunan [CFilter] atau anak kelasnya. Kode
berikut mendefinisikan kelas filter baru:

~~~
[php]
class PerformanceFilter extends CFilter
{
	protected function preFilter($filterChain)
	{
		// logika sedang diterapkan sebelum action dieksekusi
		return true; // false jika action tidak dieksekusi
	}

	protected function postFilter($filterChain)
	{
		// logika sedang diterapkan setelah action dieksekusi
	}
}
~~~

Untuk menerapkan filter terhadap action, kita perlu meng-override metode
`CController::filters()`. Metode harus mengembalikan array konfigurasi
filter. Contoh,

~~~
[php]
class PostController extends CController
{
	......
	public function filters()
	{
		return array(
			'postOnly + edit, create',
			array(
				'application.filters.PerformanceFilter - edit, create',
				'unit'=>'second',
			),
		);
	}
}
~~~

Kode di atas menetapkan dua filter: `postOnly` dan `PerformanceFilter`.
Filter `postOnly` berbasis-metode (metode filter yang berhubungan sudah didefinisikan
dalam [CController]); sementara filter `PerformanceFilter` berbasis
objek. Alias path `application.filters.PerformanceFilter`
menetapkan bahwa file kelas filter adalah
`protected/filters/PerformanceFilter`. Kita menggunakan array untuk mengkonfigurasi
`PerformanceFilter` agar dapat dipakai guna menginisialisasi nilai
properti objek filter. Di sini, properti `unit` pada
`PerformanceFilter` akan diinisialisasi sebagai `'second'`.

Dengan menggunakan operator plus dan minus, kita dapat menetapkan action mana
yang harus diterapkan dan yang mana tidak diterapkan oleh filter. Dalam contoh di atas, `postOnly`
harus diterapkan ke action `edit` dan `create`, sementara
`PerformanceFilter` harus diterapkan ke semua action KECUALI `edit` dan
`create`. Jika plus maupun minus tidak muncul dalam konfigurasi filter,
maka filter akan diterapkan ke semua action.

<div class="revision">$Id: basics.controller.txt 3251 2011-06-01 00:24:06Z qiang.xue $</div>
