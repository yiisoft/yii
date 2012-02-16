Active Record
=============

Meskipun Yii DAO secara virtual dapat menangani setiap tugas terkait database,
kemungkinan kita akan menghabiskan 90% waktu kita dalam penulisan beberapa pernyataan SQL
yang melakukan operasi umum CRUD (create, read, update dan delete) tetap ada.
Pemeliharaan kode kita saat dicampur dengan pernyataan SQL juga akan menambah kesulitan.
Untuk memecahkan masalah ini, kita dapat menggunakan Active Record.

Active Record (AR) adalah teknik populer Pemetaan Relasional Objek / Object-Relational Mapping (ORM).
Setiap kelas AR mewakili tabel database (atau view) yang atributnya diwakili
oleh properti kelas AR, dan turunan AR mewakili baris pada tabel
tersebut. Operasi umum CRUD diimplementasikan sebagai metode AR. Hasilnya,
kita dapat mengakses data dengan cara lebih terorientasi-objek. Sebagai contoh,
kita dapat menggunakan kode berikut untuk menyisipkan baris baru ke dalam tabel `Post`:

~~~
[php]
$post=new Post;
$post->title='sample post';
$post->content='post body content';
$post->save();
~~~

Selanjutnya kita mendalami bagaimana menyiapkan AR dan menggunakannya untuk melakukan operasi
CRUD. Kita akan melihat bagaimana untuk menggunakan AR untuk bekerja dengan relasi database
dalam seksi berikutnya. Demi kemudahan, kami menggunakan tabel database berikut
sebagai contoh kita dalam bagian ini. Harap dicatat bahwa jika Anda menggunakan database MySQL,
Anda harus mengganti `AUTOINCREMENT` dengan `AUTO_INCREMENT` dalam SQL berikut.

~~~
[sql]
CREATE TABLE tbl_post (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	title VARCHAR(128) NOT NULL,
	content TEXT NOT NULL,
	create_time INTEGER NOT NULL
);
~~~

> Note|Catatan: AR tidak ditujukan untuk memecahkan semua tugas-tugas terkait-database. 
AR paling cocok dipakai untuk memodelkan tabel database dalam konstruksi PHP dan melakukan
query yang tidak melibatkan SQL yang kompleks. Yii DAO baru dipakai untuk skenario
kompleks tersebut.


Membuat Koneksi DB
--------------------

AR bergantung pada koneksi DB untuk melaksanakan operasi terkait-DB. Secara default,
AR menganggap bahwa komponen aplikasi `db` adalah turunan
[CDbConnection] yang dibutuhkan untuk bertindak sebagai koneksi DB. Konfigurasi
aplikasi berikut memperlihatkan sebuah contoh:

~~~
[php]
return array(
	'components'=>array(
		'db'=>array(
			'class'=>'system.db.CDbConnection',
			'connectionString'=>'sqlite:path/to/dbfile',
			// hidupkan cache schema untuk meningkatkan kinerja
			// 'schemaCachingDuration'=>3600,
		),
	),
);
~~~

> Tip: Karena Active Record bergantung pada metadata mengenai tabel untuk
menentukan informasi kolom, dibutuhkan waktu untuk membaca metadata dan
menganalisanya. Jika skema database Anda sepertinya kurang perlu diubah,
Anda bisa menghidupkan cache skema (schema caching) dengan mengkonfigurasi properti
[CDbConnection::schemaCachingDuration] ke nilai lebih besar daripada
0.

Dukungan terhadap AR dibatasi oleh DBMS. Saat ini, hanya DBMS berikut yang
didukung:

   - [MySQL 4.1 atau lebih tinggi](http://www.mysql.com)
   - [PostgreSQL 7.3 atau lebih tinggi](http://www.postgres.com)
   - [SQLite 2 dan 3](http://www.sqlite.org)
   - [Microsoft SQL Server 2000 atau lebih tinggi](http://www.microsoft.com/sqlserver/)
   - [Oracle](http://www.oracle.com)

Jika Anda ingin menggunakan komponen aplikasi selain `db`, atau jika Anda
ingin bekerja dengan multipel database menggunakan AR, Anda harus meng-override
[CActiveRecord::getDbConnection()]. Kelas [CActiveRecord] adalah basis kelas
untuk semua kelas AR.

> Tip: Ada dua cara untuk bekerja dengan multipel database dalam AR. Jika
skema database berbeda, Anda dapat membuat basis kelas AR yang berbeda
dengan implementasi berbeda pada
[getDbConnection()|CActiveRecord::getDbConnection]. Sebaliknya, secara dinamis
mengubah variabel static [CActiveRecord::db] merupakan ide yang lebih baik.

Mendefinisikan Kelas AR
-----------------------

Untuk mengakses tabel database, pertama kita perlu mendefinisikan kelas AR dengan
menurun [CActiveRecord]. Setiap kelas AR mewakili satu tabel database,
dan instance AR mewakili sebuah record (baris) dalam tabel tersebut. Contoh berikut
memperlihatkan kode minimal yang diperlukan untuk kelas AR yang mewakili
tabel `Post`.

~~~
[php]
class Post extends CActiveRecord
{
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return 'tbl_post';
	}
}
~~~

> Tip: Karena kelas AR sering dirujuk di banyak tempat, kita dapat
> mengimpor seluruh direktori yang berisi kelas AR, daripada menyertakannya
> satu demi satu. Sebagai contoh, jika semua file kelas AR kita di bawah
> `protected/models`, kita dapat mengkonfigurasi aplikasi sebagai berikut:
> ~~~
> [php]
> return array(
> 	'import'=>array(
> 		'application.models.*',
> 	),
> );
> ~~~

Secara default, nama kelas AR sama dengan nama tabel database.
Meng-override metode [tableName()|CActiveRecord::tableName] jika berbeda.
Metode [model()|CActiveRecord::model] dideklarasikan seperti itu
untuk setiap kelas AR (akan dijelaskan kemudian).

> Info: Untuk menggunakan [fitur prefiks tabel](/doc/guide/database.dao#using-table-prefix),
> metode [tableName()|CActiveRecord::tableName]
> untuk kelas AR harus di-override demikian,
> ~~~
> [php]
> public function tableName()
> {
>     return '{{post}}';
> }
> ~~~
> Alih-alih mengembalikan nama tabel secara lengkap, kita mengembalikan
> nama tabel tanpa prefiks dan membungkusnya dengan kurung kurawal ganda.

Nilai kolom pada baris tabel dapat diakses sebagai properti
turunan kelas AR terkait. Sebagai contoh, kode berikut menyetel kolom
`title` (atribut):

~~~
[php]
$post=new Post;
$post->title='a sample post';
~~~

Meskipun kita tidak pernah mendeklarasikan properti `title` secara eksplisit dalam kelas `Post`,
kita masih dapat mengaksesnya dalam kode di atas. Ini disebabkan `title` adalah
kolom dalam tabel `Post`, dan CActiveRecord membuatnya bisa diakses seperti layaknya
properti dengan bantuan metode magic PHP `__get()`. Exception (exception) akan ditampilkan
jika kita mencoba mengakses kolom yang tidak ada, dengan cara yang sama.

> Info: Di dalam panduan ini, kita menggunakan huruf kecil untuk seluruh nama tabel dan kolom.
Ini dikarenakan berbeda-bedanya cara DBMS dalam penanganan case-sensitive. Contohnya PostgreSQL
tidak memperlakukan nama kolom case-sensitive (jadi huruf besar sama dengan huruf kecil) secara default,
dan kita harus memberi tanda kutip pada kolom dalam query jika namanya memang mengandung campuran huruf besar dan kecil.
Menggunakan huruf kecil saja akan menyelesaikan permasalahan ini.

AR bergantung pada pendefinisian primary key tabel yang baik. Jika sebuah tabel tidak memiliki primary key,
maka AR membutuhkan kelas AR menentukan kolom mana yang dijadikan primary key dengan meng-overide fungsi `primaryKey()` sebagai berikut,

~~~
[php]
public function primaryKey()
{
	return 'id';
	// Kalau composite primary key, mak kembalikan nilai array seperti demikian
	// return array('pk1', 'pk2');
}
~~~

Membuat Record
---------------

Untuk melakukan insert baris baru ke dalam tabel database, kita membuat instance baru dari kelas
AR terkait, menyetel propertinya yang berkaitan dengan kolom tabel,
dan memanggil metode [save()|CActiveRecord::save] untuk menyelesaikan
proses insert.

~~~
[php]
$post=new Post;
$post->title='sample post';
$post->content='content for the sample post';
$post->createTime=time();
$post->save();
~~~

Jika primary key table bersifat auto-increment, setelah insert instance AR
maka akan berisi primary key yang baru. Dalam contoh di atas, properti
`id` akan merujuk pada nilai primary key tulisan yang baru saja disisipkan,
meskipun kita tidak pernah mengubahnya secara eksplisit.

Jika kolom didefinisikan dengan beberapa nilai standar statis (misalnya string, angka)
dalam skema tabel, properti terkait dalam instance AR akan secara otomatis memiliki
nilai tersebut setelah instance dibuat. Satu cara untuk mengubah nilai default ini
adalah dengan secara eksplisit mendeklarasikan properti dalam
kelas AR:

~~~
[php]
class Post extends CActiveRecord
{
	public $title='please enter a title';
	......
}

$post=new Post;
echo $post->title;  // ini akan menampilkan: please enter a title
~~~

Atribut dapat menempatkan nilai tipe [CDbExpression]
sebelum record disimpan (baik skenario insert ataupun update) ke database.
Sebagai contoh, untuk menyimpan timestamp yang dihasilkan oleh fungsi MySQL `NOW()`,
kita dapat menggunakan kode berikut:

~~~
[php]
$post=new Post;
$post->createTime=new CDbExpression('NOW()');
// $post->createTime='NOW()'; tidak akan bekerja karena
// 'NOW()' akan dianggap sebagai string
$post->save();
~~~

> Tip: Karena AR mengijinkan kita untuk melakukan operasi database tanpa menulis
sejumlah pernyataan SQL, seringkali kita ingin mengetahui pernyataan SQL apa yang dijalankan
oleh AR. Ini bisa dilakukan dengan menghidupkan [fitur pencatatan](/doc/guide/topics.logging)
pada Yii. Sebagai contoh, kita dapat menghidupkan [CWebLogRoute] dalam konfigurasi aplikasi,
dan kita akan melihat pernyataan SQL yang dijalankan untuk ditampilkan  di akhir setiap halaman Web.
Kita dapat menyetel [CDbConnection::enableParamLogging] menjadi true dalam
konfigurasi aplikasi agar nilai parameter yang diikat ke pernyataan SQL
juga dicatat.


Membaca Record
---------------

Untuk membaca data dalam tabel database, kita memanggil salah satu metode `find` seperti
berikut.

~~~
[php]
// cari baris pertama sesuai dengan kondisi yang ditetapkan
$post=Post::model()->find($condition,$params);
// cari baris dengan primary key yang ditetapkan
$post=Post::model()->findByPk($postID,$condition,$params);
// cari baris dengan nilai atribut yang ditetapkan
$post=Post::model()->findByAttributes($attributes,$condition,$params);
// cari baris pertama menggunakan pernyataan SQL yang ditetapkan
$post=Post::model()->findBySql($sql,$params);
~~~

Dalam contoh di atas, kita memanggil metode `find` dengan `Post::model()`. Ingat
bahwa metode statis `model()` diperlukan oleh setiap kelas AR. Metode ini
menghasilkan instance AR yang dipakai untuk mengakses metode tingkat kelas
(mirip dengan metode kelas static) dalam konteks obyek.

Jika metode `find` menemukan baris yang sesuai dengan kondisi query, ia akan
mengembalikan turunan `Post` yang propertinya berisi nilai kolom terkait
dari baris table. Kemudian kita dapat membaca nilai yang diambil sepert yang
kita lakukan pada properti obyek, sebagai contoh, `echo $post->title;`.

Metode `find` akan menghasilkan null jika tidak ada yang ditemukan dalam database
dengan kondisi query yang diberikan.

Ketika memanggil `find`, kita menggunakan `$condition` dan `$params` untuk menetapkan kondisi
query. Di sini, `$condition` dapat berupa string yang mewakili klausul `WHERE` dalam
pernyataan SQL, dan `$params` adalah array parameter yang nilainya harus diikat
ke tempat di dalam `$condition`. Sebagai contoh,

~~~
[php]
// cari baris dengan postID=10
$post=Post::model()->find('postID=:postID', array(':postID'=>10));
~~~

> Note|Catatan: Dalam contoh di atas, kita mungkin perlu meng-escape referensi pada kolom `postID`
untuk DBMS tertentu. Sebagai contoh, jika kita menggunakan PostgreSQL, kita harus menulis 
kondisi sebagai `"postID"=:postID`, karena PostgreSQL standarnya akan memperlakukan nama
kolom tidak case-sensitive.

Kita juga bisa menggunakan `$condition` untuk menetapkan kondisi query lebih kompleks
Alih-alih mengisi sebuah string, kita dapat mengatur `$condition` menjadi instance [CDbCriteria] yang
mengijinkan kita untuk menetapkan kondisi selain klausul `WHERE`. Sebagai contoh,

~~~
[php]
$criteria=new CDbCriteria;
$criteria->select='title';  // hanya memilih kolom 'title'
$criteria->condition='postID=:postID';
$criteria->params=array(':postID'=>10);
$post=Post::model()->find($criteria); // $params tidak diperlukan
~~~

Catatan, saat menggunakan [CDbCriteria] sebagai kondisi query, parameter `$params`
tidak lagi diperlukan karena ia bisa ditetapkan dalam [CDbCriteria], seperti dicontohkan
di atas.

Cara alternatif terhadap [CDbCriteria] adalah dengan mengoper array ke metode `find`.
Kunci dan nilai array masing-masing berhubungan dengan properti kriteria nama dan nilai.
Contoh di atas dapat ditulis ulang seperti berikut

~~~
[php]
$post=Post::model()->find(array(
	'select'=>'title',
	'condition'=>'postID=:postID',
	'params'=>array(':postID'=>10),
));
~~~

> Info: Saat kondisi query menemukan beberapa kolom dengan nilai yang
ditetapkan, kita dapat menggunakan
[findByAttributes()|CActiveRecord::findByAttributes]. Kita biarkan parameter
`$attributes` menjadi array nilai yang diindeks oleh nama kolom.
Dalam beberapa framework, tugas ini bisa dilaksanakan dengan memanggil metode
seperti `findByNameAndTitle`. Meskipun pendekatan ini terlihat atraktif, sering
menyebabkan kebingungan, konflik dan masalah seperti sensitifitas-jenis huruf pada
nama-nama kolom.

Ketika lebih dari satu baris data memenuhi kondisi query yang ditetapkan, kita dapat
mengambilnya sekaligus menggunakan metode `findAll`, masing-masing memiliki
pasangan metode `find`, seperti yang sudah kami jelaskan.

~~~
[php]
// cari seluruh baris yang sesuai dengan kondisi yang ditetapkan
$posts=Post::model()->findAll($condition,$params);
// cari seluruh baris dengan primary key yang ditetapkan
$posts=Post::model()->findAllByPk($postIDs,$condition,$params);
// cari seluruh baris dengan nilai atribut yang ditetapkan
$posts=Post::model()->findAllByAttributes($attributes,$condition,$params);
// cari seluruh baris dengan pernyataan SQL yang ditetapkan
$posts=Post::model()->findAllBySql($sql,$params);
~~~

Jika tidak ada yang sama dengan kondisi query, `findAll` akan mengembalikan array
kosong. Ini berbeda dengan `find` yang akan mengembalikan null jika tidak menemukan
apapun.

Selain metode `find` dan `findAll` seperti dijelaskan di atas, metode berikut
juga disediakan demi kenyamanan:

~~~
[php]
// ambil sejumlah baris yang sesuai dengan kondisi yang ditetapkan
$n=Post::model()->count($condition,$params);
// ambil sejumlah baris menggunakan pernyataan SQL yang ditetapkan
$n=Post::model()->countBySql($sql,$params);
// periksa apakah ada satu baris yang sesuai denga kondisi yang ditetapkan
$exists=Post::model()->exists($condition,$params);
~~~

Mengupdate Record
---------------------

Setelah instance AR diisi dengan nilai kolom, kita dapat mengubah
dan menyimpannya kembali ke tabel database.

~~~
[php]
$post=Post::model()->findByPk(10);
$post->title='new post title';
$post->save(); // simpan perubahan ke database
~~~

Seperti yang kita lihat, kita menggunakan metode [save()|CActiveRecord::save] yang sama
untuk melakukan operasi insert maupun update. Jika instance AR dibuat
menggunakan operator `new`, pemanggilan [save()|CActiveRecord::save] akan menyisipkan
baris record baru ke dalam tabel database; jika turunan AR adalah hasil dari beberapa
pemanggilan metode `find` atau `findAll`, memanggil [save()|CActiveRecord::save] akan
mengupdate baris yang sudah ada dalam tabel. Bahkan, kita dapat menggunakan
[CActiveRecord::isNewRecord] untuk mengetahui apakah turunan AR baru atau tidak.

Dimungkinkan juga untuk mengupdate satu atau beberapa baris dalam sebuah tabel database
tanpa memanggilnya lebih dulu. AR menyediakan metode tingkat-kelas yang nyaman
untuk tujuan ini:

~~~
[php]
// mutakhirkan baris yang sama seperti kondisi yang ditetapkan
Post::model()->updateAll($attributes,$condition,$params);
// mutakhirkan baris yang sama seperti kondisi dan primary key yang ditetapkan
Post::model()->updateByPk($pk,$attributes,$condition,$params);
// mutakhirkan kolom counter dalam baris yang sesuai dengan kondisi yang ditetapkan
Post::model()->updateCounters($counters,$condition,$params);
~~~

Dalam contoh di atas, `$attributes` adalah array nilai kolom yang diindeks oleh
nama kolom; `$counters` adalah array nilai inkremental yang diindeks oleh nama
kolom; sedangkan `$condition` dan `$params` seperti yang sudah dijelaskan dalam
sub-bab sebelumnya.

Menghapus Record
-----------------

Kita juga bisa menghapus baris data jika turunan AR sudah diisi
dengan baris ini.

~~~
[php]
$post=Post::model()->findByPk(10); // menganggap ada tulisan yang memiliki ID = 10
$post->delete(); // hapus baris dari tabel database
~~~

Catatan, setelah penghapusan, turunan AR tetap tidak berubah, tapi baris
terkait dalam tabel database sudah tidak ada.

Metode tingkat kelas berikut disediakan untuk menghapus baris tanpa
haris mengambilnya lebih dulu:

~~~
[php]
// hapus baris yang sesuai dengan kondisi yang ditetapkan
Post::model()->deleteAll($condition,$params);
// hapus baris yang sesuai dengan kondisi dan primary key yang ditetapkan
Post::model()->deleteByPk($pk,$condition,$params);
~~~

Validasi Data
-------------

Ketika melakukan insert atau update baris, seringkali kita harus memeriksa apakah nilai
kolom sesuai dengan aturan tertentu. Hal ini penting terutama jika nilai kolom
diberikan oleh pengguna akhir. Pada dasarnya, kita seharusnya tidak boleh
mempercayai apapun yang berasal dari sisi klien.

AR melakukan validasi data secara otomatis ketika
[save()|CActiveRecord::save] sedang dipanggil. Validasi didasarkan pada
aturan yang ditetapkan dalam metode [rules()|CModel::rules] pada kelas AR.
Untuk lebih jelasnya mengenai bagaimana untuk menetapkan aturan validasi, silahkan merujuk ke
bagian [Mendeklarasikan Aturan Validasi](/doc/guide/form.model#declaring-validation-rules).
Di bawah ini adalah alur kerja umum yang diperlukan oleh penyimpanan record:

~~~
[php]
if($post->save())
{
	// data benar dan insert/update dengan sukses
}
else
{
	// data tidak benar. panggil getErrors() untuk mengambil pesan kesalahan
}
~~~

Ketika data untuk insert atau update dikirimkan oleh pengguna akhir dalam
bentuk HTML, kita perlu menempatkannya ke properti AR terkait. Kita apat melakukannya
seperti berikut:

~~~
[php]
$post->title=$_POST['title'];
$post->content=$_POST['content'];
$post->save();
~~~

Jika terdapat banyak kolom, kita akan melihat daftar yang panjang dari penempatan tersebut.
Ini dapat dipersingkat dengan pemakaian properti
[attributes|CActiveRecord::attributes] seperti ditampilkan di bawah. Rincian
dapat ditemukan dalam seksi [Mengamankan Penempatan Atribut](/doc/guide/form.model#securing-attribute-assignments)
dan seksi [Membuat Aksi](/doc/guide/form.action).

~~~
[php]
// anggap $_POST['Post'] adalah array nilai kolom yang diindeks oleh nama kolom
$post->attributes=$_POST['Post'];
$post->save();
~~~


Membandingkan Record
---------------------

Seperti baris tabel, turunan AR secara unik diidentifikasi dengan nilai primary key.
Oleh karena itu, untuk membandingkan dua instance AR, kita perlu membandingkan
nilai primary key-nya, menganggap keduanya milik kelas AR yang sama. Cara
paling sederhana adalah dengan memanggil [CActiveRecord::equals()].

> Info: Tidak seperti implementasi AR dalam framework lain, Yii mendukung
primary key composite dalam turunan AR-nya. Kunci primer terdiri dari dua
atau lebih kolom. Secara bersamaan, nilai primary key disajikan sebagai
array dalam Yii. Properti [primaryKey|CActiveRecord::primaryKey] memberikan
nilai primary key pada turunan AR.

Kustomisasi
-----------

[CActiveRecord] menyediakan beberapa metode penampungan yang dapat di-overide
dalam anak kelas untuk mengkustomisasi alur kerjanya.

   - [beforeValidate|CModel::beforeValidate] dan
[afterValidate|CModel::afterValidate]: ini dipanggil sebelum dan sesudah
validasi dilakukan.

   - [beforeSave|CActiveRecord::beforeSave] dan
[afterSave|CActiveRecord::afterSave]: ini dipanggil sebelum dan sesudah
penyimpanan instance AR.

   - [beforeDelete|CActiveRecord::beforeDelete] dan
[afterDelete|CActiveRecord::afterDelete]: ini dipanggil sebelum dan sesudah
instance AR dihapus.

   - [afterConstruct|CActiveRecord::afterConstruct]: ini dipanggil untuk
setiap instance AR yang dibuat menggunakan operator `new`.

   - [beforeFind|CActiveRecord::beforeFind]: ini dipanggil sebelum pencari AR
dipakai untuk melakukan query (misal `find()`, `findAll()`). 

   - [afterFind|CActiveRecord::afterFind]: ini dipanggil untuk setiap instance AR
yang dibuat sebagai hasil dari query.


Menggunakan Transaksi dengan AR
-------------------------------

Setiap instance AR berisi properti bernama
[dbConnection|CActiveRecord::dbConnection] yang merupakan turunan dari [CDbConnection].
Kita dapat menggunakan fitur
[transaksi](/doc/guide/database.dao#using-transactions) yang disediakan oleh Yii
DAO jika diinginkan saat bekerja dengan AR:

~~~
[php]
$model=Post::model();
$transaction=$model->dbConnection->beginTransaction();
try
{
	// cari dan simpan adalah dua langkah yang bisa diintervensi oleh permintaan lain
	// oleh karenanya kita menggunakan transaksi untuk memastikan konsistensi dan integritas
	$post=$model->findByPk(10);
	$post->title='new post title';
	$post->save();
	$transaction->commit();
}
catch(Exception $e)
{
	$transaction->rollBack();
}
~~~


Named Scope
---------------

> Note|Catatan: Ide named scope berasal dari Ruby on Rails.

Sebuah *named scope* mewakili kriteria query *bernama* yang dapat digabung dengan named scope lain dan diterapkan ke query active record.

Named scope dideklarasikan terutama dalam metode [CActiveRecord::scopes()] sebagai pasangan nama-kriteria. Kode berikut mendeklarasikan tiga named scope, `published` dan `recently`, dalam kelas model `Post`

~~~
[php]
class Post extends CActiveRecord
{
	......
	public function scopes()
	{
		return array(
			'published'=>array(
				'condition'=>'status=1',
			),
			'recently'=>array(
				'order'=>'createTime DESC',
				'limit'=>5,
			),
		);
	}
}
~~~

Setiap named scope dideklarasikan sebagai sebuah array yang dipakai untuk menginisialisasi instance [CDbCriteria]. Sebagai contoh, named scope `recently` menetapkan bahwa properti `order` adalah `createTime DESC` dan properti `limit` adalah 5 yang diterjemahkan ke dalam sebuah kriteria query yang harus menghasilkan paling banyak 5 tulisan terbaru.

Named scope dipakai sebagai pembeda pada pemanggilan metode `find`. Beberapa named scope dapat dikaitkan bersamaan dan menghasilkan set yang lebih terbatas. Sebagai contoh, untuk mencari tulisan terbaru yang diterbitkan, kita menggunakan kode berikut:

~~~
[php]
$posts=Post::model()->published()->recently()->findAll();
~~~

Secara umum, named scope harus berada di sebelah kiri pemanggilan metode `find`. Masing-masing menyediakan kriteria query, yang merupakan gabungan dengan kriteria lain, termasuk yang dioper ke pemanggilan metode `find`. Hal ini mirip dengan menambahkan sebuah daftar filter ke sebuah query.

> Note|Catatan: Named scope hanya bisa dipakai dengan metode tingkat-kelas. Yakni, metode harus dipanggil menggunakan `ClassName::model()`.


### Parameterisasi Named Scope

Named scope dapat diparameterkan. Sebagai contoh, kita mungkin ingin mengkustomisasi sejumlah tulisan dengan named scope `recently`. Untuk melakukannya, daripada mendeklarasikan named scope dalam metode [CActiveRecord::scopes], kita dapat mendefinisikan sebuah metode baru yang namanya sama seperti named scope tadi:

~~~
[php]
public function recently($limit=5)
{
	$this->getDbCriteria()->mergeWith(array(
		'order'=>'createTime DESC',
		'limit'=>$limit,
	));
	return $this;
}
~~~

Selanjutnya, kita bisa menggunakan pernyataan berikut untuk mengambil 3 tulisan terbaru yang diterbitkan:

~~~
[php]
$posts=Post::model()->published()->recently(3)->findAll();
~~~

Jika kita tidak melengkapi parameter 3 di atas, secara standar kita akan mengambil 5 tulisan terbaru yang diterbitkan.


### Default  Scope

Kelas model dapat memiliki default scope yang akan diterapkan untuk semua query (termasuk yang relasional) berkenaan dengan model. Sebagai contoh, website yang mendukung multi bahasa mungkin hanya ingin menampilkan konten yang ditetapkan oleh pengguna saat ini. Karena di sana ada banyak query atas konten situs, kita dapat mendefinisikan default scope guna memecahkan masalah ini. Untuk melakukannya, kita meng-override metode [CActiveRecord::defaultScope] seperti berikut,

~~~
[php]
class Content extends CActiveRecord
{
	public function defaultScope()
	{
		return array(
			'condition'=>"language='".Yii::app()->language."'",
		);
	}
}
~~~

Sekarang, jika metode di bawah ini dipanggil, Yii akan secara otomatis menggunakan kriteria query seperti didefinisikan di atas:

~~~
[php]
$contents=Content::model()->findAll();
~~~

> Catatan|Note: Default scope dan named scope hanya berlaku pada query `SELECT`. Mereka akan mengabaikan query `INSERT`, `UPDATE`, dan `DELETE`.
> Juga, ketika mendeklarasikan scope (baik default maupun named), kelas AR tidak dapat digunakan untuk membuat query DB di dalam method yang mendeklarasikan scope.

<div class="revision">$Id: database.ar.txt 3318 2011-06-24 21:40:34Z qiang.xue $</div>