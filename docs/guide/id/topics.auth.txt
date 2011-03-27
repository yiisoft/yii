Otentikasi dan Otorisasi
========================

Otentikasi dan otorisasi diperlukan pada halaman Web yang dibatasi
untuk para pengguna tertentu. Otentikasi adalah verifikasi apakah
seseorang itu adalah orang yang berhak. Biasanya melibatkan username dan
password, tapi dapat menyertakan metode lain yang menunjukan identitas, seperti
kartu pintar, sidik jari, dll. Otorisasi adalah pencarian apakah
orang yang sudah diidentifikasi (diotentikasi), diijinkan untuk memanipulasi
sumber daya tertentu. Ini biasanya ditentukan dengan mencari apakah orang
itu merupakan bagian dari aturan khusus yang memiliki akses ke sumber daya.

Yii memiliki kerangka kerja bawaan otentikasi/otorisasi yang mudah
untuk dipakai dan dapat dikustomisasi untuk kebutuhan khusus.

Bagian utama dari kerangka kerja auth Yii adalah pra-deklarasi *komponen
aplikasi user*, berupa obyek yang mengimplementasikan antar muka [IWebUser].
Komponen user mewakili informasi identitas bagi pengguna saat ini. Kita dapat
mengaksesnya di mana saja menggunakan
`Yii::app()->user`.

Memanfaatkan komponen user, kita dapat memeriksa apakah pengguna sudah masuk atau tidak via
[CWebUser::isGuest]; kita bisa [memasukan|CWebUser::login] dan
[mengeluarkan|CWebUser::logout] seorang pengguna; kita bisa memeriksa apakah pengguna dapat melakukan
operasi tertentu dengan memanggil [CWebUser::checkAccess]; dan kita juga bisa
mendapatkan [pembeda unik|CWebUser::name] serta informasi identitas persisten
lainnya mengenai pengguna.

Mendefinisikan Kelas Identitas
------------------------------

Seperti yang sudah disinggung di atas, otentikasi adalah segala sesuatu yang berhubungan dengan validasi identitas pengguna. Sebuah implementasi otentikasi pada aplikasi Web pada umumnya menyangkut kombinasi penggunaan username dan password untuk memverifikasi identitas user. Namun, otentikasi bisa juga menyertakan implementasi yang berbeda atau metode lain. Untuk mengakomodasi kebutuhan metode yang beragam, Yii Framework memperkenalkan identity class (kelas identitas)

Kita mendefinisikan sebuah kelas yang mengandung logika otentikasi yang sebenarnya. Identity class harus mengimplementasi interface [IUserIdentity]. Kelas-kelas identity dapat
diimplementasikan untuk pendekatan otentiksai yang berbeda-beda (misalnya OpenID, LDAP, Twitter OAuth, Facebook Connect). Adalah sebuah permulaan yang baik ketika menulis implementasi sendiri dengan menurunkan [CUserIdentity] yang menjadi kelas dasar untuk otentikasi dengan pendekatan penggunaan username dan password.

Pekerjaan utama dalam mendefinisikan kelas identitas adalah implementasi
metode [IUserIdentity::authenticate]. Ini merupakan metode yang digunakan untuk mengenkapsulasi detail utama pada pendekatan otentikasi. Kelas identity juga dapat mendeklarasikan
informasi identitas tambahan yang perlu tetap ada (persistent) selama sesi
pengguna.

###Sebuah Contoh

Pada contoh berikut, kita akan menggunakan kelas identity untuk menunjukkan pendekatan database untuk otentikasi. Cara ini merupakan cara paling umum pada aplikasi Web. Seorang user akan memasukkan username dan password ke dalam form login, dan kemudian kita, dengan menggunakan [ActiveRecord](/doc/guide/database.ar) akan memvalidasi data rahasia tersebut dengan membandingkannya pada tabel user di dalam database. Sebetulnya ada beberapa hal lain yang akan didemokan pada contoh berikut :

1. Mengimplementasikan fungsi `authenticate()` untuk menggunakan database dalam memvalidasi data pribadi.
2. Meng-override metode `CUserIdentity::getId()` untuk mengembalikan properti `_id` karena implementasi default mengembalikan username sebagai ID.
3. Menggunakan metode `setState()` ([CBaseUserIdentity::setState]) untuk menunjukkan penyimpanan informasi lain yang dapat diambil dengan mudah pada request berikutnya.

~~~
[php]
class UserIdentity extends CUserIdentity
{
	private $_id;
	public function authenticate()
	{
		$record=User::model()->findByAttributes(array('username'=>$this->username));
		if($record===null)
			$this->errorCode=self::ERROR_USERNAME_INVALID;
		else if($record->password!==md5($this->password))
			$this->errorCode=self::ERROR_PASSWORD_INVALID;
		else
		{
			$this->_id=$record->id;
			$this->setState('title', $record->title);
			$this->errorCode=self::ERROR_NONE;
		}
		return !$this->errorCode;
    }

	public function getId()
	{
		return $this->_id;
	}
}
~~~

Ketika kita memahami login dan logout di bagian berikutnya, kita akan melihat bahwa kita pass kelas identity ini ke metode login untuk seorang user. Informasi yang disimpan dalam sebuah state(dengan memanggil [CBaseUserIdentity::setState]) akan di-pass ke [CWebUser] yang menyimpannya dalam bentuk penyimpanan permanen (persistent), seperti misalnya sesi.
Informasi ini dapat diakses seperti properti [CWebUser]. Sebagai contoh, kita menyimpan informasi title user via `$this->setState('title', $record->title);`. Begitu kita sudah menyelesaikan proses login kita, maka kita dapat mengambil informasi `title` dari user sekarang dengan menggunakan `Yii::app()->user->title`.

> Info: Secara default, [CWebUser] menggunakan sesi sebagai penyimpanan permanen atas informasi
identitas pengguna. Jika login berbasis-cookie dihidupkan (dengan menyetel
[CWebUser::allowAutoLogin] menjadi true), informasi identitas pengguna juga dapat
disimpan dalam cookie. Pastikan Anda tidak mendeklarasikan informasi sensitif
(misalnya kata sandi) menjadi permanen.

Login dan Logout
----------------

Sekarang kita telah melihat contoh dari pembuatan sebuah identitas user, kita menggunakan in untuk membantu kita dalam mengimplementasi kebutuhan akan aksi login dan logout. Kode berikut akan mendemonstrasikan bagaimana dilaksanakan :

~~~
[php]
// Masukkan pengguna dengan username dan password yang disediakan.
$identity=new UserIdentity($username,$password);
if($identity->authenticate())
	Yii::app()->user->login($identity);
else
	echo $identity->errorMessage;
......
// Logout-kan pengguna saat ini
Yii::app()->user->logout();
~~~

Di sini kita akan membuat sebuah kelas UserIdentity yang baru dan pass ke dalam data rahasia (seperti nilai `$username` dan `$password` yang di-submit oleh user) ke constructor. Kita cukup memanggil fungsi metode authenticate(). Jika sukses, kita akan pass informasi identitas ke metode [CWebUser::login], yang akan menyimpan informasi identitas ke tempat penyimpanan tetap (persistent) (secara default adalah sesi PHP) untuk mengambil pada saat request berikutnya. JIka otentikasi gagal, kita akan menanyakan properti `errorMessage` untuk informasi lebih lajut mengapa bisa gagal.

Apakah seorang user sudah terotentikasi atau belum dapat diperiksa dengan mudah melalui penggunaan `Yii::app()->user->isGuest`. Jika menggunakan penyimpanan tetap (persistent) seperti sesi dan/atau cookie (akan dibahas di bawah) untuk menyimpan informasi identitas, seorang user dapat tetap dalam keadaan login pada request berikutnya. Pada kasus ini, kita tidak perlu kelas UserIdentity dan seluruh proses login pada request selanjutnya. Namun CWebUser yang akan secara otomatis mengurus pengisian informasi identitas dari tempat penyimpanan tetap dan menggunakannya untuk menentukan apakah `Yii::app()->user->isGuest` akan mengembalikan true atau false.

Cookie-based Login
----------------------------------

Secara default, seorang pengguna akan di-logout-kan setelah periode waktu tertentu bila tidak aktif,
tergantung pada [konfigurasi sesi](http://www.php.net/manual/en/session.configuration.php).
Untuk mengubah perilaku ini, kita dapat menyetel properti [allowAutoLogin|CWebUser::allowAutoLogin]
pada komponen pengguna menjadi true dan mengoper parameter durasi ke dalam
metode [CWebUser::login]. Selanjutnya pengguna akan tetap masuk selama durasi yang
sudah ditetapkan, meskipun jika dia menutup jendela browser. Catatan bahwa
fitur ini mengharuskan browser pengguna untuk menerima cookie.

~~~
[php]
// Biarkan pengguna tetap masuk selama 7 hari.
// Pastikan allowAutoLogin disetel true pada komponen user.
Yii::app()->user->login($identity,3600*24*7);
~~~

Seperti yang kami singgung di atas, ketika kita mengaktifkan login berbasis cookie, 
state-state yang disimpan melalui [CBaseUserIdentity::setState] akan disimpan juga ke dalam cookie.
Ketika lain kali user tersebut login kembali, state-state ini akan dibaca dari
cookie dan menjadi dapat diakses melalui `Yii::app()->user`.

Walaupun Yii telah mempersiapkan beberapa pencegahan kemungkinan nilai state cookie dimanipulasi
pada bagian client, kami sangat menyarankan informasi yang sensitif tidak disimpan
sebagai state alih-alih menyimpan informasi tersebut pada bagian server
dengan membaca dari tempat penyimpanan di bagian server (seperti database).

Sebagai tambahan, untuk aplikasi Web serius apapun, kami merekomendasikan beberapa
strategi berikut untuk meningkatkan keamanan pada login berbasis cookie.

* Ketika seorang user berhasil login dengan mengisi form login, kita men-generate dan
menyimpan sebuah kunci acak pada state cookie dan dalam tempat penyimpanan permanen pada bagian server
(seperti database)

* Pada saat request selanjutnya, ketika otentikasi user dilakukan dengan informasi cookie, kita membandingkan dua buah
kunci acak ini dan memastikan kecocokannya sebelum me-login-kan user.

* Jika user log melalui form login lagi, sebuah kunci harus di-generate ulang.

Dengan menggunakan strategi di atas, kita sudah mengeleminasi kemungkinan seorang user menggunakan
ulang sebuah state cookie yang lama yang mungkin berisi informasi state yang lama.

Untuk mengimplementasi srategi di atas, kita perlu meng-override dua metode :

* [CUserIdentity::authenticate()]: merupakan tempat di mana otentikasi nyata dilakukan.
Jika user terotentikasi, kita harus men-generate sebuah kunci acak yang baru dan menyimpannya
ke dalam database termasuk juga dalam state identitas melalui [CBaseUserIdentity::setState].

* [CWebUser::beforeLogin()]: merupakan fungsi yang dipanggil ketika seorang user sedang diloginkan.
Kita harus mengecek apakah kunci yang didapatkan dari state cookie adalah sama dengan
yang ada di database.




Filter Kontrol Akses
--------------------

Filter kontrol akses merupakan skema awal otorisasi yang memeriksa apakah
pengguna saat ini dapat melakukan aksi controller yang diminta. Otorisasi
didasarkan pada nama pengguna, alamat IP klien dan jenis permintaan.
Ini disediakan sebagai filter bernama
["accessControl"|CController::filterAccessControl].

> Tip: Filter kontrol akses cukup untuk skenario sederhana. Untuk kontrol akses yang kompleks,
Anda dapat menggunakan role-based access (RBAC) yang akan segera dibahas.

Untuk mengontrol akses terhadap aksi dalam controller, kita menginstalasi filter
kontrol akses dengan mengoverride [CController::filters] (lihat
[Filter](/doc/guide/basics.controller#filter) untuk lebih jelasnya mengenai
instalasi filter).

~~~
[php]
class PostController extends CController
{
	......
	public function filters()
	{
		return array(
			'accessControl',
		);
	}
}
~~~

Dalam contoh di atas, kita menetapkan bahwa filter [kontrol
akses|CController::filterAccessControl] harus diterapkan pada setiap
aksi `PostController`. Rincian aturan otorisasi yang dipakai oleh
filter ditetapkan dengan meng-override [CController::accessRules] pada kelas
controller.

~~~
[php]
class PostController extends CController
{
	......
	public function accessRules()
	{
		return array(
			array('deny',
				'actions'=>array('create', 'edit'),
				'users'=>array('?'),
			),
			array('allow',
				'actions'=>array('delete'),
				'roles'=>array('admin'),
			),
			array('deny',
				'actions'=>array('delete'),
				'users'=>array('*'),
			),
		);
	}
}
~~~

Kode di atas menetapkan tiga aturan, masing-masing disajikan sebagai array.
Elemen pertama array bisa berupa `'allow'` atau `'deny'` dan sisanya pasangan
nama-nilai lainnya menetapkan parameter pola aturan. Aturan-aturan yang didefinisikan sebelumnya diterjemahkan sebagai berikut: aksi `create` dan `edit` tidak bisa dijalankan oleh pengguna
anonim; aksi `delete` dapat dijalankan oleh pengguna dengan aturan `admin`;
dan aksi `delete` tidak bisa dijalankan oleh siapapun.

Aturan akses dievaluasi satu demi satu sesuai urutan yang ditetapkan.
Aturan pertama yang sesuai dengan pola saat ini (misalnya nama pengguna, roles,
alamat IP klien) menentukan hasil otorisasi. Jika aturan ini adalah aturan `allow`,
aksi bisa dijalankan; jika aturan `deny`, aksi tidak bisa dijalankan;
jika tidak ada aturan yang sesuai konteks, aksi masih bisa 
dijalankan.

> Tip: Untuk memastikan aksi tidak bisa dijalankan pada konteks tertentu,
> sangat bermanfaat untuk selalu menetapkan semuanya sesuai aturan `deny` di akhir
> set aturan, seperti yang berikut:
> ~~~
> [php]
> return array(
>     // ... aturan lain...
>     // aturan ini menolak aksi 'delete' untuk semua konteks
>     array('deny',
>         'action'=>'delete',
>     ),
> );
> ~~~
> Alasan atas aturan ini dikarenakan jika tidak ada seorangpun yang sesuai konteks aturan, aksi akan dijalankan.


Aturan akses akan sesuai dengan parameter konteks berikut:

   - [aksi|CAccessRule::actions]: menetapkan aksi mana dijalankan jika aturan sesuai.
Ini harus berupa array ID aksi. Pembandingannya bersifat case-sensitive.

   - [controller|CAccessRule::controllers]: menetapkan controller mana yang sesuai aturan
ini. Ini harus berupa array ID controller. Pembandingannya bersifat case-sensitive.

   - [pengguna|CAccessRule::users]: menetapkan pengguna mana yang sesuai aturan ini.
[Nama|CWebUser::name] pengguna saat ini dipakai untuk penyesuaian. Pembandingannya
bersifat case-sensitive. Tiga karakter khusus dapat dipakai di sini:

	   - `*`: setiap pengguna, termasuk anonim dan pengguna terotentikasi.
	   - `?`: pengguna anonim.
	   - `@`: pengguna terotentikasi.

   - [roles|CAccessRule::roles]: menetapkan roles mana yang sesuai aturan ini.
Pemakaian fitur [kontrol akses berbasis-role](#role-based-access-control)
dijelaskan pada subseksi berikutnya. Aturan diterapkan khususnya jika
[CWebUser::checkAccess] mengembalikan true pada salah satu set aturan.
Catatan, Anda harus menggunakan set aturan terutama dalam aturan `allow` karena secara definisi,
aturan mewakili perijinan untuk melakukan sesuatu. Juga harap dicatat, meskipun kita menggunakan
batasan `roles` di sini, nilainya sebenarnya dapat berupa item otentikasi, termasuk roles,
tugas dan operasi.

   - [ip|CAccessRule::ips]: menetapkan alamat IP klien mana yang sesuai aturan
ini.

   - [verb|CAccessRule::verbs]: menetapkan jenis permintaan apa (misalnya
`GET`, `POST`) sesuai aturan ini. Pembandingannya tidak bersifat case-sensitive.

   - [ekspresi|CAccessRule::expression]: menetapkan ekspresi PHP yang nilainya menunjukan
apakah aturan ini sesuai atau tidak. Dalam ekspresi, Anda dapat menggunakan variabel `$user`
yang merujuk ke `Yii::app()->user`.


Menangani Hasil Otorisasi
--------------------------------------------

Ketika otorisasi gagal, misalnya, pengguna tidak diijinkan untuk melakukan
aksi yang ditetapkan, salah satu dari dua skenario berikut mungkin terjadi:

   - Jika pengguna tidak masuk dan jika properti [loginUrl|CWebUser::loginUrl]
komponen pengguna dikonfigurasi menjadi URL halaman login,
browser akan mengalihkan ke halaman itu. Catatan bahwa standarnya,
[loginUrl|CWebUser::loginUrl] merujuk ke halaman `site/login`.

   - Sebaliknya exception HTTP akan ditampilkan dengan kode kesalahan 401.

Ketika mengkonfigurasi properti [loginUrl|CWebUser::loginUrl], Anda dapat
menyediakan URL relatif ataupun absolut. Selain itu, Anda juga bisa menyediakan array
yang akan dipakai untuk membuat URL dengan memanggil [CWebApplication::createUrl].
Elemen pertma array harus menetapkan
[rute](/doc/guide/basics.controller#route) ke aksi controller login,
dan pasangan nama-nilai lainnya adalah parmeter GET. Sebagai contoh,

~~~
[php]
array(
	......
	'components'=>array(
		'user'=>array(
			// ini sebetulnya merupakan nilai default
			'loginUrl'=>array('site/login'),
		),
	),
)
~~~

Jika browser dialihkan ke halaman login dan login berhasil,
kita mungkin ingin mengalihkan browser kembali ke halaman yang menyebabkan
kegagalan otorisasi. Bagaimana kita mengetahui URL halaman itu? Kita bisa
memperoleh informasi ini dari properti [returnUrl|CWebUser::returnUrl]
pada komponen pengguna. Selanjutnya kita dapat melakukan pengalihan
berikut:

~~~
[php]
Yii::app()->request->redirect(Yii::app()->user->returnUrl);
~~~

Kontrol Akses Berbasis-Role
---------------------------

Role-Based Access Control (RBAC) menyediakan kontrol akses terpusat yang
sederhana dan powerful. Silahkan merujuk ke
[artikel Wiki](http://en.wikipedia.org/wiki/Role-based_access_control) untuk
lebih jelasnya mengenai perbandingan RBAC dengan skema kontrol akses
tradisional.

Yii mengimplementasikan skema RBAC berhierarki via komponen aplikasi
[authManager|CWebApplication::authManager]. Berikut ini, pertama-pertama kita
lihat konsep utama yang dipakai dalam skema ini; kemudian kita
memahami bagaimana untuk mendefinisikan data otorisasi; di akhir kita akan
melihat bagaimana menggunakan data otorisasi untuk melakukan pemeriksaan akses.

### Tinjauan

Konsep dasar dalam RBAC Yii adalah *item otorisasi*. Item otorisasi adalah
perijinan untuk melakukan sesuatu (misalnya membuat post blog baru,
mengatur pengguna). Berdasarkan granularitas dan pengguna yang ditargetkan,
item otorisasi bisa diklasifikasikan sebagai *operations (operasi)*,
*tasks (tugas)* dan *roles (peran)*. Role terdiri dari task, task terdiri dari operation,
dan operation adalah perijinan yang paling kecil.
Sebagai contoh, kita bisa memiliki sistem dengan role `administrator` yang terdiri dari
task `post management` dan task `user management`. Task `user management`
terdiri dari operation `create user`, `update user` dan `delete user`.
Agar lebih fleksibel, Yii juga mengijinkan role terdiri dari role atau operation
lain, task terdiri dari task lain, dan operation terdiri dari operation lainnya,
task terdiri dari task-task lain, dan operation terdiri dari operation-operation lainnya.

Item otorisasi secara unik diidentifikasi dengan namanya.

Item otorisasi dapat dikaitkan dengan *aturan bisnis*. Aturan bisnis adalah
bagian kode PHP yang akan dijalankan saat melakukan pemeriksaan akses
terhadap itemnya. Hanya ketika eksekusi mengembalikan nilai true,
pengguna akan dipertimbangkan memiliki perijinan yang disajikan oleh
item. Sebagai contoh, saat mendifinisikan operation `updatePost`, kita akan menambahkan
aturan bisnis yang memeriksa apakah ID pengguna sama seperti ID pemuat tulisan
agar pembuat tersebut dapat memiliki perijinan untuk memutakhirkan
tulisan.

Menggunakan item otorisasi, kita bisa membangun *hirarki
otorisasi*. Item `A` adalah induk dari item `B` lain dalam hirarki
jika `A` terdiri dari `B` (atau katakanlah `A` mewarisi perijinan
disajikan oleh `B`). Item dapat memiliki multipel item anak, dan juga bisa
memiliki multipel item induk. Oleh karena itu, hirarki otorisasi adalah
grarik urutan-bagian alih-alih sebuah pohon (susunan). Dalam hirarki ini, item role berada
tingkat atas, item operasi pada tingkat bawah, item tugas berada
diantaranya.

Setelah kita memiliki hirarki otorisasi, kita bisa menempatkan roles dalam
hirarki ini ke pengguna aplikasi. Pengguna setelah ditempatkan akan memiliki
perijinan yang disajikan oleh role. Sebagai contoh, jika kita menempatkan role
`administrator` kepada seorang pengguna, dia akan memiliki perijinan administrator
yang termasuk `post management` dan `user management` (dan operasi terkait
seperti misalnya `create user`).

Sekarang bagian yang menyenangkan. Dalam aksi controller, kita ingin memeriksa apakah
pengguna saat ini dapat menghapus tulisan yang ditetapkan. Menggunakan hirarki RBAC serta
penempatan, ini bisa dikerjakan dengan mudah seperti berikut:

~~~
[php]
if(Yii::app()->user->checkAccess('deletePost'))
{
	// hapus tulisan
}
~~~

Mengkonfigurasi Manajer Otorisasi
---------------------------------------------------------------

Sebelum kita mendefinisikan hirarki otorisasi dan melakukan pemeriksaan akses,
kita perlu mengkonfigurasi komponen aplikasi
[authManager|CWebApplication::authManager]. Yii menyediakan dua jenis
manajer otorisasi: [CPhpAuthManager] dan
[CDbAuthManager]. Yang pertama menggunakan file skrip PHP untuk menyimpan data
otorisasi, sementara yang kedua menyimpan data otorisasi dalam database. Ketika kita
mengkonfigurasi komponen aplikasi [authManager|CWebApplication::authManager],
kita harus menetapkan kelas komponen mana yang dipakai dan apa nilai
awal untuk komponennya. Sebagai contoh,

~~~
[php]
return array(
	'components'=>array(
		'db'=>array(
			'class'=>'CDbConnection',
			'connectionString'=>'sqlite:path/to/file.db',
		),
		'authManager'=>array(
			'class'=>'CDbAuthManager',
			'connectionID'=>'db',
		),
	),
);
~~~

Selanjutnya kita dapat mengakses komponen aplikasi [authManager|CWebApplication::authManager]
menggunakan `Yii::app()->authManager`.

Mendefinisikan Hirarki Otorisasi
------------------------------------------------------

Mendefinisikan hirarki otorisasi mencakup tiga langkah: mendefinisikan
item otorisasi, membuat hubungan diantara item otorisasi,
dan menempatkan role ke pengguna aplikasi. Komponen aplikasi
[authManager|CWebApplication::authManager] menyediakan seluruh set API
untuk tugas-tugas ini.

Untuk mendefinisikan item otorisasi, panggil salah satu metode berikut,
tergantung pada jenis itemnya:

   - [CAuthManager::createRole]
   - [CAuthManager::createTask]
   - [CAuthManager::createOperation]

Setelah kita memiliki satu set item otorisasi, kita dapat memanggil metode
untuk membuat hubungan diantara item-item otorisasi:

   - [CAuthManager::addItemChild]
   - [CAuthManager::removeItemChild]
   - [CAuthItem::addChild]
   - [CAuthItem::removeChild]

Dan terakhir, kita memanggil metode berikut untuk menempatkan item role ke
pengguna individual:

   - [CAuthManager::assign]
   - [CAuthManager::revoke]

Di bawah ini kami memperlihatkan contoh mengenai pembangunan hirarki otorisasi dengan
API yang sudah disediakan:

~~~
[php]
$auth=Yii::app()->authManager;

$auth->createOperation('createPost','create a post');
$auth->createOperation('readPost','read a post');
$auth->createOperation('updatePost','update a post');
$auth->createOperation('deletePost','delete a post');

$bizRule='return Yii::app()->user->id==$params["post"]->authID;';
$task=$auth->createTask('updateOwnPost','update a post by author himself',$bizRule);
$task->addChild('updatePost');

$role=$auth->createRole('reader');
$role->addChild('readPost');

$role=$auth->createRole('author');
$role->addChild('reader');
$role->addChild('createPost');
$role->addChild('updateOwnPost');

$role=$auth->createRole('editor');
$role->addChild('reader');
$role->addChild('updatePost');

$role=$auth->createRole('admin');
$role->addChild('editor');
$role->addChild('author');
$role->addChild('deletePost');

$auth->assign('reader','readerA');
$auth->assign('author','authorB');
$auth->assign('editor','editorC');
$auth->assign('admin','adminD');
~~~

Komponen [authManager|CWebApplication::authManager] (seperti [CPhpAuthManager], [CDbAuthManager])
akan menjalankan item otorisasi begitu dibuat. Kita TIDAK perlu menjalankan perintah diatas
untuk mengulangi pembuatannya di setiap permintaan.

> Info: sementara pada contoh di atas terlihat panjang dan melelahkan, ini hanya dikarenakan
untuk keperluan demonstratif saja. Para pengembang biasanya harus mengembangkan beberapa
antar muka agar pengguna akhir bisa menggunakan hirarki otorisasi
lebih intuitif.


Menggunakan Aturan Bisnis (Business Rule)
-----------------------------------------------

Ketika kita mendefinisikan hirarki otorisasi, kita dapat mengaitkan sebuah role, tugas atau operasi dengan apa yang disebut *aturan bisnis*. Kita juga dapat mengaitkan aturan bisnis saat kita menempatkan sebuah role kepada pengguna. Aturan bisnis adalah bagian dari kode PHP (teppatnya pernyataan PHP) yang dijalankan ketika kita melakukan pemeriksaan akses. Nilai kode yang dikembalikan dipakai untuk menentukan apakah role atau penempatan diterapkan pada pengguna saat ini. Dalam contoh di atas, kita mengaitkan aturan bisnis dengan tugas `updateOwnPost`. Dalam aturan bisnis, kita cukup memeriksa apakah ID pengguna saat ini sama dengan ID pembuat tulisan. Informasi tulisan dalam array `$params` disediakan oleh para pengembang saat melakukan pemeriksaan akses.


### Pemeriksaan Akses

Untuk melakukan pemeriksaan akses, pertama kita perlu mengetahui nama item
otorisasi. Sebagai contoh, untuk memeriksa apakah pengguna saat ini dapat membuat
sebuah post, kita akan memeriksa apakah dia memiliki perijinan yang disiapkan
oleh operasi `createPost`. Kemudian kita memanggil [CWebUser::checkAccess] untuk melakukan
pemeriksaan akses:

~~~
[php]
if(Yii::app()->user->checkAccess('createPost'))
{
	// buat post
}
~~~

Jika aturan otorisasi dikaitkan dengan aturan bisnis yang memerlukan
parameter tambahan, kita juga dapat mengoperkannya. Contoh, untuk
memeriksa apakah pengguna dapat memutakhirkan sebuah tulisan, kita akan melakukan

~~~
[php]
$params=array('post'=>$post);
if(Yii::app()->user->checkAccess('updateOwnPost',$params))
{
	// mengupdate tulisan
}
~~~


### Menggunakan Role Default

Banyak aplikasi Web memerlukan beberapa role sangat khusus yang ditempatkan ke
setiap pengguna atau hampir semua pengguna sistem. Sebagai contoh, kita mungkin ingin menempatkan beberapa
hak bagi para pengguna terotentikasi. Ini akan memunculkan banyak masalah pemeliharaan jika
kita secara eksplisit menetapkan dan menyimpan penempatan role tersebut. Kita dapat mengeksploitasi
*role default* guna memecahkan masalah ini.

Role default adalah sebuah role yang secara implisit ditempatkan ke setiap pengguna, termasuk
yang terotentikasi dan pengunjung. Kita tidak perlu secara eksplisit meng-assign kepada pengguna.
Saat [CWebUser::checkAccess] dipanggil, role default akan diperiksa lebih dulu seolah-olah di-assign
kepada pengguna.

Role default harus dideklarasikan dalam properti [CAuthManager::defaultRoles].
Contohnya, konfigurasi berikut mendeklarasikan dua role sebagai role standar: `authenticated` dan `guest`.

~~~
[php]
return array(
	'components'=>array(
		'authManager'=>array(
			'class'=>'CDbAuthManager',
			'defaultRoles'=>array('authenticated', 'guest'),
		),
	),
);
~~~

Karena role default ditempatkan ke setiap pengguna, biasanya perlu dikaitkan
dengan aturan bisnis yang menentukan apakah role benar-benar di-assign
kepada pengguna. Sebagai contoh, kode berikut mendefinisikan dua role,
`authenticated` dan `guest`, yang secara efektif di-assign ke masing-masing
para pengguna terotentikasi dan pengunjung.

~~~
[php]
$bizRule='return !Yii::app()->user->isGuest;';
$auth->createRole('authenticated', 'authenticated user', $bizRule);

$bizRule='return Yii::app()->user->isGuest;';
$auth->createRole('guest', 'guest user', $bizRule);
~~~

<div class="revision">$Id: topics.auth.txt 2890 2011-01-18 15:58:34Z qiang.xue $</div>
