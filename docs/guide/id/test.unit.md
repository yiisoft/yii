Uji Coba Unit(Unit Testing)
===========================

Karena framework uji coba Yii dibuat dari [PHPUnit](http://www.phpunit.de/), maka Anda direkomendasi untuk membaca [dokumentasi PHPUnit](http://www.phpunit.de/manual/current/en/index.html) terlebih dahulu untuk memahami bagaimana menulis sebuah uji coba unit. Kita meringkaskan prinsip dasar dalam penulisan uji coba unit dalam Yii sebagai berikut:

 * Sebuah uji coba unit ditulis dalam bentuk kelas dengan aturan nama `XyzTest` yang diturunkan [CTestCase] atau [CDbTestCase]. `Xyz` merupakan nama kelas yang diuji coba. Misalnya, kita ingin menguji kelas `Post`, kita akan menamakan unit uji coba bersangkutan dengan `PostTest` sesuai aturan. Kelas dasar [CTestCase] ditujukan untuk uji coba unit yang umum, sedangkan [CDbTestCase] lebih cocok untuk uji coba kelas model [rekaman aktif(active record)](/doc/guide/database.ar). Karena `PHPUnit_Framework_TestCase` merupakan induk dari kedua kelas tersebut, kita dapat menggunakan semua metode yang diturunkan dari kelas ini.

 * Sebuah unit uji coba disimpan dalam file bernama `XyzTest.php`. Sesuai aturan, file uji coba hanya dapat disimpan dalam direktori `protected/tests/unit`.

 * Pada umumnya, kelas uji coba berisi serangkaian metode uji coba yang dinamakan `testAbc`, dengan `Abc` pada umumnya merupakan nama metode kelas yang ingin diuji.

 * Sebuah metode pengujian biasanya berisi serangkaian assertion statements(kalimat penuntutan) seperti `assertTrue`, `assertEquals` yang akan berfungsi sebagai cek poin dalam validasi perilaku kelas yang diuji.


Berikut ini, kita akan mempelajari bagaimana menulis sebuah uji coba unit untuk [rekaman aktif(active record)](/doc/guide/database.ar). Kita akan menurunkan kelas kita dari [CDbTestCase] karena kelas ini menyediakan dukungan fixture database yang sudah diperkenalkan di bab sebelumnya.

Asumsi kita ingin menguji kelas model `Comment` di dalam [blog demo](http://www.yiiframework.com/demos/blog/). Kita mulai dengan membuat sebuah kelas bernama `CommentTest` dan simpan sebagai `protected/tests/unit/CommentTest.php`:

~~~
[php]
class CommentTest extends CDbTestCase
{
	public $fixtures=array(
		'posts'=>'Post',
		'comments'=>'Comment',
	);

	......
}
~~~

Di dalam kelas, kita akan menspesifikasi variabel anggota `fixture` menjadi larik(array) yang menspesifikasi fixture yang mana akan digunakan oleh uji coba ini. Larik tersebut akan mewakili pemetaan dari nama fixture ke nama kelas model atau nama tabel fixture (misalnya dari nama fixture `posts` ke kelas model `Post`). Perhatikan bahwa pemetaan ke nama tabel fixture, kita harus mengawali nama tabel dengan titik dua (seperti `:Post`) untuk membedakan dari nama kelas model. Ketika menggunakan nama kelas model, tabel bersangkutan akan dianggap sebagai tabel fixture. Seperti yang dijelaskan sebelumnya, tabel fixture akan me-reset ke kondisi yang dikenali ketika metode uji coba dijalankan.

Nama fixture memungkinkan kita untuk mengakses data dalam metode uji coba dengan mudah. Berikut merupakan kode yang menunjukkan penggunaan umumnya:

~~~
[php]
// menampung seluruh baris dalam tabel fixture `Comment`
$comments = $this->comments;
// menampung nilai baris yang aliasnya `sample` dalam tabel fixture `Post`
$post = $this->posts['sample1'];
// menampung instance dari AR yang mewakili baris data fixture `sample1`
$post = $this->posts('sample1');
~~~

> Note|Catatan: Jika sebuah fixture dideklarasi dengan menggunakan nama tabel (misalnya `'posts'=>':Post'`), maka cara penggunaan di atas yang ketiga tidak akan valid karena kita tidak memiliki informasi mengenai kelas model pada tabel bersangkutan.

Selanjutnya kita menulis sebuah metode `testApprove` untuk menguji metode `approve` di kelas model `Comment`. Kode ini sangat mudah: pertama kita memasukkan sebuah comment yang statusnya pending; kemudian kita verifikasi bahwa comment ini dalam keadaan pending dengan mengambilnya dari database; dan terakhir kita memanggil metode `approve` dan memastikan bahwa statusnya sudah berubah menjadi sesuai harapan.

~~~
[php]
public function testApprove()
{
	// memasukkan sebuah comment dalam status pending
	$comment=new Comment;
	$comment->setAttributes(array(
		'content'=>'comment 1',
		'status'=>Comment::STATUS_PENDING,
		'createTime'=>time(),
		'author'=>'me',
		'email'=>'me@example.com',
		'postId'=>$this->posts['sample1']['id'],
	),false);
	$this->assertTrue($comment->save(false));

	// pastikan comment dalam keadaan pending
	$comment=Comment::model()->findByPk($comment->id);
	$this->assertTrue($comment instanceof Comment);
	$this->assertEquals(Comment::STATUS_PENDING,$comment->status);

	// panggil approve() dan verifikasi komentar dalam keadaan tersetujui
	$comment->approve();
	$this->assertEquals(Comment::STATUS_APPROVED,$comment->status);
	$comment=Comment::model()->findByPk($comment->id);
	$this->assertEquals(Comment::STATUS_APPROVED,$comment->status);
}
~~~


<div class="revision">$Id: test.unit.txt 2841 2011-01-12 21:04:12Z alexander.makarow $</div>