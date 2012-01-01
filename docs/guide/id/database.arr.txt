	Active Record Relasional
========================

Kita sudah melihat bagaimana menggunakan Active Record (AR) untuk memilih data dari
satu tabel database. Dalam bagian ini,kita akan melihat bagaimana menggunakan AR untuk
menggabung beberapa tabel database terkait dan mengembalikan hasil data yang telah digabungkan.

Untuk menggunakan AR relasional, disarankan untuk mendeklarasi constraint primary key-foreign key
pada tabel yang ingin digabungkan(di-join). Contraint ini akan membantu menjaga konsistensi
dan integritas data relasional.

Untuk menyederhanakan, kita akan menggunakan skema database yang ditampilkan dalam
diagram entity-relationship (ER) atau hubungan-entitas berikut untuk memberi gambaran contoh pada
bagian ini.

![Diagram ER](er.png)

> Info: Dukungan untuk constraint foreign key bervariasi pada berbagai DBMS.
> SQLite < 3.6.19 tidak mendukung constraint foreign key, tetapi kita masih dapat
> mendeklarasikan constraint pada saat pembuatan tabel. Engine MySQL MyISAM
> tidak mendukung foreign key sama sekali.


Mendeklarasikan Hubungan
------------------------

Sebelum kita menggunakan AR untuk melakukan query relasional, kita perlu membuat AR
mengetahui bagaimana satu kelas AR dikaitkan dengan yang lain.

Hubungan antara dua kelas AR secara langsung dikaitkan dengan hubungan
antara tabel-tabel database yang diwakili oleh kelas-kelas AR.
Dari sudut pandang database, hubungan antar dua tabel A dan B memiliki
tiga jenis: one-to-many/satu-ke-banyak (misal `User` dan `Post`), one-to-one/satu-ke-satu (misal
`User` dan `Profile`) dan many-to-many/banyak-ke-banyak (misal `Category` dan `Post`). Dalam AR,
ada empat jenis hubungan:

   - `BELONGS_TO`: jika hubungan antara tabel A dan B adalah
satu-ke-banyak, maka B milik A (misal `Post` milik `User`);

   - `HAS_MANY`: jika hubungan tabel A dan B adalah satu-ke-banyak,
maka A memiliki banyak B (misal `User` memiliki banyak `Post`);

   - `HAS_ONE`: ini kasus khusus pada `HAS_MANY` di mana A memiliki paling banyak satu
B (misal `User` memiliki paling banyak satu `Profile`);

   - `MANY_MANY`: ini berkaitan dengan hubungan banyak-ke-banyak dalam
database. Tabel asosiatif diperlukan untuk memecah hubungan banyak-ke-banyak
ke dalam hubungan satu-ke-banyak, karena umumnya DBMS tidak mendukung
hubungan banyak-ke-banyak secara langsung. Dalam contoh skema database kita,
`tbl_post_category` yang menjadi tabel asosiatif ini. Dalam terminologi AR, kita dapat menjelaskan
`MANY_MANY` sebagai kombinasi `BELONGS_TO` dan `HAS_MANY`. Sebagai contoh,
`Post` milik banyak `Category` dan `Category` memiliki banyak `Post`.

Mendeklarasikan hubungan dalam AR mencakup penimpaan metode
[relations()|CActiveRecord::relations] pada [CActiveRecord]. Metode tersebut
mengembalikan array dari konfigurasi hubungan. Setiap elemen array mewakili
satu hubungan dengan format berikut:

~~~
[php]
'VarName'=>array('RelationType', 'ClassName', 'ForeignKey', ...opsi tambahan)
~~~

dengan `VarName` sebagai nama hubungan/relasi; `RelationType` menetapkan jenis
hubungan yang bisa berupa salah satu dari empat konstan:
`self::BELONGS_TO`, `self::HAS_ONE`, `self::HAS_MANY` dan
`self::MANY_MANY`; `ClassName` adalah nama kelas AR terkait dengan
kelas AR ini; dan `ForeignKey` menetapkan kunci asing yang terkait dalam
hubungan. Opsi tambahan dapat ditetapkan di akhir setiap relasi
(dijelaskan nanti).

Kode berikut memperlihatkan bagaimana kita mendeklarasikan hubungan kelas `User`
dan `Post`.

~~~
[php]
class Post extends CActiveRecord
{
	......

	public function relations()
	{
		return array(
			'author'=>array(self::BELONGS_TO, 'User', 'author_id'),
			'categories'=>array(self::MANY_MANY, 'Category',
				'tbl_post_category(post_id, category_id)'),
		);
	}
}

class User extends CActiveRecord
{
	......

	public function relations()
	{
		return array(
			'posts'=>array(self::HAS_MANY, 'Post', 'author_id'),
			'profile'=>array(self::HAS_ONE, 'Profile', 'owner_id'),
		);
	}
}
~~~

> Info: Foreign key bisa saja berupa composite, yang artinya terdiri dari dua atau lebih kolom.
Untuk hal ini, kita harus menggabungkan nama-nama kolom kunci dan
memisahkannya dengan koma, atau dengan bentuk array seperti array('key1','key2').
Apabila Anda perlu menspesifikasikan asosiasi PK->FK sendiri, Anda bisa menulis 
array('fk'=>'pk').Untuk composite maka bentuknya akan berupa array('fk_c1'=>'pk_c1','fk_c2'=>'pk_c2').
Untuk jenis hubungan `MANY_MANY`,
nama tabel asosiatif juga harus ditetapkan dalam foreign key. Contohnya,
hubungan `categories` dalam `Post` ditetapkan dengan foreign key
`tbl_post_category(post_id, category_id)`.
Deklarasi hubungan dalam kelas AR secara implisit menambahkan properti
ke kelas untuk setiap hubungan. Setelah query relasional dilakukan,
properti terkait akan diisi dengan instance dari AR yang dihubungkan.
Sebagai contoh, jika `$author` mewakili turunan AR `User`, kita
bisa menggunakan `$author->posts` untuk mengakses kaitannya dengan turunan `Post`.

Melakukan Query Relasional
--------------------------

Cara termudah melakukan query relasional adalah dengan membaca properti
relasional turunan AR. Jika properti tidak diakses sebelumnya, query
relasional akan diinisiasi, yang menggabungkan dua tabel terkait dan filter
dengan primary key pada instance dari AR saat ini. Hasil query akan disimpan
ke properti sebagai instance kelas AR terkait. Proses ini dikenal sebagai
pendekatan *lazy loading*, contohnya, query relasional dilakukan hanya
saat obyek terkait mulai diakses. Contoh di bawah memperlihatkan
bagaimana menggunakan pendekatan ini:

~~~
[php]
// ambil tulisan di mana ID adalah 10
$post=Post::model()->findByPk(10);
// ambil penulis tulisan: query relasional akan dilakukan di sini
$author=$post->author;
~~~

> Info: Jika tidak ada instance terkait pada hubungan, properti
bersangkutan dapat berupa null atau array kosong. Untuk hubungan
`BELONGS_TO` dan `HAS_ONE`, hasilnya adalah null; untuk hubungan
`HAS_MANY` dan `MANY_MANY`, hasilnya adalah array kosong.
Catatan bahwa hubungan `HAS_MANY` dan `MANY_MANY` mengembalikan array obyek,
Anda harus melakukan loop melalui hasilnya sebelum mencoba untuk mengakses setiap propertinya.
Kalau tidak, Anda akan menerima pesan kesalahan "Trying to get property of non-object" ("Mencoba untuk mendapatkan properti non-obyek").

Pendekatan lazy loading sangat nyaman untuk dipakai, tetapi tidak efisien
dalam beberapa skenario. Sebagai contoh, jika kita ingin mengakses informasi
author (pengarang) untuk `N` post, menggunakan pendekatan lazy akan menyertakan eksekusi
`N` query join. Kita harus beralih ke apa yang disebut pendekatan *eager loading*
dlam situasi seperti ini.

Pendekatan eager loading mengambil instance AR terkait bersama
dengan instance utama AR. Ini dilakukan dengan menggunakan metode
[with()|CActiveRecord::with] bersama dengan salah satu metode
[find|CActiveRecord::find] atau [findAll|CActiveRecord::findAll] dalam
AR. Sebagai contoh,

~~~
[php]
$posts=Post::model()->with('author')->findAll();
~~~

Kode di atas akan mengembalikan sebuah array turunan `Post`. Tidak seperti pendekatan
lazy, properti `author` dalam setiap instance `Post` sudah diisi dengan
instance `User` terkait sebelum kita mengakses properti.
Alih-alih menjalankan query join (gabungan) untuk setiap post, pendekatan eager loading
membawa semua post bersama dengan author-nya ke dalam satu query join (gabungan)!

Kita dapat menetapkan nama multipel relasi dalam metode
[with()|CActiveRecord::with] dan pendekatan eager loading akan mengembalikan
semuanya dalam satu pekerjaan. Sebagai contoh, kode berikut akan mengembalikan
post bersama dengan author dan category-nya (kategorinya):

~~~
[php]
$posts=Post::model()->with('author','categories')->findAll();
~~~

kita juga bisa melakukan nested eager loading. Alih-alih mendaftar nama-nama
relation, kita mengopernya dalam penyajian hirarki nama relasi ke method
[with()|CActiveRecord::with], seperti berikut,

~~~
[php]
$posts=Post::model()->with(
	'author.profile',
	'author.posts',
	'categories')->findAll();
~~~

Contoh di atas akan mengembalikan semua post bersama dengan author dan
category-nya. Ini juga akan menghasilkan profil setiap author serta post.

Eager loading dapat dijalankan dengan menspesifikasi properti
[CDbCriteria::with], seperti di bawah ini:

~~~
[php]
$criteria=new CDbCriteria;
$criteria->with=array(
	'author.profile',
	'author.posts',
	'categories',
);
$posts=Post::model()->findAll($criteria);
~~~

atau

~~~
[php]
$posts=Post::model()->findAll(array(
	'with'=>array(
		'author.profile',
		'author.posts',
		'categories',
	)
));
~~~


Melakukan query relasional tanpa mengambil model bersangkutan
---------------------------------------------------------

Terkadang kita perlu melakukan query dengan menggunakan relasi tetapi tidak
ingin mengambil model bersangkutan. Misalnya, kita memiliki `User` yang memposting beberapa `Post`. Post dapat dipublikasi
tetapi juga dapat dalam status draft. Status ini ditentukan oleh field `published` pada
model post. Sekarang kita ingin mengambil semua user yang telah mempublikasikan post dan kita
sendiri tidak tertarik pada post-post mereka. Maka, kita dapat mengambilnya dengan cara demikian:

~~~
[php]
$users=User::model()->with(array(
	'posts'=>array(
		//kita tidak ingin men-select post
		'select'=>false,
		// tetapi hanya ingin mengambil user yang telah publikasi post
		'joinType'=>'INNER JOIN',
		'condition'=>'posts.published=1',
	),
))->findAll();
~~~


Opsi Query Relasional
---------------------

Telah kita sebutkan bahwa opsi tambahan dapat dispesifikasi dalam deklarasi relasi.
Opsi ini ditetapkan sebagai pasangan name-value (nama-nilai), dipakai untuk mengkustomisasi
query relasional. Rangkumannya adalah sebagai berikut.

   - `select`: daftar kolom yang dipilih untuk kelas AR terkait.
Standarnya adalah '*', yang artinya semua kolom. Nama-nama kolom yang direferensi dalam
opsi ini tidak boleh ambigu.

   - `condition`: klausul `WHERE`. Default-nya kosong. Nama kolom yang
direferensikan di opsi ini juga tidak boleh ambigu.

   - `params`: parameter yang diikat ke pernyataan SQL yang dibuat.
Ini harus berupa array (larik) pasangan name-value.

   - `on`: klausul `ON`. Kondisi yang ditetapkan di sini akan ditambahkan ke
kondisi penggabungan menggunakan operator `AND`. Nama kolom direferensikan
dalam opsi ini tidak boleh ambigu.
Opsi ini tidak berlaku pada relasi `MANY_MANY`. 

   - `order`: klausul `ORDER BY`. Default-nya kosong. Nama kolom yang digunakan
di sini tidak boleh ambigu.

   - `with`: daftar dari child (turunan) objek terkait yang harus diambil bersama dengan
objek ini. Harap berhati-hati, salah menggunakan opsi ini akan mengakibatkan
pengulangan tanpa akhir.

   - `joinType`: jenis gabungan untuk relasi ini. Standarnya `LEFT
OUTER JOIN`.

   - `alias`: alias untuk tabel terkait dengan hubungan ini.
Default-nya null, yang berarti alias tabel sama dengan nama relasi.

   - `together`: menentukan apakah tabel yang terkait dengan hubungan ini harus
dipaksa untuk bergabung bersama dengan tabel primer dan tabel lainnya.
Opsi ini hanya berarti untuk relasi HAS_MANY dan MANY_MANY.
Jika opsi ini disetel false, setiap relasi yang berelasi HAS_MANY atau MANY_MANY
akan digabungkan dengan tabel utama dalam query SQL yang terpisah, yang artinya dapat
meningkatkan performa keseluruhan karena duplikasi pada data yang dihasilkan akan lebih sedikit.
Jika opsi ini di-set true, maka tabel yang diasosiasi akan selalu di-join dengan tabel primer dalam satu
query, walaupun jika tabel primer tersebut terpaginasi (paginated).
Jika opsi ini tidak di-set, maka tabel yang terasosiasi akan di-join dengan tabel primer ke dalam query SQL tunggal
hanya ketika tabel primer tidak terpaginasi.
Untuk info selengkapnya, silahkan melihat bagian "Relational Query Performance".

   - `join`: klausul `JOIN` ekstra. Secara default kosong. Opsi ini sudah
ada semenjak versi 1.1.3

   - `group`: klausul `GROUP BY`. Default-nya kosong. Nama kolom
yang direferensi ke dalam opsi ini tidak boleh ambigu.

   - `having`: klausul `HAVING`. Default-nya kosong. Nama kolom
yang direferensi dalam opsi tidak boleh ambigu.

   - `index`: nama kolom yang nilainya harus dipakai sebagai key (kunci)
array yang menyimpan obyek terkait. Tanpa menyetel opsi ini,
array obyek terkait akan menggunakan indeks integer berbasis-nol.
Opsi ini hanya bisa disetel untuk relasi `HAS_MANY` dan `MANY_MANY`.

   - `scopes`: nama scope yang ingin diaplikasikan. Jika ingin satu scope dapat
digunakan seperti `'scopes'=>'scopeName'`. Dan jika lebih dari satu scope maka dapat
digunakan seperti `'scopes'=>array('scopeName1','scopeName2')`. Opsi ini sudah ada
mulai dari versi 1.1.9.

Sebagai tambahan, opsi berikut tersedia untuk relasi tertentu
selama lazy loading:

   - `limit`: batas baris yang dipilih. Opsi ini TIDAK berlaku pada
relasi `BELONGS_TO`.

   - `offset`: offset baris yang dipilih. opsi ini TIDAK berlaku pada
relasi `BELONGS_TO`.

   - `through`: Nama dari relasi model yang digunakan sebagai penghubung ketika
mengambil data relasi. Hanya bisa di-set untuk `HAS_ONE` dan `HAS_MANY`.
Opsi ini sudah ada mulai dari versi 1.1.7.

Di bawah ini kita memodifikasi deklarasi relasi `posts` dalam `User` dengan
menyertakan beberapa opsi di atas

~~~
[php]
class User extends CActiveRecord
{
	public function relations()
	{
		return array(
			'posts'=>array(self::HAS_MANY, 'Post', 'authorID'
							'order'=>'??.createTime DESC',
							'with'=>'categories'),
			'profile'=>array(self::HAS_ONE, 'Profile', 'ownerID'),
		);
	}
}
~~~

Sekarang jika kita mengakses `$author->posts`, kita akan mendapatkan post-post dari sang author
yang tersusun berdasarkan waktu pembuatan secara terbalik (descending). Setiap instance post
juga me-load category-nya.


Membedakan Nama Kolom
---------------------

Ketika terdapat dua buah tabel atau lebih yang di-join ternyata memiliki
sebuah kolom dengan nama yang sama, maka kita perlu membedakannya. Kita dapat
melakukannya dengan menambah awalan pada nama kolom dengan nama alias tabel.

Dalam query AR relasi, nama alias untuk tabel utama ditetapkan sebagai `t`,
sedangkan nama alias untuk sebuah tabel relasi adalah sama dengan nama relasi
secara default. Misalnya, pada statement berikut, nama alias untuk
`Post` adalah `t` dan untuk `Comment` adalah
`comments`:

~~~
[php]
$posts=Post::model()->with('comments')->findAll();
~~~

Sekarang kita asumsi masing-masing `Post` dan `Comment` memiliki sebuah kolom bernama `create_time` yang
menunjukkan waktu pembuatan post atau komentar, dan kita ingin mengambil nilai post beserta komentar-komentar
dengan mengurutkan waktu pembuatan post baru kemudian waktu pembuatan komentar. Kita harus membedakan
kolom `create_time` dengan kode berikut : 

~~~
[php]
$posts=Post::model()->with('comments')->findAll(array(
	'order'=>'t.create_time, comments.create_time'
));
~~~


Opsi Query Relasional Dinamis
-----------------------------

Kita dapat menggunakan opsi query relasional dinamis baik dalam
[with()|CActiveRecord::with] maupun opsi `with`. Opsi dinamis akan
menimpa opsi yang sudah ada seperti yang ditetapkan pada metode [relations()|CActiveRecord::relations].
Sebagai contoh, dengan model `User` di atas, jika kita ingin menggunakan pendekatan eager
loading untuk membawa kembali tulisan milik author (penulis) dalam *urutan membesar/ascending*
(opsi `order` dalam spesifikasi relasi adalah urutan mengecil/descending ), kita dapat
melakukannya seperti berikut:

~~~
[php]
User::model()->with(array(
	'posts'=>array('order'=>'posts.create_time ASC'),
	'profile',
))->findAll();
~~~

Opsi query dinamis juga dapat dipakai saat menggunakan pendekatan  lazy loading untuk melakukan query relasional. Untuk mengerjakannya, kita harus memanggil metode yang namanya sama dengan nama relasi dan mengoper opsi query dinamis sebagai parameter metode. Sebagai contoh, kode berikut mengembalikan tulisan pengguna yang memiliki `status` = 1:

~~~
[php]
$user=User::model()->findByPk(1);
$posts=$user->posts(array('condition'=>'status=1'));
~~~


Performa Query Relasi
---------------------

Seperti yang dijelaskan di atas, pendekatan eager loading sering dipakai dalam skenario
yang berkenan pada pengaksesan banyak objek yang berhubungan. Eager loading menghasilkan
sebuah statement SQL kompleks dengan menggabungkan semua tabel yang diperlukan. Statement SQL
yang besar lebih dipilih dalam beberapa kasus karena menyederhanakan pemfilteran berdasarkan
kolom pada tabel yang ter-relasi.
Namun, bisa saja untuk kasus-kasus tertentu cara tersebut tidak efisien.

Bayangkan sebuah contoh, kita perlu mencari post terbaru bersama dengan komentar-komentar post tersebut.
Asumsi setiap post memiliki 10 komentar, menggunakan sebuah statement SQL besar, akan mengembalikan
data post yang redundan banyak sekali dikarenakan setiap post akan mengulangi setiap komentar yang dimilikinya.
Mari kita menggunakan pendekatan lain: pertama kita melakukan query pertama untuk post terbaru, dan kemudian
kueri komentarnya. Pada pendekatan ini, kita perlu mengeksekusi dua statement SQL. Manfaat dari pendekatan ini
adalah tidak ada redudansi sama sekali dalam hasilnya.

Jadi pendekatan mana yang lebih efisien? Sebetulnya tidak ada jawaban pasti. Mengeksekusi sebuah statemen SQL besar
mungkin lebih efisien karena mengurangi overhead pada DBMS untuk parsing dan executing pada statement SQL. Di lain pihak,
menggunakan statement SQL tunggal, kemungkinan akan menghasilkan data redundansi sehingga perlu waktu lebih lama untuk baca dan memprosesnya.

Oleh karena itu, Yii menyediakan opsi query `together` sehingga kita dapat memilih di antara dua pendekatan ini jika diperlukan.
Secara default, Yii menggunakan eager loading, seperti men-generate sebuah statement SQL tunggal, kecuali terdapat `LIMIT` di dalam model utama.
Kita dapat mengeset opsi `together` di dalam deklarasi relasi menjadi true untuk memaksakan statement SQL walaupun `LIMIT` digunakan.
Mengeset menjadi false, akan menyebabkan beberapa tabel akan di-join di statement SQL terpisah. Misalnya, untuk menggunakan statement SQL terpisah
untuk mengquery post terbaru dengan komentar-komentarnya, kita dapat mendeklarasikan relasi `comments` 
di dalam kelas `Post` sebagai berikut

~~~
[php]
public function relations()
{
	return array(
		'comments' => array(self::HAS_MANY, 'Comment', 'post_id', 'together'=>false),
	);
}
~~~

Kita juga dapat mengeset opsi ini secara dinamis ketika melakukan eager loading:

~~~
[php]
$posts = Post::model()->with(array('comments'=>array('together'=>false)))->findAll();
~~~


Query Statistik
---------------

Selain query yang dijelaskan di atas, Yii juga mendukung apa yang disebut query statistik (atau query agregasional). Maksud dari query statistik adalah pengambilan informasi agregasional mengenai objek terkait, seperti jumlah komentar untuk setiap tulisan, rata-rata peringkat setiap produk, dll. Query statistik hanya bisa dilakukan untuk obyek terkait dalam `HAS_MANY` (misalnya sebuah tulisan memiliki banyak komentar) atau `MANY_MANY` (misalnya tulisan milik banyak kategori dan kategori memiliki banyak tulisan).

Melakukan query statistik sangat mirip dengan melakukan query relasional seperti dijelaskan sebelumnya. Pertama kita perlu mendeklarasikan query statistik dalam metode [relations()|CActiveRecord::relations] pada [CActiveRecord] seperti yang kita lakukan dengan query relasional.

~~~
[php]
class Post extends CActiveRecord
{
	public function relations()
	{
		return array(
			'commentCount'=>array(self::STAT, 'Comment', 'postID'),
			'categoryCount'=>array(self::STAT, 'Category', 'PostCategory(postID, categoryID)'),
		);
	}
}
~~~

Dalam contoh di atas, kita mendeklarasikan dua query statistik: `commentCount` menghitung jumlah komentar milik sebuah post, dan `categoryCount` menghitung jumlah kategori di mana post tersebut berada. Catatan bahwa hubungan antara `Post` dan `Comment` adalah `HAS_MANY`, sementara hubungan `Post` dan `Category` adalah `MANY_MANY` (dengan menggabung tabel `PostCategory`). Seperti yang bisa kita lihat, deklarasi sangat mirip dengan relasi yang kita lihat dalam subbagian sebelumnya. Perbedaannya jenis relasinya adalah `STAT` di sini.


Dengan deklarasi di atas, kita dapat mengambil sejumlah komentar untuk sebuah tulisan menggunakan ekspresi `$post->commentCount`. Ketika kita mengakses properti ini untuk pertama kalinya, pernyataan SQL akan dijalankan secara implisit untuk mengambil hasil terkait. Seperti yang sudah kita ketahui, ini disebut pendekatan *lazy loading*. Kita juga dapat menggunakan pendekatan *eager loading* jika kita harus menentukan jumlah komentar untuk multipel tulisan:

~~~
[php]
$posts=Post::model()->with('commentCount', 'categoryCount')->findAll();
~~~

Pernyataan di atas akan menjalankan tiga SQL untuk menghasilkan semua post bersama dengan jumlah komentar dan jumlah kategorinya. Menggunakan pendekatan lazy loading, kita akan berakhir dengan `2*N+1` query SQL jika ada `N` post.

Secara default, query statistik akan menghitung ekspresi `COUNT` (dan selanjutnya jumlah komentar dan jumlah kategori dalam contoh di atas). Kita dapat mengkustomisasinya dengan menetapkan opsi tambahan saat mendeklarasikannya dalam [relations()|CActiveRecord::relations]. Opsi yang tersedia diringkas seperti berikut.

   - `select`: ekspresi statistik. Standarnya `COUNT(*)`, yang berarti jumlah turunan objek.

   - `defaultValue`: nilai yang diberikan ke record bersangkutan yang tidak menerima hasil query statistik. Sebagai contoh, jika ada sebuah post tidak memiliki komentar apapun, `commentCount` akan menerima nilai ini. Nilai standar untuk opsi ini adalah 0.

   - `condition`: klausul `WHERE`. Default-nya kosong.

   - `params`: parameter yang diikat ke pernyataan SQL yang dibuat.
Ini harus berupa array pasangan nama-nilai.

   - `order`: klausul `ORDER BY`. Default-nya kosong.

   - `group`: klausul `GROUP BY`. Default-nya kosong.

   - `having`: klausul `HAVING`. Default-nya kosong.


Query Relasional dengan Named Scope
---------------------------------------

Query relasional juga dapat dilakukan dengan kombinasi [named scope](/doc/guide/database.ar#named-scopes). Named scope pada tabel relasional datang dengan dua bentuk. Dalam bentuk pertama, named scope diterapkan ke model utama. Dalam bentuk kedua, named scope diterapkan ke model terkait.

Kode berikut memperlihatkan bagaimana untuk menerapkan named scope ke model utama.

~~~
[php]
$posts=Post::model()->published()->recently()->with('comments')->findAll();
~~~

Ini sangat mirip dengan query non-relasional. Perbedaannya hanyalah bahwa kita memiliki panggilan `with()` setelah rantai named-scope. Query ini akan membawa kembali post terbaru yang dipublikasikan bersama dengan komentar-komentarnya.

Kode berikut memperlihatkan bagaimana untuk menerapkan named scope ke model bersangkutan.

~~~
[php]
$posts=Post::model()->with('comments:recently:approved')->findAll();
// atau kalau mulai dari 1.1.7
$posts=Post::model()->with(array(
    'comments'=>array(
        'scopes'=>array('recently','approved')
    ),
))->findAll();
// or semenjak 1.1.7
$posts=Post::model()->findAll(array(
    'with'=>array(
        'comments'=>array(
            'scopes'=>array('recently','approved')
        ),
    ),
));
~~~

Query di atas akan membawa kembali semua tulisan bersama dengan komentarnya yang sudah disetujui. Catatan bahwa `comments` merujuk ke nama relasi, sementara `recently` dan `approved` merujuk ke dua named scope yang dideklarasikan dalam kelas model `Comment`. Nama relasi dan named scope harus dipisahkan dengan titik dua.

Named scope dapat ditetapkan dalam opsi `with` pada aturan relasional yang dideklarasikan dalam [CActiveRecord::relations()]. Dalam contoh berikut, jika kita mengakses `$user->posts`, maka akan mengembalikan semua komentar yang *disetujui* pada tulisan.

~~~
[php]
class User extends CActiveRecord
{
	public function relations()
	{
		return array(
			'posts'=>array(self::HAS_MANY, 'Post', 'author_id',
				'with'=>'comments:approved'),
		);
	}
}

// atau semenjak 1.1.7
class User extends CActiveRecord
{
	public function relations()
	{
		return array(
		    'posts'=>array(self::HAS_MANY, 'Post', 'author_id',
				'with'=>array(
					'comments'=>array(
						'scopes'=>'approved'
					),
				),
			),
		);
	}
}
~~~

> Note|Catatan: Sebelum versi 1.1.7, named scope diaplikasikan ke model yang berelasi harus ditentukan di CActiveRecord::scopes.
> Oleh karenanya, tidak dapat diberi parameter.

Mulai dari versi 1.1.7, Yii memungkinkan passing parameter untuk relational named scopes.
Misalnya, jika anda memiliki scope bernama `rated` di dalam `Post` yang menerima rating
minimal post, Anda dapat menggunakannya dari `User` dengan cara berikut:

~~~
[php]
$users=User::model()->findAll(array(
	'with'=>array(
		'posts'=>array(
			'scopes'=>array(
				'rated'=>5,
			),
		),
	),
));
~~~

Query Relasional dengan through
-----------------------------

Ketika menggunakan `through`, definisi relasi harus dilihat seperti berikut:

~~~
[php]
'comments'=>array(self::HAS_MANY,'Comment',array('key1'=>'key2'),'through'=>'posts'),
~~~

Di atas, nilai `foreign_key` adalah nama sebuah key yang:

  - `key1` merupakan key yang didefinisikan di `throughRelationName`.
  - `key2` merupakan key yang didefinisikan di `ClassName`.

`through` dapat digunakan oleh relasi `HAS_ONE` dan `HAS_MANY`.


### HAS_MANY through

![HAS_MANY through ER](has_many_through.png)

Contoh `HAS_MANY` dengan `through` adalah mendapatkan user dari sebuah grup ketika
user-user dimasukkan ke group tertentu melalui roles.

Contoh yang lebih kompleks misalnya mengambil semua komentar dari semua user 
dalam grup tertentu. Pada kasus ini kita harus menggunakan beberapa relasi melalui `through` dalam satu model:

~~~
[php]
class Group extends CActiveRecord
{
   ...
   public function relations()
   {
       return array(
           'roles'=>array(self::HAS_MANY,'Role','group_id'),
           'users'=>array(self::HAS_MANY,'User',array('user_id'=>'id'),'through'=>'roles'),
           'comments'=>array(self::HAS_MANY,'Comment',array('id'=>'user_id'),'through'=>'users'),
       );
   }
}
~~~

#### Contoh Penggunaan

~~~
[php]
// mendapatkan semua grup dengan usernya
$groups=Group::model()->with('users')->findAll();

// mendapatkan semua group dengan usernya dan role-nya.
$groups=Group::model()->with('roles','users')->findAll();

// mendapatkan semua user dan role yang ID grup-nya 1
$group=Group::model()->findByPk(1);
$users=$group->users;
$roles=$group->roles;

// mendapatkan semua komentar yang grup ID-nya 1
$group=Group::model()->findByPk(1);
$comments=$group->comments;
~~~


### HAS_ONE through

![HAS_ONE through ER](has_one_through.png)

Contoh penggunaan `HAS_ONE` dengan `through` adalah mendapatkan alamat user
di mana user diikat dengan address menggunakan profile. Semua entiti ini (user, profile, address)
masing-masing memiliki model:

~~~
[php]
class User extends CActiveRecord
{
   ...
   public function relations()
   {
       return array(
           'profile'=>array(self::HAS_ONE,'Profile','user_id'),
           'address'=>array(self::HAS_ONE,'Address',array('id'=>'profile_id'),'through'=>'profile'),
       );
   }
}
~~~

#### Contoh penggunaan

~~~
[php]
// mendapatkan alamat user yang ID-nya 1
$user=User::model()->findByPk(1);
$address=$user->address;
~~~


### through pada diri sendiri

`through` dapat digunakan pada model yang diikat diri sendiri dengan menggunakan sebuah model penghubung. Pada kasus
kita, seorang user merupakan mentor bagi user lain:


![through self ER](through_self.png)


Beginilah kita mendefinisikan relasi pada kasus ini:

~~~
[php]
class User extends CActiveRecord
{
   ...
   public function relations()
   {
       return array(
           'mentorships'=>array(self::HAS_MANY,'Mentorship','teacher_id','joinType'=>'INNER JOIN'),
           'students'=>array(self::HAS_MANY,'User',array('student_id'=>'id'),'through'=>'mentorships','joinType'=>'INNER JOIN'),
       );
   }
}
~~~

#### Contoh Penggunaan

~~~
[php]
// Dapatkan semua siswa yang diajarkan guru dengan ID 1
$teacher=User::model()->findByPk(1);
$students=$teacher->students;
~~~

<div class="revision">$Id: database.arr.txt 3416 2011-10-13 18:18:13Z alexander.makarow $</div>