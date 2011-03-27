Data Access Objects (DAO)
========================

Data Access Objects (DAO) atau Objek Akses Data menyediakan API generik untuk mengakses data yang disimpan dalam
sistem manajemen database (DBMS) yang berbeda. Hasilnya, Lapisan DBMS
dapat diubah ke yang lain yang berbeda tanpa memerlukan perubahan kode
yang menggunakan DAO untuk mengakses data.

Yii DAO dibangun di atas [PHP Data Objects
(PDO)](http://php.net/manual/en/book.pdo.php) yang merupakan extension
yang menyediakan akses data gabungan untuk beberapa DBMS populer, seperti MySQL,
PostgreSQL. Oleh karena itu, untuk menggunakan Yii DAO, extension PDO dan driver database
PDO tertentu (misalnya `PDO_MYSQL`) harus sudah terinstal.

Yii DAO terdiri dari empat kelas utama sebagai berikut:

   - [CDbConnection]: mewakili koneksi ke database.
   - [CDbCommand]: mewakili pernyataan SQL untuk dijalankan pada database.
   - [CDbDataReader]: mewakili forward-only stream terhadap baris dari set hasil query.
   - [CDbTransaction]: mewakili transaksi DB.

Berikutnya, kami memperkenalkan pemakaian Yii DAO dalam skenario
berbeda.

Membuat Koneksi Database
------------------------

Untuk membuat koneksi database, buat instance [CDbConnection] dan mengaktifkannya.
Nama sumber data (DSN) diperlukan untuk menetapkan informasi yang diperlukan
untuk menyambung ke database. Nama pengguna dan kata sandi juga diperlukan
guna melakukan koneksi. Exception akan dimunculkan seandainya kesalahan terjadi
selama pelaksanaan koneksi (misalnya DSN tidak benar atau username/password
tidak benar).

~~~
[php]
$connection=new CDbConnection($dsn,$username,$password);
// melakukan koneksi. Anda dapat mencoba try...catch exception yang mungkin
$connection->active=true;
......
$connection->active=false;  // tutup koneksi
~~~

Format DSN tergantung pada driver PDO database yang digunakan. Secara umum, DSN
terdiri dari nama driver PDO, diikuti oleh titik dua, diikuti oleh
sintaks koneksi spesifik-driver. Lihat [Dokumentasi
PDO](http://www.php.net/manual/en/pdo.construct.php) untuk informasi
lebih lengkap. Di bawah ini adalah daftar format DSN yang umum dipakai:

   - SQLite: `sqlite:/path/to/dbfile`
   - MySQL: `mysql:host=localhost;dbname=testdb`
   - PostgreSQL: `pgsql:host=localhost;port=5432;dbname=testdb`
   - SQL Server: `mssql:host=localhost;dbname=testdb`
   - Oracle: `oci:dbname=//localhost:1521/testdb`

Karena [CDbConnection] diturunkan dari [CApplicationComponent], kita juga dapat
menggunakannya sebagai [komponen
aplikasi](/doc/guide/basics.application#application-component). Untuk melakukannya, konfigurasi
dalam komponen aplikasi `db` (atau nama lain) pada [konfigurasi
aplikasi](/doc/guide/basics.application#application-configuration) sebagai berikut,

~~~
[php]
array(
	......
	'components'=>array(
		......
		'db'=>array(
			'class'=>'CDbConnection',
			'connectionString'=>'mysql:host=localhost;dbname=testdb',
			'username'=>'root',
			'password'=>'password',
			'emulatePrepare'=>true,  //  pada beberapa instalasi MySQL, diperlukan
		),
	),
)
~~~

Selanjutnya kita dapat mengakses koneksi DB via `Yii::app()->db` yang sudah
diaktifkan secara otomatis, kecuali dikonfigurasi secara eksplisit
[CDbConnection::autoConnect] menjadi false. Menggunakan pendekatan ini, koneksi DB
tunggal dapat dibagi dalam tempat multipel pada kode kita.

Menjalankan Pernyataan SQL
--------------------------

Setelah koneksi database terlaksana, pernyataan SQL dapat dijalankan
menggunakan [CDbCommand]. Membuat instance [CDbCommand] dengan memanggil
[CDbConnection::createCommand()] dengan pernyataan SQL yang ditetapkan:

~~~
[php]
$connection=Yii::app()->db;   // asumsi bahwa Anda memiliki koneksi "db" yang terkonfigurasi
// Jika tidak, Anda bisa membuat sebuah koneksi secara eksplisit
// $connection=new CDbConnection($dsn,$username,$password);
$command=$connection->createCommand($sql);
// jika diperlukan, statement SQL dapat diupdate sebagai berikut
// $command->text=$newSQL;
~~~

Pernyataan SQL dijalankan via [CDbCommand] dalam dua cara
berikut:

   - [execute()|CDbCommand::execute]: melakukan pernyataan SQL non-query,
seperti `INSERT`, `UPDATE` and `DELETE`. Jika berhasil, mengembalikan
sejumlah baris yang dipengaruhi oleh eksekusi

   - [query()|CDbCommand::query]: melakukan pernyataan SQL yang mengembalikan
baris data, seperti `SELECT`. Jika berhasil,mengembalikan instance [CDbDataReader]
yang dapat ditelusuri baris data yang dihasilkan. Untuk
kenyamanan, satu set metode `queryXXX()` juga diimplementasikan yang secara
langsung mengembalikan hasil query.

Exception akan dimunculkan jika kesalahan terjadi selama eksekusi pernyataan
SQL.

~~~
[php]
$rowCount=$command->execute();   // jalankan SQL non-query
$dataReader=$command->query();   // jalankan query SQL
$rows=$command->queryAll();      // query dan kembalikan seluruh baris hasil
$row=$command->queryRow();       // query dan kembalikan baris pertama hasil
$column=$command->queryColumn(); // query dan kembalikan kolom pertama hasil
$value=$command->queryScalar();  // query dan kembalikan field pertama dalam baris pertama
~~~

Mengambil Hasil Query
---------------------

Setelah [CDbCommand::query()] membuat instance [CDbDataReader], Anda
bisa mengambil baris data yang dihasilkan oleh pemanggilan [CDbDataReader::read()]
secara berulang. Ia juga dapat menggunakan [CDbDataReader] dalam konsrruksi bahasa PHP
`foreach` untuk mengambil baris demi baris.

~~~
[php]
$dataReader=$command->query();
// memanggil read() secara terus menerus sampai ia mengembalikan false
while(($row=$dataReader->read())!==false) { ... }
// menggunakan foreach untuk menelusuri setiap baris data
foreach($dataReader as $row) { ... }
// mengambil seluruh baris sekaligus dalam satu array tunggal
$rows=$dataReader->readAll();
~~~

> Note|Catatan: Tidak seperti [query()|CDbCommand::query], semua metode `queryXXX()`
mengembalikan data secara langsung. Sebagai contoh, [queryRow()|CDbCommand::queryRow]
mengembalikan array yang mewakili baris pertama pada hasil query.

Menggunakan Transaksi
---------------------

Ketika aplikasi menjalankan beberapa query, setiap pembacaan dan/atau penulisan
informasi dalam database, penting untuk memastikan bahwa database tidak
tertinggal dengan hanya beberapa query yang dihasilkan. Transaksi diwakili
oleh instance [CDbTransaction] dalam Yii, dapat diinisiasi dalam
hal:

   - Mulai transaksi.
   - Jalankan query satu demi satu. Setiap pemutakhiran pada database tidak terlihat bagi dunia luar.
   - Lakukan transaksi. Pemutakhiran menjadi terlihat jika transaksi berhasil.
   - Jika salah satu query gagal, seluruh transaksi dibatalkan.

Alur kerja di atas dapat diimplementasikan menggunakan kode berikut:

~~~
[php]
$transaction=$connection->beginTransaction();
try
{
	$connection->createCommand($sql1)->execute();
	$connection->createCommand($sql2)->execute();
	//.... eksekusi SQL lainnya
	$transaction->commit();
}
catch(Exception $e) // exception dimunculkan jika query gagal
{
	$transaction->rollBack();
}
~~~

Mengikat Parameter
------------------

Untuk menghindari [serangan injeksi
SQL](http://en.wikipedia.org/wiki/SQL_injection) dan meningkatkan
kinerja pelaksanaan pernyataan SQL secara terus menerus, Anda dapat "menyiapkan"
sebuah pernyataan SQL dengan opsional penampung parameter yang akan
diganti dengan parameter sebenarnya selama proses pengikatan parameter.

Penampung parameter dapat bernama (disajikan sebagai token
unik) ataupun tidak bernama (disajikan sebagai tanda tanya). Panggil
[CDbCommand::bindParam()] atau [CDbCommand::bindValue()] untuk mengganti penampung
ini dengan parameter sebenarnya. Parameter tidak harus bertanda
kutip: lapisan driver database melakukan ini bagi Anda. Pengikatan parameter
harus dikerjakan sebelum pernyataan SQL dijalankan.

~~~
[php]
// SQL dengan dua penampung ":username" and ":email"
$sql="INSERT INTO tbl_user (username, email) VALUES(:username,:email)";
$command=$connection->createCommand($sql);
// ganti penampung ":username" dengan nilai username sebenarnya
$command->bindParam(":username",$username,PDO::PARAM_STR);
// ganti penampung ":email" dengan nilai email sebenarnya
$command->bindParam(":email",$email,PDO::PARAM_STR);
$command->execute();
// sisipkan baris lain dengan set baru parameter
$command->bindParam(":username",$username2,PDO::PARAM_STR);
$command->bindParam(":email",$email2,PDO::PARAM_STR);
$command->execute();
~~~

Metode [bindParam()|CDbCommand::bindParam] dan
[bindValue()|CDbCommand::bindValue] sangat mirip. Perbedaannya hanyalah bahwa
bidParam mengikat parameter dengan referensi variabel PHP sedangkan
bindValue dengan nilai. Untuk parameter yang mewakili memori blok besar data,
bindParam dipilih dengan pertimbangan kinerja.

Untuk lebih jelasnya mengenai pengikatan parameter, lihat [dokumentasi PHP
relevan](http://www.php.net/manual/en/pdostatement.bindparam.php).

Mengikat Kolom
--------------

Ketika mengambil hasil query, Anda dapat mengikat kolom ke variabel PHP
dengan demikian hasil akan dipopulasi secara otomatis dengan data terbaru setiap kali
baris diambil.

~~~
[php]
$sql="SELECT username, email FROM tbl_user";
$dataReader=$connection->createCommand($sql)->query();
// ikat kolom ke-1 (username) ke variabel $username
$dataReader->bindColumn(1,$username);
// ikat kolom ke-2 (email) ke variabel $email
$dataReader->bindColumn(2,$email);
while($dataReader->read()!==false)
{
    // $username dan $email berisi username dan email pada baris saat ini
}
~~~

Menggunakan Prefiks Tabel
------------------

Yii menyediakan dukungan terintegrasi untuk menggunakan
prefiks tabel. Prefiks tabel merupakan sebuah string yang dipasang di depan  nama
tabel yang sedang terkoneksi ke database. Kebanyakan digunakan pada lingkungan
shared-hosting yang mana berbagai aplikasi saling pakai sebuah database dan menggunakan
prefiks tabel yang berbeda untuk saling membedakan. Misalnya, satu aplikasi bisa
menggunakan `tbl_` sebagai prefiks sedangkan aplikasi yang lain bisa saja menggunakan `yii_`

Untuk menggunakan prefiks tabel, mengkonfigurasi properti [CdbConnection::tablePrefix] menjadi
sesuai dengan prefiks tabel yang diinginkan. Kemudia, dalam statement SQL gunakan `{{NamaTabel}}`
untuk merujuk nama tabel, di mana `NamaTabel` adalah nama table tanpa prefik.
Misalnya, jika database terdapat sebuah tabel bernama `tbl_user`
 di mana `tbl_` dikonfigurasi sebagai prefiks tabel, maka kita dapat menggunakan kode berikut untuk query user.

~~~
[php]
$sql='SELECT * FROM {{user}}';
$users=$connection->createCommand($sql)->queryAll();
~~~

<div class="revision">$Id: database.dao.txt 2890 2011-01-18 15:58:34Z qiang.xue $</div>