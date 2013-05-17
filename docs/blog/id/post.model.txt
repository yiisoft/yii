Kustomisasi Model Post
======================

Kelas model `Post` di-generate oleh tool `gii` pada umumnya perlu dimodifikasi di dua tempat:

 - method `rules()`: menetapkan aturan validasi untuk atribut model;
 - method `relations()`: menetapkan objek-objek yang direlasikan;

> Info : Sebuah [model](http://www.yiiframework.com/doc/guide/basics.model) terdiri atas sebuah daftar atribut, masing-masing berasosiasi dengan sebuah kolom pada tabel database bersangkutan. Atribut dapat dideklarasikan secara eksplisit sebagai variabel member kelas atau secara implisit tanpa deklarasi apapun.


Kustomisasi Method `rules()`
----------------------------

Pertama-tama kita menetapkan aturan validasi guna memastikan nilai atribut yang dimasukkan user adalah benar, sebelum mereka menyimpannya ke dalam database. Contohnya, atribut `status` dari `Post` seharusnya bernilai integer 1,2 atau 3. Tool `gii` juga men-generate aturan validasi untuk setiap model. Namun, aturan ini berdasarkan informasi kolom dan mungkin tidak sesuai.

Berdasarkan analisis requirement, kita perlu memodifikasi method `rules()` sebagai berikut:

~~~
[php]
public function rules()
{
	return array(
		array('title, content, status', 'required'),
		array('title', 'length', 'max'=>128),
		array('status', 'in', 'range'=>array(1,2,3)),
		array('tags', 'match', 'pattern'=>'/^[\w\s,]+$/',
			'message'=>'Tags can only contain word characters.'),
		array('tags', 'normalizeTags'),

		array('title, status', 'safe', 'on'=>'search'),
	);
}
~~~

Di atas, kita menetapkan bahwa atribut `title`, `content` dan `status` wajib diisi; dan panjang dari `title` tidak boleh lebih dari 128 karakter; atribut nilai `status` haruslah berupa 1 (draf), 2 (dipublikasi) atau 3 (diarsip); dan atribut `tags` hanya boleh berisi karakter dan koma. Sebagai tambahan, kita menggunakan `normalizeTags` untuk normalisasi tag-tag yang dimasukkan user sehingga setiap tag unik dan dipisahkan antar koma. Aturan terakhir digunakan oleh fitur pencarian, yang akan dijelaskan nantinya.

Validator-validator seperti `required`, `length`, `in` dan `match` semuanya adalah validator built-in dari Yii. Validator `normalizeTags` adalah sebuah validator berbasis method yang harus didefinisikan pada kelas `Post`. Untuk informasi lebih bagaimana menspesifikasi aturan validasi, silahkan merujuk ke [Guide](http://www.yiiframework.com/doc/guide/form.model#declaring-validation-rules).

~~~
[php]
public function normalizeTags($attribute,$params)
{
	$this->tags=Tag::array2string(array_unique(Tag::string2array($this->tags)));
}
~~~

dengan `array2string` dan `string2array` merupakan dua method yang didefinisikan oleh kelas model `Tag`. Silahkan merujuk ke file `/wwwroot/yii/demos/blog/protected/models/Tag.php` untuk detail lebih lanjut.

Aturan-aturan yang dideklarasikan method `rules()` dijalankan satu per satu ketika kita memanggil method [validate()|CMode::validate] atau [save()|CActiveRecord::save] dari instance model.

> Note|Catatan: Sangat penting untuk diingat bahwa atribut-atribut yang muncul di `rules()` harus dimasukan oleh end user. Atribut lain, seperti `id` dan `create_time` di dalam model `Post`, yang akan diset oleh kode kita atau database, tidak seharusnya muncul di `rules()`. Untuk lebih detail, silahkan merujuk ke [Pengamanan Assignment Atribut](http://www.yiiframework.com/doc/guide/form.model#securing-attribute-assignments).

Setelah melakukan perubahan-perubahan, kita dapat mengunjungi halaman pembuatan post untuk memastikan bahwa aturan validasi yang baru berjalan.


Kustomisasi Metode `relations()`
--------------------------------

Terakhir kita mengutak metode `relations()` untuk menetapkan objek yang berkaitan pada sebuah post. Dengan mendeklarasikan objek yang terkait di dalam `relations()`, kita dapat memanfaatkan kemampuan fitur [Relational ActiveRecord (RAR)](http://www.yiiframework.com/doc/guide/database.arr) untuk mengakses informasi objek yang berhubungan dengan post, misalnya pengarang (author) dan komentar (comment), tanpa perlu menulis statement SQL JOIN yang kompleks.

Kita mengatur metode `relations()` dengan cara ini:

~~~
[php]
public function relations()
{
	return array(
		'author' => array(self::BELONGS_TO, 'User', 'author_id'),
		'comments' => array(self::HAS_MANY, 'Comment', 'post_id',
			'condition'=>'comments.status='.Comment::STATUS_APPROVED,
			'order'=>'comments.create_time DESC'),
		'commentCount' => array(self::STAT, 'Comment', 'post_id',
			'condition'=>'status='.Comment::STATUS_APPROVED),
	);
}
~~~

Kita juga memperkenalkan dua konstan pada kelas model `Comment` yang digunakan di dalam metode di atas:

~~~
[php]
class Comment extends CActiveRecord
{
	const STATUS_PENDING=1;
	const STATUS_APPROVED=2;
	......
}
~~~

Relasi yang dideklarasi di dalam status `relations()` bahwa

 * Sebuah post merupakan kepunyaan dari pengarang (author) yang kelasnya adalah `User` dan relasi dibuat berdasarkan nilai atribut `author_id` dari sebuah post;
 * Sebuah post bisa memiliki banyak komentar yang kelasnya adalah `Comment` dan relasi yang dibuat berdasarkan nilai atribut `post_id` pada komentar. Komentar-komentar ini akan diusun berdasarkan waktu pembuatan dan komentar-komentar harus disetujui.
 * Relasi `commentCount` agak spesial karena mengembalikan hasil agregat yakni berapa banyak komentar yang dimiliki oleh post.


Dengan deklarasi relasi di atas, kita dapat dengan gampang mengakses nama pengarang dan komentar seperti berikut :

~~~
[php]
$author=$post->author;
echo $author->username;

$comments=$post->comments;
foreach($comments as $comment)
	echo $comment->content;
~~~

Untuk informasi lebih lengkap bagaimana mendeklarasi dan menggunakan relasi, silahkan merujuk ke [Guide](http://www.yiiframework.com/doc/guide/database.arr).


Menambah Properti `url`
---------------------

Sebuah post adalah konten yang berasosiasi dengan URL unik untuk menampilkan. Alih-alih memanggil [CWebApplication::createUrl] di mana-mana, dengan mengambil URL ini, kita boleh menambah sebuah properti `url` di dalam model `Post` sehingga kode pembuatan URL yang sama dapat dipakai ulang. Kemudian ketika kita melihat bagaimana mempercantik URL, kita akan melihat bagaimana menambah properti ini akan meningkatkan kenyamanan.

Untuk menambah properti `url`, kita memodifikasi kelas `Post` dengan menambah sebuah fungsi getter berikut:

~~~
[php]
class Post extends CActiveRecord
{
	public function getUrl()
	{
		return Yii::app()->createUrl('post/view', array(
			'id'=>$this->id,
			'title'=>$this->title,
		));
	}
}
~~~

Harap dicatat bahwa sebagai tambahan pada ID post, kita juga dapat menambah judul post sebagai parameter GET di dalam URL. Tujuannya adalah untuk search engine optimization (SEO), seperti yang kita jelaskan pada [Mempercantik URL](/doc/blog/final.url).

Karena [CComponent] adalah kelas induk tertinggi dari kelas `Post`, menambah fungsi getter `getUrl()` memungkinkan kita untuk menggunakan ekspresi seperti `$post->url`. Ketika kita mengakses `$post->url`, metode getter akan dieksekusi dan hasilnya akan dikembalikan sebagai nilai ekspresi. Untuk informasi lebih lengkap mengenai fitur component ini, silahkan mengacu ke [Guide](/doc/guide/basics.component).


Mewakili Status dalam Teks
---------------------------

Karena status sebuah post disimpan dalam database sebagai integer, kita perlu menyediakan teks yang diwakili sehingga lebih intuitif ketika ditampilkan kepada end user. Pada sistem yang besar, keperluan seperti ini sangatlah umum.

Sebagai solusi yang umum, kita menggunakan tabel `tbl_lookup` untuk menyimpan pemetaan antara nilai integer dan teks yang diwakili diperlukan oleh objek data lain . Kita modifikasikan kelas model `Lookup`, supaya lebih gampang mengakses data teks dalam tabel, menjadi berikut,

~~~
[php]
class Lookup extends CActiveRecord
{
	private static $_items=array();

	public static function items($type)
	{
		if(!isset(self::$_items[$type]))
			self::loadItems($type);
		return self::$_items[$type];
	}

	public static function item($type,$code)
	{
		if(!isset(self::$_items[$type]))
			self::loadItems($type);
		return isset(self::$_items[$type][$code]) ? self::$_items[$type][$code] : false;
	}

	private static function loadItems($type)
	{
		self::$_items[$type]=array();
		$models=self::model()->findAll(array(
			'condition'=>'type=:type',
			'params'=>array(':type'=>$type),
			'order'=>'position',
		));
		foreach($models as $model)
			self::$_items[$type][$model->code]=$model->name;
	}
}
~~~

Kode baru kita utamanya menyediakan dua metode statik: `Lookup::items()` dan `Lookup::item()`. Yang pertama mengembalikan sebuah daftar string kepunyaan tipe data tertentu, sedangkan yang kedua mengembalikan sebuah string tertentu sesuai tipe data dan nilai data yang diberikan.

Pada awal-awal, database blog kita sudah dipopulasikan dengan dua tipe lookup : `PostStatus` dan `CommentStatus`. Yang pertama merujuk ke status post, sedangkan yang kedua merujuk ke status komentar.

Dalam rangka membuat kode kita lebih gampang di baca, kita juga mendeklarasikan sebuah set konstan yang mewakili nilai integer status. Kita harus menggunakan konstan ini sepanjang kode kita ketika ingin merujuk ke nilai status yang terkait.

~~~
[php]
class Post extends CActiveRecord
{
	const STATUS_DRAFT=1;
	const STATUS_PUBLISHED=2;
	const STATUS_ARCHIVED=3;
	......
}
~~~

Oleh karena itu, kita dapat memanggil `Lookup::items('PostStatus')` untuk mendapatkan daftar dari status post yang memungkinkan (string teks diindeks berdasarkan nilai integer yang bersangkutan), dan memanggil `Lookup::item('PostStatus', Post::STATUS_PUBLISHED)` untuk mendapatkan teks yang mewakili nilai status yang dipublikasi.


<div class="revision">$Id: post.model.txt 2119 2010-05-10 01:27:29Z qiang.xue $</div>