Layanan Web
===========

[Layanan Web](http://en.wikipedia.org/wiki/Web_service) adalah sistem software
yang didesain untuk mendukung interaksi interoperable mesin-ke-mesin
melalui sebuah jaringan. Dalam konteks aplikasi Web, ia biasanya merujuk
ke satu set API yang dapat diakses melalui Internet dan menjalankan
layanan di hosting sitem remote. Sebagai contoh,
klien berbasis-[Flex](http://www.adobe.com/products/flex/) dapat memanggil
fungsi yang diimplementasikan pada sisi server yang menjalankan aplikasi berbasis-PHP.
Layanan Web bergantung
pada [SOAP](http://en.wikipedia.org/wiki/SOAP)
sebagai lapisan dasar tumpukan protokol komunikasinya.

Yii menyediakan [CWebService] dan [CWebServiceAction] untuk menyederhanakan pekerjaan
implementasi layanan Web dalam aplikasi Web. APIs dikelompokkan ke dalam
kelas, disebut *service providers*. Yii akan membuat sebuah
spesifikasi [WSDL](http://www.w3.org/TR/wsdl) untuk setiap kelas yang menjelaskan
API apa yang bersifat variabel dan bagaimana ia harus dipanggil oleh klien. Ketika
sebuah API dipanggil oleh klien, Yii akan menginisiasi penyedia layanan terkait
dan memanggil API yang diminta guna memenuhi permintaan.

> Note|Catatan: [CWebService] bergantung pada [PHP SOAP
extension](http://www.php.net/manual/en/ref.soap.php). Pastikan Anda menghidupkannya
sebelum mencoba menampilkan contoh dalam seksi ini.

Mendefinisikan Penyedia Layanan
-------------------------------

Seperti sudah disebutkan di atas, penyedia layanan adalah kelas yang mendefinisikan metode
yang dapat secara remote (jarak jauh) dipanggil. Yii bergantung pada [komentar 
dokumen](http://java.sun.com/j2se/javadoc/writingdoccomments/) dan [kelas 
refleksi](http://www.php.net/manual/en/language.oop5.reflection.php) dalam
mengidentifikasi metode mana yang bisa dipanggil secara remote dan parameter
apa serta nilai balik.

Mari kita mulai dengan kutipan layanan stok. Layanan ini mengijinkan klien
untuk meminta kutipan stok yang sudah ditetapkan. Kita mendefinisikan penydia
layanan sebagai berikut. Catatan bahwa kita mendefinisikan kelas penyedia
`StockController` dengan memperluas [CController]. Ini tidak diperlukan. Kami akan
segera menjelaskan mengapa kita melakukannya.

~~~
[php]
class StockController extends CController
{
	/**
	 * @param string the symbol of the stock
	 * @return float the stock price
	 * @soap
	 */
	public function getPrice($symbol)
	{
		$prices=array('IBM'=>100, 'GOOGLE'=>350);
		return isset($prices[$symbol])?$prices[$symbol]:0;
	    //...return stock price for $symbol
	}
}
~~~

Dalam contoh di atas, kita mendeklarasikan metode `getPrice` menjadi API layanan Web,
menandainya dengan tag `@soap` dalam komentar dokumen. Kita bergantung pada komentar dokumen
untuk menetapkan jenis data parameter input dan nilai hasil.
API tambahan dapat dideklarasikan dengan cara yang sama.

Mendeklarasikan Aksi Layanan Web
--------------------------------

Setelah mendefinisikan penyedia layanan, kita harus membuatnya tersedia bagi
klien. Sebenarnya kita ingin membuat aksi controller untuk mengekspos layanan.
Ini bisa dilakukan secara mudah dengan mendeklarasikan aksi [CWebServiceAction] dalam
kelas controller. Dalam contoh kita, kita akan memasukkan
`StockController`.

~~~
[php]
class StockController extends CController
{
	public function actions()
	{
		return array(
			'quote'=>array(
				'class'=>'CWebServiceAction',
			),
		);
	}

	/**
	 * @param string the symbol of the stock
	 * @return float the stock price
	 * @soap
	 */
	public function getPrice($symbol)
	{
	    //...kembalikan harga stok untuk simbol $symbol
	}
}
~~~

Selesai, hanya itu yang kita perlukan dalam membuat layanan Web! Jika kita mencoba mengakses
aksi dengan URL `http://hostname/path/to/index.php?r=stock/quote`, kita akan
melihat banyak konten XML yang sebenarnya WSDL bagi layanan Web yang kita
definisikan.

> Tip: Secara default, [CWebServiceAction] menganggap controller saat ini adalah
penyedia layanan. Itulah mengapa kita mendefinisikan metode `getPrice` di dalam
kelas `StockController`.

Menerima Layanan Web
--------------------

Untuk melengkapi contoh, mari kita buat klien untuk menerima layanan Web
yang baru kita buat. Contoh klien ditulis dalam PHP, tapi ia dapat dibuat dalam
bahasa lainnya seperti `Java`, `C#`, `Flex`, dll.

~~~
[php]
$client=new SoapClient('http://hostname/path/to/index.php?r=stock/quote');
echo $client->getPrice('GOOGLE');
~~~

Jalankan skrip di atas baik dalam Web ataupun mode konsol, dan kita seharusnya melihat `350`
yang merupakan harga untuk `GOOGLE`.

Tipe Data
---------

Ketika mendeklarasikan metode kelas dan properti yang akan diakses secara remote, kita
harus menetapkan tipe data atas parameter input dan output. Tipe data
primitif berikut bisa dipakai:

   - str/string: dipetakan ke `xsd:string`;
   - int/integer: dipetakan ke `xsd:int`;
   - float/double: dipetakan ke `xsd:float`;
   - bool/boolean: dipetakan ke `xsd:boolean`;
   - date: dipetakan ke `xsd:date`;
   - time: dipetakan ke `xsd:time`;
   - datetime: dipetakan ke `xsd:dateTime`;
   - array: dipetakan ke `xsd:string`;
   - object: dipetakan ke `xsd:struct`;
   - mixed: dipetakan ke `xsd:anyType`.

Jika tipe bukan salah satupun dari tipe primitif di atas, ia dianggap sebagai
tipe composite yang terdiri dari properti. Tipe composite disajikan dalam
batasan kelas, dan propertinya adalah variabel anggota public kelas yang ditandai
dengan `@soap` dalam komentar dokumennya.

Kita juga bisa menggunakan tipe array dengan menambahkan `[]` di akhir tipe primitif atau
tipe composite. Ini akan menetapkan sebuah array pada tipe yang sudah ditetapkan tersebut.

Di bawah ini adalah contoh mendefinisikan Web API `getPosts` Web API yang menghasilkan array
obyek `Post`.

~~~
[php]
class PostController extends CController
{
	/**
	 * @return Post[] a list of posts
	 * @soap
	 */
	public function getPosts()
	{
		return Post::model()->findAll();
	}
}

class Post extends CActiveRecord
{
	/**
	 * @var integer post ID
	 * @soap
	 */
	public $id;
	/**
	 * @var string post title
	 * @soap
	 */
	public $title;

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
~~~

Pemetaan Kelas
--------------

Agar bisa menerima parameter tipe composite dari klien, aplikasi
harus mendeklarasikan pemetaan dari tipe  WSDL ke kelas PHP
terkait. Ini dilakukan dengan mengkonfigurasi properti
[classMap|CWebServiceAction::classMap] pada [CWebServiceAction].

~~~
[php]
class PostController extends CController
{
	public function actions()
	{
		return array(
			'service'=>array(
				'class'=>'CWebServiceAction',
				'classMap'=>array(
					'Post'=>'Post',  // atau cukup 'Post'
				),
			),
		);
	}
	......
}
~~~

Mengintersepsi Penyertaan Metode Remote
---------------------------------------

Dengan mengimplementasi antar muka [IWebServiceProvider], penyedia layanan bisa
mengintersepsi penyertaan metode jarak jauh. Dalam
[IWebServiceProvider::beforeWebMethod], penyedia bisa mengambil instance
[CWebService] saat ini dan mendapatkan nama metode yang saat ini sedang dipakai
via [CWebService::methodName]. Ini dapat menghasilkan false jika metode
remote seharusnya tidak dipanggil untuk beberapa alasan (misalnya
akses tidak diotorisasi).

<div class="revision">$Id: topics.webservice.txt 1808 2010-02-17 21:49:42Z qiang.xue $</div>