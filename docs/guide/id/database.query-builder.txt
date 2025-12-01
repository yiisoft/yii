Query Builder
=============

Yii Query Builder menyediakan cara berorientasi objek dalam menulis statement SQL. Fitur ini membantu pengembang untuk menggunakan property dan method kelas untuk menentukan bagian-bagian dari statement SQL yang kemudian menggabungkannya menjasi sebuah statement SQL yang valid yang bisa dieksekusi lebih lanjut oleh method DAO seperti yang dideskripsikan di [Data Access Objects](/doc/guide/database.dao). Berikut akan ditampilkan penggunaan umum dari Query Builder untuk membangun sebuah statement SQL SELECT:

~~~
[php]
$user = Yii::app()->db->createCommand()
	->select('id, username, profile')
	->from('tbl_user u')
	->join('tbl_profile p', 'u.id=p.user_id')
	->where('id=:id', array(':id'=>$id))
	->queryRow();
~~~


Query Builder sangat cocok digunakan ketika Anda memerlukan menggabungkan sebuah statement SQL secara prosedural, atau berdasarkan suatu kondisi logis dalam aplikasi Anda. Manfaat utama dalam menggunakan Query Builder termasuk:

* Memungkinkan membangun statement SQL yang kompleks secara programatik

* Fitur ini akan memberikan quote pada nama table dan kolom secara otomatis guna mencegah konflik dengan tulisan SQL ataupun karakter khusus.

* Fitur ini juga memberikan quote pada nilai parameter dan melakukan binding pada parameter ketika memungkinkan, sehingga mengurangi resiko terserang SQL injection.

* Fitur ini menyediakan sekian tingkatan abstraksi pada DB, yang menyederhanakan migrasi ke platform DB yang berbeda.


Menggunakan Query Builder bukanlah sebuah keharusan. Bahkan, jika query Anda cukup sederhana, akan leih gampang dan cepat menulis SQL-nya langsung.

> Note|Catatan: Query builder tidak bisa digunakan untuk memodifikasi query yang
> sudah ada sebagai statement SQL. Misalnya, code berikut tidak akan berjalan:
>
> ~~~
> [php]
> $command = Yii::app()->db->createCommand('SELECT * FROM tbl_user');
> // baris berikut tidak akan menambahkan WHERE ke klausa SQL di atas.
> $command->where('id=:id', array(':id'=>$id));
> ~~~
>
> Oleh karena itu, jangan campur penggunaan SQL biasa dengan query builder.


Mempersiapkan Query Builder
-----------------------

Query Builder Yii disediakan oleh [CDbCommand], kelas query DB utama di [Data Access Objects](/doc/guide/database.dao).

Untuk menggunakan Query Builder, kita membuat sebuah instance baru dari [CDbCommand] dengan cara berikut,

~~~
[php]
$command = Yii::app()->db->createCommand();
~~~

Begitulah, kita menggunakan `Yii::app()->db` untuk mendapatkan koneksi DB, kemudian melakukan pemanggilanpada [CDbConnection::createCommand()] untuk membuat instance command yang diperlukan.

Perhatikan bahwa alih-alih kita mem-pass semua statement SQL ke `createCommand()` seperti yang dilakukan di [Data Access Objects](/doc/guide/database.dao), kita membiarkannya kosong. Ini dikarenakan kita akan membangun bagian-bagian individu dari statement SQL dengan menggunakan method Query Builder yang akan dijelaskan pada bagian berikut.


Membangung Query Penarik Data
-------------------------------

Query penarik data merujuk pada statement SELECT pada SQL. Query builder menyediakan sekumpulan method untuk membangun bagian dari statement SELECT. DIkarenakan semua method ini mengembalikan instance [CDbCommand], kita dapat memanggil mereka dengan menggunakan method chaining, seperti pada contoh di awal.

* [select()|CDbCommand::select()]: menentukan bagian SELECT pada query
* [selectDistinct()|CDbCommand::selectDistinct()]: menentukan bagian SELECT pada query serta mengaktifkan flag DISTINCT
* [from()|CDbCommand::from()]: menentukan bagian FROM pada query
* [where()|CDbCommand::where()]: menentukan bagian WHERE pada query
* [join()|CDbCommand::join()]: menambah pecahan query inner join
* [leftJoin()|CDbCommand::leftJoin()]: menambah pecahan left query left outer join
* [rightJoin()|CDbCommand::rightJoin()]: menambah pecahan query right outer join
* [crossJoin()|CDbCommand::crossJoin()]: menambah pecahan query cross join
* [naturalJoin()|CDbCommand::naturalJoin()]: menambah pecahan query natural join
* [group()|CDbCommand::group()]: menentukan bagian GROUP BY pada query
* [having()|CDbCommand::having()]: menentukan bagian HAVING pada query
* [order()|CDbCommand::order()]: menentukan bagian ORDER BY pada query
* [limit()|CDbCommand::limit()]: menentukan bagian LIMIT pada query
* [offset()|CDbCommand::offset()]: menentukan bagian OFFSET pada query
* [union()|CDbCommand::union()]: menentukan bagian UNION pada query


Berikut, kami akan menunjukkan bagaimana menggunakan method-method query builder ini. Supaya sederhana, kami mengasumsi database yang digunakan adalah MySQL. Perhatikan bahwa jika Anda menggunakan DBMS yang lain, quote table/kolom/nilai akan berbeda dengan contoh.


### select()

~~~
[php]
function select($columns='*')
~~~

Method [select()|CDbCommand::select()] menentukan bagian `SELECT` pada query. Parameter `$columns` menentukan kolom-kolom apa saja yang akan di-select, yang bisa berupa string dengan nama kolom dipisah koma, atau sebuah array dari nama kolom. Nama kolom dapat berisi prefiks table dan/atau alias kolom. Method ini akan secara otomatis memberikan quote pada nama kolom kecuali kolom tersebut mengandung tanda kurung (yang bararti kolom yang diberikan merupakan ekspresi DB).

Berikut ini merupakan beberapa contoh:

~~~
[php]
// SELECT *
select()
// SELECT `id`, `username`
select('id, username')
// SELECT `tbl_user`.`id`, `username` AS `name`
select('tbl_user.id, username as name')
// SELECT `id`, `username`
select(array('id', 'username'))
// SELECT `id`, count(*) as num
select(array('id', 'count(*) as num'))
~~~


### selectDistinct()

~~~
[php]
function selectDistinct($columns)
~~~

Method [selectDistinct()|CDbCommand::selectDistinct()] mirip dengan [select()|CDbCommand::select()]. Hanya saja [selectDistinct|CDbCommand::selectDistinct] mengaktifkan flag `DISTINCT`. Misalnya, `selectDistinct(`id,username')` akan menghasilkan SQL berikut:

~~~
SELECT DISTINCT `id`, `username`
~~~


### from()

~~~
[php]
function from($tables)
~~~

Method [from()|CDbCommand::from()] menentukan bagian `FROM` pada query. Parameter `$tables` menentukan table mana yang akan di-select. Yang ini juga bisa berupa string dengan nama table dipisahkan dengan koma, atau sebuah array dari nama table. Nama table dapat diambil dari prefiks skema (misalnya `public.tbl_user`) dan/atau alias table (misalnya `tbl_user u`). Method ini akan secara otomatis memberikan quote pada nama table kecuali nama table-nya mengandung huruf kurung (yang artinya berupa sub-query atau ekspresi DB).

Berikut merupakan beberapa contoh:

~~~
[php]
// FROM `tbl_user`
from('tbl_user')
// FROM `tbl_user` `u`, `public`.`tbl_profile` `p`
from('tbl_user u, public.tbl_profile p')
// FROM `tbl_user`, `tbl_profile`
from(array('tbl_user', 'tbl_profile'))
// FROM `tbl_user`, (select * from tbl_profile) p
from(array('tbl_user', '(select * from tbl_profile) p'))
~~~


### where()

~~~
[php]
function where($conditions, $params=array())
~~~

Method [where()|CDbCommand::where()] menetapkan bagian `WHERE` pada query. Parameter `$conditions` menentukan kondisi query sedangkan `$params` menentukan parameter yang diikat pada keseluruhan query. Parameter `$conditions` dapat berupa sebuah string (misalnya `id=1`) atau sebuah array dengan format:

~~~
[php]
array(operator, operand1, operand2, ...)
~~~

dengan `operator`dapat bisa berupa :

* `and`: operan harus digabung dengan menggunakan `AND`. Misalnya `array('and', 'id=1', 'id=2')` akan menghasilkan `id=1 AND id=2`. Jika operan adalah array, maka akan diubah menjadi string dengan menggunakan aturan yang sama. Misalnya `array('and', 'type=1', array('or', 'id=1', 'id=2'))` akan menghasilkan `type=1 AND (id=1 OR id=2)`. Method ini tidak akan memberikan quote ataupun escape character.

* `or`: mirip dengan operator `and` hanya saja operan-operan akan digabung dengan OR.

* `in`: Operan satu harus berupa kolom atau ekspresi DB, dan operan 2 harus berupa array yang merepresentasikan kumpulan nilai yang harus dipenuhi oleh kolom atau ekspresi DB. Misalnya `array('in', 'id', array(1,2,3))` akan menghasilkan `id IN (1,2,3)`. Method ini akan memberikan quote pada nama kolom dan nilai escape di dalam range.

* `not in`: mirip dengan operator `in` kecuali tulisan `IN` akan diubah dengan `NOT IN` di dalam kondisi yang di-generate.

* `like`: operan 1 harus berupa kolom atau ekspresi DB, dan operan 2 harus berupa string atau sebuah array yang mewakili range dari nilai-nilai di kolom atau ekspresi DB yang mirip. Misalnya,  `array('like', 'name', '%tester%')` akan menghasilkan `name LIKE '%tester%'`.  Ketika range nilai diberikan sebagai array, maka beberapa predikat `LIKE` akan di-generate dan digabungkan dengan menggunakan `AND`. Misalnya `array('like', 'name', array('%test%', '%sample%'))` akan menghasilkan `name LIKE '%test%' AND name LIKE '%sample%'`. Method ini akan memberikan quote pada nama kolom dan nilai escape pada range nilai.

* `not like`: mirip dengan operator `like` kecuali tulisan `LIKE` akan diganti dengan `NOT LIKE` pada kondisi yang dihasilkan.

* `or like`: mirip dengan operator `like` hanya saja tulisan `OR` yang digunakan untuk menggabungkan beberapa predikat `LIKE`.

* `or not like`: mirip dengan operator `not like` kecuali `OR` yang digunakan untuk menggabungkan predikat `NOT LIKE`.


Berikut merupakan beberapa contoh yang menggunakan `where`:

~~~
[php]
// WHERE id=1 or id=2
where('id=1 or id=2')
// WHERE id=:id1 or id=:id2
where('id=:id1 or id=:id2', array(':id1'=>1, ':id2'=>2))
// WHERE id=1 OR id=2
where(array('or', 'id=1', 'id=2'))
// WHERE id=1 AND (type=2 OR type=3)
where(array('and', 'id=1', array('or', 'type=2', 'type=3')))
// WHERE `id` IN (1, 2)
where(array('in', 'id', array(1, 2))
// WHERE `id` NOT IN (1, 2)
where(array('not in', 'id', array(1,2)))
// WHERE `name` LIKE '%Qiang%'
where(array('like', 'name', '%Qiang%'))
// WHERE `name` LIKE '%Qiang' AND `name` LIKE '%Xue'
where(array('like', 'name', array('%Qiang', '%Xue')))
// WHERE `name` LIKE '%Qiang' OR `name` LIKE '%Xue'
where(array('or like', 'name', array('%Qiang', '%Xue')))
// WHERE `name` NOT LIKE '%Qiang%'
where(array('not like', 'name', '%Qiang%'))
// WHERE `name` NOT LIKE '%Qiang%' OR `name` NOT LIKE '%Xue%'
where(array('or not like', 'name', array('%Qiang%', '%Xue%')))
~~~

Perhatikan bahwa ketika menggunakan operator `like`, kita harus menentukan karakter wildcard secara eksplisit (seperti `%` dan `_`) . JIka polanya berasal dari input user, maka kita harus menggunakan code berikut untuk escape karakter spesial guna menghindarinya dianggap sebagai wildcard:

~~~
[php]
$keyword=$_GET['q'];
// escape % and _ characters
$keyword=strtr($keyword, array('%'=>'\%', '_'=>'\_'));
$command->where(array('like', 'title', '%'.$keyword.'%'));
~~~


### order()

~~~
[php]
function order($columns)
~~~

Method [order()|CDbCommand::order()] menentukan bagian `ORDER BY` pada query.
Parameter `$columns` menentukan kolom-kolom yang diurutkan. Dapat berupa sebuah string dengan kolom yang dipisahkan koma dan arah pengurutan (`ASC` atau `DESC`), atau sebuah array dari kolom dan arah pengurutan. Nama kolom dapat mengandung prefiks table. Method ini akan memberikan quote pada nama kolom secara otomatis kecuali kolom tersebut mengandung tanda kurung (yang berarti kolom tersebut merupakan ekspresi DB).

Berikut merupakan beberapa contohnya:

~~~
[php]
// ORDER BY `name`, `id` DESC
order('name, id desc')
// ORDER BY `tbl_profile`.`name`, `id` DESC
order(array('tbl_profile.name', 'id desc'))
~~~


### limit() dan offset()

~~~
[php]
function limit($limit, $offset=null)
function offset($offset)
~~~

Method [limit()|CDbCommand::limit()] dan [offset()|CDbCommand::offset()] menentukan bagian `OFFSET` dan `LIMIT` pada query. Perhatikan bahwa beberapa DBMS mungkin tidak mendukung sintaks `LIMIT` dan `OFFSET`. Pada kasus tersebut, Query Builder akan menulis ulang seluruh statement SQL untuk mensimulasi fungsi limit dan offset.

Berikut merupakan beberapa contoh:

~~~
[php]
// LIMIT 10
limit(10)
// LIMIT 10 OFFSET 20
limit(10, 20)
// OFFSET 20
offset(20)
~~~


### join() dan varian-variannya

~~~
[php]
function join($table, $conditions, $params=array())
function leftJoin($table, $conditions, $params=array())
function rightJoin($table, $conditions, $params=array())
function crossJoin($table)
function naturalJoin($table)
~~~

Method [join()|CDbCommand::join()] dan varian-variannya menentukan bagaimana melakukan join dengan table lain dengan menggunakan `INNER JOIN`, `LEFT OUTER JOIN`, `RIGHT OUTER JOIN`, `CROSS JOIN`, atau `NATURAL JOIN`. Parameter `$table` menentukan table mana yang akan dijoin. Nama table akan mengandung prefiks skema dan /atau alias. Method ini akan memberikan quote pada nama table kecuali kolom tersebut mengandung tanda kurung yang artinya bis berupa ekspresi DB atau sub-query. Parameter `$conditions` menentukan kondisi join. Sintaksnya sama dengan [where()|CDbCommand::where()]. Dan `$params` menentukan parameter yang diikat pada keseluruhan query.

Perhatikan bahwa tidak seperti method query builder lainnya, setiap pemanggilan method join akan ditambahkan di belakang sebelumnya.

Berikut merupakan beberapa contohnya.

~~~
[php]
// JOIN `tbl_profile` ON user_id=id
join('tbl_profile', 'user_id=id')
// LEFT JOIN `pub`.`tbl_profile` `p` ON p.user_id=id AND type=1
leftJoin('pub.tbl_profile p', 'p.user_id=id AND type=:type', array(':type'=>1))
~~~


### group()

~~~
[php]
function group($columns)
~~~

Method [group()|CDbCommand::group()] menetapkan bagian `GROUP BY` pada query.
Parameter `$columns` menentukan kolom-kolom yang dikelompokkan. Bisa berupa string yang berisi kolom dipisah koma, atau sebuah array dari kolom. Nama kolom bisa didapatkan pada prefiks table. Method ini akan secara otomatis memberikan quote pada nama kolom kecuali terdapat sebuah kolom yang mengandung tanda kurung (yang artinya kolom tersebut merupakan ekspresi DB).

Berikut beberapa contoh:

~~~
[php]
// GROUP BY `name`, `id`
group('name, id')
// GROUP BY `tbl_profile`.`name`, `id`
group(array('tbl_profile.name', 'id')
~~~


### having()

~~~
[php]
function having($conditions, $params=array())
~~~

Method [having()|CDbCommand::having()] menetapkan bagian `HAVING` pada query. Penggunaannya sama dengan [where()|CDbCommand::where()].

Berikut contoh-contohnya:

~~~
[php]
// HAVING id=1 or id=2
having('id=1 or id=2')
// HAVING id=1 OR id=2
having(array('or', 'id=1', 'id=2'))
~~~


### union()

~~~
[php]
function union($sql)
~~~

Method [union()|CDbCommand::union()] menentukan bagian `UNION` pada query. Method ini akan menambahkan `$sql` ke SQL yang sudah ada dengan menggunakan operator `UNION`. Memanggil `union()` beberapa kali akan menambahkan berkali-kali SQL-SQL-nya ke belakang SQL yang sudah ada.

Contoh:

~~~
[php]
// UNION (select * from tbl_profile)
union('select * from tbl_profile')
~~~


### Menjalankan Query

Setelah melakukan pemanggilan method query builder di atas, kita dapat memanggil method DAO seperti yang dijelaskan pada [Data Access Objects](/doc/guide/database.dao) untuk mengeksekusi query. Misalnya, kita dapat memanggil [CDbCommand::queryRow()] untuk mendapatkan sebaris hasil atau [CDbCommand::queryAll()] untuk mendapatkan seluruhnya sekaligus.
Berikut beberapa contohnya :

~~~
[php]
$users = Yii::app()->db->createCommand()
	->select('*')
	->from('tbl_user')
	->queryAll();
~~~


### Mengambil SQL-SQL

Selain menjalankan query yang dibuat oleh Query Builder, kita juga dapat menarik statement SQL bersangkutan. Untuk melakukannya gunakan fungsi [CDbCommand::getText()].

~~~
[php]
$sql = Yii::app()->db->createCommand()
	->select('*')
	->from('tbl_user')
	->text;
~~~

Jika terdapat parameter tertentu yang terikat pada query, mereka dapat diambil melalui properti [CDbCommand::params].


### Sintaks Alternatif untuk Membentuk Query

Kadangkala, menggunakan method chaining bukanlah pilihan yang tepat. Query Builder Yii memungkinkan kita untuk membuat query dengan menggunakan assignment property object yang sederhana. Pada umumnya, untuk setiap method query builder, terdapat sebuah property yang memiliki nama yang sama. Meng-assign nilai ke property sama saja dengan memanggil method tersebut. Misalnya, berikut merupakan dua statement yang ekuivalen, dengan asumsi `$command` adalah objek [CDbCommand]:

~~~
[php]
$command->select(array('id', 'username'));
$command->select = array('id', 'username');
~~~

Selain itu, method [CDbConnection::createCommand()] dapat menerima array sebagai parameter. Pasangan nama-nilai di array akan digunakan untuk inisialisasi property instance [CDbCommand] yang dibuat. Ini artinya, kita dapat menggunakan code berikut untuk membuat sebuah query:

~~~
[php]
$row = Yii::app()->db->createCommand(array(
	'select' => array('id', 'username'),
	'from' => 'tbl_user',
	'where' => 'id=:id',
	'params' => array(':id'=>1),
))->queryRow();
~~~


### Membangun Beberapa Query

Sebuah instance [CDbCommand] dapat dipakai ulang beberapa kali untuk membuat beberapa query. Namun, sebelum membuat query baru, harus memanggil method [CDbCommand::reset()] terlebih dahulu untuk menghapus query sebelumnya. Misalnya:

~~~
[php]
$command = Yii::app()->db->createCommand();
$users = $command->select('*')->from('tbl_users')->queryAll();
$command->reset();  // clean up the previous query
$posts = $command->select('*')->from('tbl_posts')->queryAll();
~~~


Membuat Query Manipulasi Data
----------------------------------

Query manipulasi data adalah statement SQL untuk melakukan insert, update dan delete data dalam table database. Query builder menyediakan `insert`, `update` dan `delete` untuk tiap query tersebut. Tidak seperti method query SELECT yang dijelaskan di atas, setiap method query manipulasi data ini akan membuat sebuah statement SQL lengkap dan langsung menjalankannya.

* [insert()|CDbCommand::insert]: menyisipkan sebaris ke table
* [update()|CDbCommand::update]: melakukan update data pada sebuah table
* [delete()|CDbCommand::delete]: menghapus data dari table


Di bawah ini kami akan memaparkan method-method query manipulasi data


### insert()

~~~
[php]
function insert($table, $columns)
~~~

Method [insert()|CDbCommand::insert] membuat dan menjalankan statement SQL `INSERT`. Parameter `$table` menentukan table yang mana yang disisipkan, sedangkan `$columns` merupakan sebuah array dengan pasangan nama-nilai yang menjelaskan nilai-nilai kolom yang akan disisipkan. Method tersebut akan memberikan quote pada nama table dan akan menggunakan parameter-binding untuk nilai yang dimasukkan.

Berikut merupakan contohnya:

~~~
[php]
// buat dan jalankan SQL berikut :
// INSERT INTO `tbl_user` (`name`, `email`) VALUES (:name, :email)
$command->insert('tbl_user', array(
	'name'=>'Tester',
	'email'=>'tester@example.com',
));
~~~


### update()

~~~
[php]
function update($table, $columns, $conditions='', $params=array())
~~~

Method [update()|CDbCommand::update] akan membuat dan mengeksekusi statement `UPDATE` SQL. Parameter `$table` menentukan table mana yang akan di-update; `$columns` adalah sebuah array dengan pasangan nama-nilai yang menentukan nilai kolom yang akan di-update; `$conditions` dan `$params` mirip dengan [where()|CDbCommand::where()], yang akan menetapkan klausa `WHERE` dalam statement `UPDATE`. Method ini akan memberikan quote pada nama dan menggunakan parameter-binding untuk nilai yang di-update.

Berikut merupakan contohnya:

~~~
[php]
// buat dan jalankan SQL berikut:
// UPDATE `tbl_user` SET `name`=:name WHERE id=:id
$command->update('tbl_user', array(
	'name'=>'Tester',
), 'id=:id', array(':id'=>1));
~~~


### delete()

~~~
[php]
function delete($table, $conditions='', $params=array())
~~~

Method [delete()|CDbCommand::delete] membuat dan menjalankan statement SQL `DELETE`. Parameter `$table` menentukan table yang mana yang akan dihapus; `$conditions`  dan `$params` mirip dengan [where()|CDbCommand::where()], yakni menentukan `WHERE` di dalam statement `DELETE`. Method ini akan memberikan quote pada nama.

Berikut salah satu contoh:

~~~
[php]
//buat dan eksekusi SQL berikut:
// DELETE FROM `tbl_user` WHERE id=:id
$command->delete('tbl_user', 'id=:id', array(':id'=>1));
~~~

Membuat Query Manipulasi Schema
------------------------------------

Selain query manipulasi dan penarikan normal, query builder juga menyediakan sekumpulan method yang digunakan untuk membuat dan menjalankan query SQL untuk manipulasi schema pada database. Query builder mendukung query-query berikut:

* [createTable()|CDbCommand::createTable]: membuat table
* [renameTable()|CDbCommand::renameTable]: mengubah nama table
* [dropTable()|CDbCommand::dropTable]: drop (menghapus) table
* [truncateTable()|CDbCommand::truncateTable]: mengosongkan table
* [addColumn()|CDbCommand::addColumn]: menambahkan sebuah kolom table
* [renameColumn()|CDbCommand::renameColumn]: mengubah nama kolom table
* [alterColumn()|CDbCommand::alterColumn]: mengubah sebuah kolom table
* [dropColumn()|CDbCommand::dropColumn]: me-drop (hapus) kolom table
* [createIndex()|CDbCommand::createIndex]: membuat index
* [dropIndex()|CDbCommand::dropIndex]: me-drop (hapus) index

> Info: Walaupun statement SQL yang digunakan untuk manipulasi database schema sangat berbeda di antara DBMS, query builder mencoba menyediakan sebuah interface yang seragam untuk membuat query-query ini. Ini akan memudahkan proses migrasi database dari satu DBMS ke yang lainnya.


###Tipe Data Abstrak 

Query builder memperkenalkan sekumpulan tipe data abstrak yang dapat digunakan untuk mendefinisikan kolom table. Tidak seperti tipe data physical yang spesifik pada DBMS tertentu dan cukup berbeda di DBMS lainnya, tipe data abstrak bebas dari DBMS. Ketika tipe data abstrak digunakan untuk mendefinisikan kolom table, query builder akan mengubahnya menjadi tipe data physical bersangkutan.

Berikut tipe data abstrak yang didukung oleh query builder.

* `pk`: sebuah jenis primary key generik, akan diubah menjadi `int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY` pada MySQL;
* `string`: jenis string, akan diubah menjadi `varchar(255)` pada MySQL;
* `text`: jenis teks (string panjang), akan diubah menjadi `text` pada MySQL
* `integer`: jenis integer, akan diubah menjadi `int(11)` pada MySQL;
* `float`: tipe angka floating, akan diubah menjadi `float` pada MySQL;
* `decimal`: tipe angka desimal, akan diubah menjadi `decimal` pada MySQL;
* `datetime`: tipe waktu tanggal, akan diubah menjadi `datetime` pada MySQL;
* `timestamp`: tipe timestamp, akan diubah menjadi `timestamp` pada MySQL;
* `time`: tipe waktu, akan diubah menjadi `time` pada MySQL;
* `date`: tipe tanggal, akan diubah menjadi `date` pada MySQL;
* `binary`: tipe data biner, akan diubah menjadi `blob` pada MySQL;
* `boolean`: tipe boolean, akan diubah menjadi `tinyint(1)` pada MySQL;
* `money`: tipe mata uang, akan diubah menjadi `decimal(19,4)` pada MySQL. Tipe ini sudah ada semenjak versi 1.1.8.


###createTable()

~~~
[php]
function createTable($table, $columns, $options=null)
~~~

Method [createTable()|CDbCommand::createTable] akan membuat dan menjalankan statement SQL untuk menghasilkan sebuah table. Parameter `$table` akan menentukan nama dari table yang dibuat. Parameter `$columns` menentukan kolom-kolom pada table baru. Kolom-kolom ini harus diberikan dalam bentuk pasangan nama-definisi (misalnya `'username'=>'string'`). Parameter `$options` menentukan pecahan SQL ekstra yang harus ditambahkan pada SQL yang dihasilkan. Query builder akan memberikan quote pada nama table dan nama kolom.

Ketika menentukan definisi sebuah kolom, kita dapat menggunakan tipe data abstrak seperti yang sudah dijelaskan di atas. Query builder akan mengubah tipe data abstrak tersebut menjadi tipe data physical bersangkuta, sesuai dengan DBMS yang digunakan. Misalnya, `string` akan diubah menjadi `varchar(255)` pada MySQL.

Sebuah definisi kolom juga bisa mengandung tipe data non-abstrak atau spesifikasi. Definisi kolom ini akan dmasukkan juga ke dalam SQL tanpa perubahan. Misalnya `point` bukanlah tipe data abstrak, dan jika digunakan di definisi kolom, maka akan keluar demikian pada SQL; dan `string NOT NULL` akan diubah menjadi `varchar(255) NOT NULL` (hanya tipe abstrak `string` yang diubah).

Berikut contoh bagaimana membuat sebuah table:

~~~
[php]
// CREATE TABLE `tbl_user` (
//     `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
//     `username` varchar(255) NOT NULL,
//     `location` point
// ) ENGINE=InnoDB
createTable('tbl_user', array(
	'id' => 'pk',
	'username' => 'string NOT NULL',
	'location' => 'point',
), 'ENGINE=InnoDB')
~~~


###renameTable()

~~~
[php]
function renameTable($table, $newName)
~~~

Method [renameTable()|CDbCommand::renameTable] membuat dan menjalankan statement SQL untuk mengubah nama table. Parameter `$table` menentukan nama dari table yang akan di-rename. Parameter `$newName` menentukan nama baru dari table. Query builder akan memberikan quote pada nama table.

Berikut contoh bagaimana melakukan perubahan nama pada table :

~~~
[php]
// RENAME TABLE `tbl_users` TO `tbl_user`
renameTable('tbl_users', 'tbl_user')
~~~


###dropTable()

~~~
[php]
function dropTable($table)
~~~

Method [dropTable()|CDbCommand::dropTable] membuat dan menjalankan statement SQL untuk menghapus sebuah table. Parameter `$table` menentukan nama table yang akan di-drop (hapus). Query builder akan memberikan quote pada nama table.

Berikut contoh bagaimana menghapus sebuah table:

~~~
[php]
// DROP TABLE `tbl_user`
dropTable('tbl_user')
~~~

###truncateTable

~~~
[php]
function truncateTable($table)
~~~

Method [truncateTable()|CDbCommand::truncateTable] membuat dan menjalankan statement SQL untuk menghapus sebuah table. Parameter `$table` menentukan nama table yang ingin dikosongkan. Query builder akan memberikan quote pada nama table.

Berikut contoh bagaimana mengosongkan table:

~~~
[php]
// TRUNCATE TABLE `tbl_user`
truncateTable('tbl_user')
~~~


###addColumn()

~~~
[php]
function addColumn($table, $column, $type)
~~~

Method [addColumn()|CDbCommand::addColumn] membuat dan menjalankan statement SQL untuk menambah kolom table baru. Parameter `$table` menentukan nama dari table yang akan ditambahkan kolom baru. Parameter `$column` akan menentukan nama dari kolom baru. Dan `$type` menentukan definisi kolom baru. Definisi kolom dapat mengandung tipe data abstrak, seperti yang dijelaskan pada sub-bagian "createTable" sebelumnya. Query builder akan memberikan quote pada nama table termasuk nama kolom.

Berikut contoh menambah sebuah kolom table:

~~~
[php]
// ALTER TABLE `tbl_user` ADD `email` varchar(255) NOT NULL
addColumn('tbl_user', 'email', 'string NOT NULL')
~~~


###dropColumn()

~~~
[php]
function dropColumn($table, $column)
~~~

Method [dropColumn()|CDbCommand::dropColumn] membuat dan menjalankan statement SQL untuk menghapus kolom table. Parameter `$table` menentukan nama dari table yang kolomnnya akan dihapus. Parameter `$column` menentukan nama dari kolom yang akan dihapus. Query builder akan memberikan quote pada nama table termasuk nama kolom.

Berikut contoh bagaimana menghapus sebuah kolom table:

~~~
[php]
// ALTER TABLE `tbl_user` DROP COLUMN `location`
dropColumn('tbl_user', 'location')
~~~


###renameColumn()

~~~
[php]
function renameColumn($table, $name, $newName)
~~~

Method [renameColumn()|CDbCommand::renameColumn] membuat dan mengeksekusi statement SQL untuk mengubah nama kolom table. Parameter `$table` menentukan nama table yang nama kolomnya akan diubah. Parameter `$name` menentukan nama kolom yang lama. Dan `$newName` menentukan nama kolom baru. Query builder akan memberikan quote pada nama table termasuk pada nama kolom.

Berikut contoh mengubah nama kolom table:

~~~
[php]
// ALTER TABLE `tbl_users` CHANGE `name` `username` varchar(255) NOT NULL
renameColumn('tbl_user', 'name', 'username')
~~~


###alterColumn()

~~~
[php]
function alterColumn($table, $column, $type)
~~~

Method [alterColumn()|CDbCommand::alterColumn] membuat dan menjalankan statement SQL untuk mengubah kolom table. Parameter `$table` menentukan nama table yang kolomnya akan diubah. Parameter `$column` menentukan nama kolom yang akan diubah. Dan `$type` menentukan definisi baru pada kolom. Definisi kolom dapat mengandung tipe data abstrak, seperti yang sudah dijelaskan pada sub-bagian "createTable". Query builder akan memberikan quote pada nama table dan nama kolom.

Berikut contoh mengubah kolom table:

~~~
[php]
// ALTER TABLE `tbl_user` CHANGE `username` `username` varchar(255) NOT NULL
alterColumn('tbl_user', 'username', 'string NOT NULL')
~~~




###addForeignKey()

~~~
[php]
function addForeignKey($name, $table, $columns,
	$refTable, $refColumns, $delete=null, $update=null)
~~~

Method [addForeignKey()|CDbCommand::addForeignKey] akan membuat dan menjalankan statement SQL untuk menambahkan foreign key constraint pada sebuah table. Parameter `$name` menentukan nama foreign key. Parameter `$table` dan `$columns` menentukan nama table dan nama kolom yang akan ditetapkan sebagai foreign key. JIka lebih dari satu kolom, maka bisa dipisahkan dengan menggunakan koma. Parameter `$refTable` dan `$refColumns` menentukan nama dan kolom table yang akan menjadi reference foreign key. Parameter `$delete` dan `$update` menentukan opsi `ON DELETE` dan `ON UPDATE` dalam statement SQL. Kebanyakan DBMS mendukung opsi ini:`RESTRICT`, `CASCADE`, `NO ACTION`, `SET DEFAULT`, `SET NULL`.  Query builder akan memberikan quote pada nama table, nama index dan nama kolom.

Berikut contoh menambah foreign key constraint,

~~~
[php]
// ALTER TABLE `tbl_profile` ADD CONSTRAINT `fk_profile_user_id`
// FOREIGN KEY (`user_id`) REFERENCES `tbl_user` (`id`)
// ON DELETE CASCADE ON UPDATE CASCADE
addForeignKey('fk_profile_user_id', 'tbl_profile', 'user_id',
	'tbl_user', 'id', 'CASCADE', 'CASCADE')
~~~


###dropForeignKey()

~~~
[php]
function dropForeignKey($name, $table)
~~~

Method [dropForeignKey()|CDbCommand::dropForeignKey] membuat dan menjalankan statement SQL untuk menghapus sebuah foreign key constraint. Parameter `$name` menentukan nama dari foreign key constraint yang akan dihapus. Parameter `$table` menentukan nama table yang foreign key constraintnya akan dihapus. Query builder akan memberikan quote pada nama table dan juga nama constraint.

Berikut contoh menghapus foreign key constraint:

~~~
[php]
// ALTER TABLE `tbl_profile` DROP FOREIGN KEY `fk_profile_user_id`
dropForeignKey('fk_profile_user_id', 'tbl_profile')
~~~


###createIndex()

~~~
[php]
function createIndex($name, $table, $column, $unique=false)
~~~

Method [createIndex()|CDbCommand::createIndex] membuat dan menjalankan statement SQL untuk membuat sebuah index. Parameter `$name` menentukan nama index yang akan dibuat. Parameter `$table` menentukan nama table yang index-nya berada. Parameter `$column` menentukan nama kolom yang akan di-indeks. Dan parameter `$unique` menentukan apakah harus membuat unique index. Jika index terdiri dari beberapa kolom, maka harus dipisah dengan koma. Query builder akan memberikan quote pada nama table, nama index dan nama kolom.

Berikut contoh pembuatan index:

~~~
[php]
// CREATE INDEX `idx_username` ON `tbl_user` (`username`)
createIndex('idx_username', 'tbl_user')
~~~


###dropIndex()

~~~
[php]
function dropIndex($name, $table)
~~~

Method [dropIndex()|CDbCommand::dropIndex] membuat dan mengeksekusi statement SQL untuk menghapus indeks. Parameter `$name` menentukan nama dari yang index-nya akan dihapus. Parameter `$table` menentukan nama table yang index-nya akan dihapus. Query builder akan memberikan quote pada nama table sekaligus nama index.

Below is an example showing how to drop an index:

~~~
[php]
// DROP INDEX `idx_username` ON `tbl_user`
dropIndex('idx_username', 'tbl_user')
~~~

<div class="revision">$Id: database.query-builder.txt 3408 2011-09-28 20:50:28Z alexander.makarow $</div>
