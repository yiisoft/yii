Uji Coba Fungsional(Functional Testing)
=======================================

Sebelum membaca bagian ini, Anda disarankan untuk membaca [dokumentasi Selenium](http://seleniumhq.org/docs/) dan [dokumentasi PHPUnit](http://www.phpunit.de/wiki/Documentation) terlebih dahulu. Kita merangkum beberapa prinsip dasar penulisan sebuah functional test(percobaan fungsional) dalam Yii menjadi:

 * Seperti halnya uji coba unit(unit test), sebuah uji coba fungsional(functional test) ditulis dalam bentuk sebuah kelas `XyzTest` yang diturunkan dari [CWebTestCase]. `Xyz` merupakan bagian dari kelas yang diuji coba. Dikarenakan `PHPUnit_Extensions_SeleniumTestCase` merupakan kelas induk dari [CWebTestCase], kita dapat menggunakan seluruh metode yang diturunkan oleh kelas ini.

 * Kelas uji coba fungsional disimpan dalam sebuah file PHP bernama `XyzTest.php`. Sesuai aturan, file uji coba fungsional disimpan di dalam direktori `protected/tests/functional`.

 * Kelas uji coba utamanya berisi sebuah rangkaian metode uji coba yang dinamakan sebagai `testAbc`. `Abc` merupakan nama fitur yang ingin diuji coba. Misalnya, untuk menguji fitur login pengguna, kita dapat memiliki sebuah metode uji coba yang dinamakan `testLogin`.

 * Sebuah metode uji coba berisi sebuah rentetan statement(pernyataan) yang akan mengeluarkan perintah ke Selenium RC untuk berinteraksi dengan aplikasi Web yang sedang diuji coba. Selain itu, juga berisi statement assertion (pernyataan tuntutan) untuk memastikan bahwa respon dari aplikasi Web sesuai harapan.

Sebelum kami menjelaskan bagaimana menulis sebuah uji coba fungsional, marilah melihat sebentar file `WebTestCase.php` yang dihasilkan oleh perintah `yiic webapp`. File ini mendefinisikan `WebTestCase` yang dapat berfungsi sebagai kelas dasar untuk semua kelas functional test.

~~~
[php]
define('TEST_BASE_URL','http://localhost/yii/demos/blog/index-test.php/');

class WebTestCase extends CWebTestCase
{
	/**
	 * Pengaturan awal sebelum tiap metode uji coba dijalankan.
	 * Di bawah ini set basis URL untuk uji coba aplikasi.
	 */
	protected function setUp()
	{
		parent::setUp();
		$this->setBrowserUrl(TEST_BASE_URL);
	}

	......
}
~~~

Kelas `WebTestCase` utamanya mengatur URL basis dari halaman yang diuji coba. Nantinya dalam metode uji coba, kita akan menggunakan URL relatif untuk menetapkan halaman mana yang ingin diuji coba.

Kita juga harus memperhatikan bahwa dalam URL tes dasar, kita dapat menggunakan `index-test.php` sebagai entry script(skrip awal) alih-alih menggunakan `index.php`. Satu-satunya perbedaan antara `index-test.php` dan `index.php` adalah `index-test.php` menggunakan `test.php` sebagai file konfigurasi aplikasi sedangkan `index.php` menggunakan `main.php`.

Sekarang kita akan melihat bagaimana menguji coba suatu fitur tentang menampilkan sebuah post dalam [blog demo](http://www.yiiframework.com/demos/blog). Pertama-tama kita menulis kelas uji coba sebagai berikut. Harap diperhatikan bahwa kelas uji coba diturunkan dari kelas dasar yang baru saja kita jelaskan:

~~~
[php]
class PostTest extends WebTestCase
{
	public $fixtures=array(
		'posts'=>'Post',
	);

	public function testShow()
	{
		$this->open('post/1');
	    // verifikasi judul sampel post ada
	    $this->assertTextPresent($this->posts['sample1']['title']);
	    // verifikasi form comment ada
	    $this->assertTextPresent('Leave a Comment');
	}

	......
}
~~~

Seperti menulis sebuah kelas uji coba unit, kita mendeklarasi fixture untuk digunakan dalam uji coba ini. Di sini kita mengindikasikan bahwa fixture `Post` harus digunakan. Dalam metode uji coba `tesetShow`, kita pertama-tama menginstruksikan Selenium RC membuka URL `post/1`. Perhatikan bahwa di sini digunakan URL relatif, dan URL lengkap terbentuk dari menambah URL basis yang kita set di kelas dasar ((i.e. `http://localhost/yii/demos/blog/index-test.php/post/1`). Kita kemudian memastikan bahwa kita dapat mencari post dengan judul `sample1` dapat ditemukan dalam halaman halaman Web sekarang. Dan kita juga memverifikasi bahwa halaman ini mengandung teks 'Leave a Comment'

> Tip: Sebelum menjalankan uji coba fungsional, server Selenium-RC harus dinyalakan. Untuk menyalakannya dapat dilakukan dengan menjalankan perintah `java -jar selenium-server.jar` di dalam direktori instalasi server Selenium.

<div class="revision">$Id: test.functional.txt 1662 2010-01-04 19:15:10Z qiang.xue $</div>