Migrasi Database
==================

> Note|Catatan: Fitur migrasi database telah tersedia sejak versi 1.1.6

Sama seperti halnya dengan source code, struktur database selalu berkembang seiring kita mengembangkan dan merawat aplikasi database-driven. Misalnya, ketika saat pengembangan, kita ingin menambah sebuah tabel baru, atau setelah aplikasi sudah rampung, kita mungkin menyadari perlu menambah sebuah indeks pada sebuah kolom. Sangatlah penting untuk selalu menjaga track dari perubahan strutktural database ini (yang disebut dengan **migration (migrasi)** seperti halnya yang kita lakukan pada source code. Jika source code dan database tidak tersinkronisasi, besar peluangnya keseluruhan aplikasi akan rusak. Karena itulah, Yii menyediakan fungsi migrasi database yang bisa menjaga histori migrasi database, mengaplikasikan migrasi baru ataupun mengembalikannya.

Berikut merupakan langkah-langkah yang dilakukan untuk migrasi database pada masa pengembangan:

1. Budi membuat sebuah migrasi baru (misalnya membuat tabel baru)
2. Budi commit migrasi baru ke sistem pengaturan source (seperti SVN, GIT)
3. Joni mengupdate dari sistem pengontrolan source dan mendapatkan migrasi baru
4. Joni mengaplikasikan migrasi ke database pengembangan lokalnya


Yii mendukung migrasi database melalui perintah `yiic migrate`. Tool ini mendukung pembuatan migrasi baru, mengaplikasikan/mengubah/mengulangi migrasi, dan menampilkan histori migrasi dan migrasi baru.

Berikut ini, kita akan menjelaskan cara penggunaan tool ini.

> Note| Catatan: Lebih baik menggunakan yiic dari aplikasi (yakni `cd path/ke/protected`)
> ketika menjalankan command `migrate` daripada dari direktori `framework`.
> Pastikan Anda memiliki direktori `protected\migrations` dan writable(memiliki hak akses untuk tulis). Cek juga 
> koneksi database di `protected/config/console.php`

Pembutan Migrasi
-------------------

Untuk membuat sebuah migrasi baru (seperti membuat table news), kita jalankan perintah ini:

~~~
yiic migrate create <name>
~~~

Parameter wajib `name` digunakan sebagai deskripsi yang sangat singkat tentang migrasi (misalnya `create_news_table`). Seperti yang akan ditunjukkan berikut ini, parameter `name` digunakan sebagai bagian dari nama kelas PHP. Oleh karena itu, seharusnya hanya mengandung huruf, angka atau garis bawah (underscore).

~~~
yiic migrate create create_news_table
~~~

Perintah di atas akan membuat sebuah file baru bernama `m101129_185401_create_news_table.php` di dalam `protected/migrations` yang mengandung code berikut ini:

~~~
[php]
class m101129_185401_create_news_table extends CDbMigration
{
	public function up()
	{
	}

public function down()
    {
		echo "m101129_185401_create_news_table tidak mendukung migration down.\n";
		return false;
    }

	/*
	// implementasi safeUp/safeDown jika perlu transaction.
	public function safeUp()
	{
	}

	public function safeDown()
	{
	}
	*/
}
~~~

Perhatikan bahwa nama kelas sama dengan nama file dengan pola `m<timestamp>_<name>`, dengan `<timestamp>` merujuk pada timestamp UTC (dalam format `yymmdd_hhmmss`) ketika migrasi dibuat, dan `<name>` diambil dari parameter `name` dari command-nya.

Method `up()` harus mengandung code yang mengimplementasikan migrasi database aktual, sedangkan method `down()` bisa berisi code yang me-revert apa yang telah dilakukan `up()`.

Kadangkala, mengimplementasi `down()` adalah tidak mungkin. Misalnya, kita menghapus baris tabel di `up()`, kita tidak akan bisa mengembalikan mereka dengan `down()`. Dalam hal ini, migrasi disebut sebagai irreversible(tidak dapat dibalikkan), yang berarti kita tidak bisa roll back ke keadaan sebelumnya dalam database. Pada code yang di-generate di atas, method `down()` mengembalikan false untuk menandakan bahwa migration tersebut tidak dapat dibalikkan.

> Info: Mulai dari versi 1.1.7, jika method `up()` atau `down()` mengembalikan
> `false`, maka semua migration selanjutnya akan dibatalkan. Sebelumnya pada versi
> 1.1.6, kita harus melempar exception untuk membatalkan migration selanjutnya

Contohnya, mari melihat tentang cara pembuatan sebuah tabel news.

~~~
[php]
class m101129_185401_create_news_table extends CDbMigration
{
	public function up()
	{
		$this->createTable('tbl_news', array(
			'id' => 'pk',
			'title' => 'string NOT NULL',
			'content' => 'text',
		));
	}

	public function down()
	{
		$this->dropTable('tbl_news');
	}
}
~~~

Kelas dasar [CDbMigration] menyediakan sekumpulan method untuk manipulasi data dan skema database. Misalnya, [CDbMigration::createTable] akan membuat table database, sedangkan [CDbMigration::insert] akan menyisipkan sebuah baris data. Method-method ini semuanya menggunakan koneksi database yang dikembalikan oleh [CDbMigration::getDbConnection()], yang secara default akan mengembalikan `Yii::app()->db`.

> Info|Catatan: Kamu mungkin menyadari bahwa method-method database yang disediakan oleh [CDbMigration] sangat mirip dengan method-method di [CDbCommand]. Memang mereka hampir sama kecuali method [CDbMigration] akan mengukur waktu yang digunakan oleh methodnya dan mencetak beberapa pesan tentang parameter method.


Transactional Migrations
------------------------

> Info: Fitur transactional migration didukung mulai versi 1.1.7.

Ketika melakukan migrasi DB yang kompleks, kita umumnya ingin memastikan apakah setiap migrasi cukup berhasil atau gagal sebagai kesatuan sehingga perawatan database bisa tetap konsisten dan integritas. Guna mencapai tujuan ini, kita dapat memanfaatkan DB transaction.

Kita dapat secara eksplisit memulai sebuah transaksi DB dan menyertakan sisa code yang berhubungan dengan DB, seperti berikut ini:

~~~
[php]
class m101129_185401_create_news_table extends CDbMigration
{
	public function up()
	{
		$transaction=$this->getDbConnection()->beginTransaction();
		try
		{
			$this->createTable('tbl_news', array(
				'id' => 'pk',
				'title' => 'string NOT NULL',
				'content' => 'text',
			));
			$transaction->commit();
		}
		catch(Exception $e)
		{
			echo "Exception: ".$e->getMessage()."\n";
			$transaction->rollBack();
			return false;
		}
	}

	// ...similar code for down()
}
~~~

Namun, cara lebih gampang untuk mendapatkan dukungan transaction adalah dengan mengimplementasikan `safeUp()` daripada `up()`, dan `safeDown()` daripada `down()`. Misalnya,

~~~
[php]
class m101129_185401_create_news_table extends CDbMigration
{
	public function safeUp()
	{
		$this->createTable('tbl_news', array(
			'id' => 'pk',
			'title' => 'string NOT NULL',
			'content' => 'text',
		));
	}

	public function safeDown()
	{
		$this->dropTable('tbl_news');
	}
}
~~~

Ketika Yii melakukan migrasi, maka Yii akan memulai DB transaction dan memanggil `safeUp()` atau `safeDown()`. Jika terdapat error DB yang terjadi di `safeUp()` atau `safeDown()`, maka transaksi akan di-rollback, sehingga memastikan database tetap dalam keadaan baik.

> Note|Catatan: Tidak semua DBMS mendukung transaction. Dan beberapa query DB tidak dapat dimasukkan
> ke transaction. Dalam hal ini, Anda tetap harus mengimplementasikan `up()` dan
> `down()`. Untuk MySQL, beberapa statement bisa menyebabkan
> [implicit commit](http://dev.mysql.com/doc/refman/5.1/en/implicit-commit.html).


Mengaplikasikan Migrasi
-------------------

Untuk mengaplikasikan migrasi baru yang tersedia (misalnya membuat database lokal up-to-date), jalankan perintah berikut:

~~~
yiic migrate
~~~

Perintah ini akan menampilkan daftar semua migrasi yang baru. Jika Anda konfirmasi untuk mengaplikasikan migrasi, maka akan dijalankan method `up()` di setiap kelas migrasi baru, satu per satu secara berurutan, sesuai dengan urutan nilai timstamp di dalam nama kelas.

Setelah mengaplikasikan migrasi, tool migrasi akan mencatat record di sebuah table database bernama `tbl_migration`. Dengan demikian memungkinkan tool untuk mengidentifikasikan berapa migrasi yang telah diaplikasikan dan yang mana belum. Jika table `tbl_migration` tidak ada, maka tool ini akan secara otomatis membuatkannya, tergantung pada database yang dispesifikasikan komponen aplikasi `db`.

Kadang-kadang, kita hanya ingin mengaplikasikan satu atau beberapa migrasi. Kita dapat menggunakan perintah berikut ini:

~~~
yiic migrate up 3
~~~

Perintah ini kan mengaplikasikan 3 migrasi baru. Dengan mengubah nilai 3 akan mengubah jumlah migrasi yang diaplikasikan.

Kita juga dapat migrasikan database ke versi tertentu melalui perintah berikut:

~~~
yiic migrate to 101129_185401
~~~

Kita menggunakan bagian timestamp pada nama migrasi untuk menentukan versi yang ingin kita migrasikan databasenya. Jika terdapat beberapa migrasi di antara migrasi terakhir yang di-apply dan migrasi khusus, semua migrasi ini akan diaplikasikan. Jika migrasi yang ditetapkan telah diaplikasikan sebelumnya, maka seluruh migrasi diaplikasikan setelah di-revert (kembalikan ke semula)


Me-Revert Migrations
--------------------

Untuk me-revert migrasi yang paling terakhir atau beberapa migrasi yang sudah teraplikasikan, kita dapat menggunakan command berikut ini:

~~~
yiic migrate down [step]
~~~

dengan parameter opsional `step` menentukan berapa banyak migrasi yang ingin di-revert. Nilai default-nya adalah 1, yang artinya me-revert kembali ke migrasi yang sudah teraplikasi paling terakhir.

Seperti yang sudah disebutkan sebelumnya, tidak semua migrasi dapat di-revert. Mencoba me-revert migrasi seperti demikian akan menghasilkan error dan menghentikan seluruh proses revert.


Mengulangi Migrasi
------------------

Mengulangi migrasi maksudnya melakukan revert kemudian mengaplikasikan migrasi tertentu. Proses ini dapat dilakukan dengan melakukan perintah berikut:

~~~
yiic migrate redo [step]
~~~

dengan parameter opsional `step` menunjukkan berapa banyak migrasi yang ingin diulangi. Nilai default-nya 1, yang artinya mengulangi migrasi terakhir.


Menampilkan Informasi Migrasi
-----------------------------

Selain mengaplikasikan dan me-revert migrasi, perangkat migrasi juga dapat digunakan untuk menampilkan histori migrasi dan migrasi baru yang akan diaplikasikan.

~~~
yiic migrate history [limit]
yiic migrate new [limit]
~~~

Parameter opsional `limit` menunjukkan angka migrasi yang akan ditampilkan. Jika `limit` tidak ditentukan, semua migrasi yang tersedia akan ditampilkan.

Perintah pertama yang menampilkan migrasi yang sudah diaplikasikan, sedangkan perintah kedua menampilkan migrasi yang belum diaplikasikan.


Mengubah Histori Migrasi
---------------------------

Kadang-kadang, mungkin kita ingin mengubah histori migrasi ke versi migrasi tertentu tanpa sebetulnya mengaplikasikan atau me-revert kembali  migrasi yang bersangkutan. Biasanya sering terjadi selama pengembangan migrasi baru. Kita dapat menggunakan perintah berikut.

~~~
yiic migrate mark 101129_185401
~~~

Perintah ini sangat mirip dengan perintah `yiic migrate to` kecuali dia hanya memodifikasikan tabel histori migrasi ke versi yang ditentukan tanpa melakukan perubahan ataupun me-revert migrasi.


Mengkustomisasi Perintah Migrasi
-----------------------------

Terdapat beberapa cara untuk mengkustomisasi perintah migration.

### Menggunakan Opsi Command Line

 Perintah migrasi datang dari empat opsi yang dpaat ditentukan di command line:

* `interactive`: boolean, menentukan apakah migrasi dilakukan dengan cara interaktif. Secara default bernilai true, yang artinya user akan ditanya terlebih dahulu ketika melakukan migrasi tertentu. Anda dapat set nilainya menjadi false sehingga migrasi dapat dilakukan di belakang layar.

* `migrationPath`: string, menentukan direktori yang menampung seluruh file kelas migrasi. Harus diisi dengan bentuk path alias, dan direktori bersangkutan harus ada. Jika tidak ditentukan, maka akan digunakan sub-direktori `migrations` di dalam base path aplikasi.

* `migrationTable`: string, menentukan nama tabel database yang digunakan untuk menyimpan informasi histori migrasi. Secara default bernilai `tbl_migration`. Struktur tabelnya yakni `version varchar(255) primary key, apply_time integer`.

* `connectionID`: string, menentukan ID pada komponen database aplikasi. Nilai default adala 'db'.

* `templateFile`: string, menentukan jalur ke file yang ditunjukkan sebagai template code untuk men-generate kelas migrasi. Harus diisi dalam format path alias (misalnya `application.migrations.template`). Jika tidak diisi, maka template internal yang akan digunakan. Di dalam template, token `{ClassName}` akan digantikan oleh nama kelas migrasi yang sesungguhnya.

Untuk menentukan opsi-opsi tersebut, jalankan perintah migrate dengan format ini

~~~
yiic migrate up --option1=value1 --option2=value2 ...
~~~

Misalnya kita ingin migrasi module forum yang file-file migrasinya terletak di direktori module `migrations`, kita dapat menggunakan perintah berikut :

~~~
yiic migrate up --migrationPath=ext.forum.migrations
~~~


### Mengatur Perintah Secara Global

Opsi command line memungkinkan kita melakukan konfiguras secara on-the-fly, namun kadangkala kita mungkin ingin mengatur command untuk selamanya. Misalnya, kita ingin menggunakan tabel yang berbeda untuk menyimpan histori migrasi, atau kita ingin menggunakan sebuah template migrasi yang sudah kita desain. Kita dapat melakukannya dengan mengubah konfigurasi console aplikasi dengan begini,

~~~
[php]
return array(
	......
	'commandMap'=>array(
		'migrate'=>array(
			'class'=>'system.cli.commands.MigrateCommand',
			'migrationPath'=>'application.migrations',
			'migrationTable'=>'tbl_migration',
			'connectionID'=>'db',
			'templateFile'=>'application.migrations.template',
		),
		......
	),
	......
);
~~~

Sekarang jika kita jalankan perintah `migrate`, konfigurasi di atas akan berefek tanpa kita harus menulis perintah opsi setiap saat lagi.


<div class="revision">$Id: database.migration.txt 3450 2011-11-20 22:52:07Z alexander.makarow $</div>
