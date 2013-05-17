Membuat Model
=============

Sebelum menulis kode HTML yang diperlukan oleh sebuah form, kita harus menetapkan jenis
data apa yang diharapkan dari pengguna akhir dan aturan apa pada data ini harus
diterapkan. Kelas model dapat dipakai guna menyimpan informasi ini. Model,
seperti yang didefinisikan dalam subseksi [Model](/doc/guide/basics.model), adalah
tempat utama untuk menyimpan  input pengguna dan memvalidasinya.

Tergantung pada bagaimana kita menggunakan input pengguna, kita bisa membuat dua jenis
model. Jika input pengguna dikumpulkan, dipakai dan kemudian diabaikan, kita bisa
membuat [model form](/doc/guide/basics.model); jika input pengguna
dikumpulkan dan disimpan ke dalam database, sebaliknya kita dapat menggunakan [active
record](/doc/guide/database.ar). Kedua jenis model berbagi basis kelas
[CModel] yang sama yang mendefinisikan antar muka umum yang diperlukan oleh form.

> Note|Catatan: Kita menggunakan model form terutama dalam contoh pada bagian ini.
Akan tetapi, hal yang sama bisa juga diterapkan pada model [active
record](/doc/guide/database.ar).

Mendefinisikan Kelas Model
--------------------------

Di bawah ini kita membuat kelas model `LoginForm` yang dipakai untuk mengumpulkan input pengguna pada
halaman login. Karena informasi login hanya dipakai untuk mengotentikasi pengguna
dan tidak perlu menyimpan, kita membuat `LoginForm` sebagai sebuah model form.

~~~
[php]
class LoginForm extends CFormModel
{
	public $username;
	public $password;
	public $rememberMe=false;
}
~~~

Tiga atribut dideklarasikan dalam `LoginForm`: `$username`, `$password` dan
`$rememberMe`. Ini dipakai untuk memelihara nama pengguna dan kata sandi
yang dimasukkan, dan opsi apakah pengguna menginginkan untuk mengingat login-nya.
Karena `$rememberMe` memiliki nilai standar `false`, opsi terkait
saat awal ditampilkan dalam form login tidak akan dicentang.

> Info: Alih-alih memanggil properi variabel anggota ini, kita menggunakan
nama *attributes* untuk membedakannya dari properti normal. Atribut
adalah properti yang terutama dipakai untuk menyimpan data yang berasal dari
input pengguna atau database.

Mendeklarasikan Aturan Validasi
-------------------------------

Setelah pengguna mengirimkan inputnya dan model sudah dipopulasi, kita perlu
memastikan bahwa input benar sebelum menggunakannya. Ini dikerjakan dengan
melakukan validasi input terhadap satu set aturan. Kita menetapkan aturan
validasi dalam metode `rules()` yang harus mengembalikan array konfigurasi
aturan.

~~~
[php]
class LoginForm extends CFormModel
{
	public $username;
	public $password;
	public $rememberMe=false;

	private $_identity;

	public function rules()
	{
		return array(
			array('username, password', 'required'),
			array('rememberMe', 'boolean'),
			array('password', 'authenticate'),
		);
	}

	public function authenticate($attribute,$params)
	{
		$this->_identity=new UserIdentity($this->username,$this->password);
		if(!$this->_identity->authenticate())
			$this->addError('password','Incorrect username or password.');
	}
}
~~~

Contoh kode di atas menetapkan bahwa `username` dan `password` keduanya diperlukan,
`password` harus diotentikasi.

Setiap aturan yang dikembalikan oleh `rules()` harus dalam format berikut:

~~~
[php]
array('AttributeList', 'Validator', 'on'=>'ScenarioList', ...opsi tambahan)
~~~

di mana `AttributeList` adalah string nama atribut yang dipisahkan dengan koma
yang perlu divalidasi sesuai dengan aturan; `Validator` menetapan jenis validasi
apa yang harus dilakukan; parameter `on` adalah opsional yang menetapkan daftar
skenario di mana aturan harus diterapkan; dan opsi tambahan adalah pasangan
nama-nilai yang dipakai untuk menginisialisasi nilai properti validator
terkait.

Ada tiga cara untuk menetapkan `Validator` dalam aturan validasi. Pertama,
`Validator` dapat berupa nama metode dalam kelas model, seperti
`authenticate` dalam contoh di atas. Metode validator harus berupa tanda tangan
berikut:

~~~
[php]
/**
 * @param string $attribute nama atribut yang akan divalidasi
 * @param array $params opsi yang ditentukan di dalam peraturan validasi
 */
public function ValidatorName($attribute,$params) { ... }
~~~

Kedua, `Validator` dapat berupa nama kelas validator. Saat aturan diterapkan,
instance kelas validator akan dibuat untuk melakukan validasi sebenarnya.
Opsi tambahan dalam aturan dipakai untuk menginisialisasi nilai atribut
instancenya. Kelas validator harus diperluas
dari [CValidator].

Ketiga, `Validator` dapat berupa alias pradefinisi untuk kelas validator. Dalam
contoh di atas, nama `required` adalah alias untuk [CRequiredValidator]
yang memastikan nilai atribut yang divalidasi tidak kosong. Di bawah ini
adalah daftar lengkap alias pradefinisi validator aliases:

   - `boolean`: alias [CBooleanValidator], memastikan atribut memiliki
nilai baik berupa [CBooleanValidator::trueValue] ataupun
[CBooleanValidator::falseValue].

   - `captcha`: alias [CCaptchaValidator], memastikan atribut sama dengan
kode verifikasi yang ditampilkan dalam
[CAPTCHA](http://en.wikipedia.org/wiki/Captcha).

   - `compare`: alias [CCompareValidator], memastikan atribut sama dengan
atribut atau konstan lain.

   - `email`: alias [CEmailValidator], memastikan atribut berupa alamat
email yang benar.

   - `date`: alias [CDateValidator], memastikan atribut mewakili nilai
tanggal, waktu atau tanggal waktu yang valid.

   - `default`: alias [CDefaultValueValidator], menempatkan nilai standar
ke atribut yang ditetapkan.

   - `exist`: alias [CExistValidator], memastikan nilai atribut dapat
ditemukan dalam kolom tabel.

   - `file`: alias [CFileValidator], memastikan atribu berisi nama file
yang di-upload.

   - `filter`: alias [CFilterValidator], mengubah atribut dengan
filter.

   - `in`: alias [CRangeValidator], memastikan data ada diantara
daftar nilai yang sudah ditetapkan.

   - `length`: alias [CStringValidator], memastikan panjang data
di dalam jangkauan tertentu.

   - `match`: alias [CRegularExpressionValidator], memastikan data
sesuai dengan ekspresi reguler.

   - `numerical`: alias [CNumberValidator], memastikan data adalah
angka yang benar.

   - `required`: alias [CRequiredValidator], memastikan atribut
tidak kosong.

   - `type`: alias [CTypeValidator], memastikan atribut adalah
jenis data tertentu.

   - `unique`: alias [CUniqueValidator], memastikan data adalah unik dalam
kolom tabel database.

   - `url`: alias [CUrlValidator], memastikan data berupa URL yang benar.

Di bawah ini daftar beberapa contoh pemakaian validator pradefinisi:

~~~
[php]
// username diperlukan
array('username', 'required'),
// username harus antara 3 dan 12 karakter
array('username', 'length', 'min'=>3, 'max'=>12),
// saat dalam skenario registrasi, password harus sama dengan password2
array('password', 'compare', 'compareAttribute'=>'password2', 'on'=>'register'),
// saat dalam skenario login, password harus diotentikasi
array('password', 'authenticate', 'on'=>'login'),
~~~


Mengamankan Penempatan Atribut
------------------------------

Setelah instance model dibuat, seringkali kita perlu mempopulasikan atributnya
dengan data yang dikirimkan oleh pengguna-akhir. Ini bisa dikerjakan
dengan nyaman menggunakan massive assignment masal berikut:

~~~
[php]
$model=new LoginForm;
if(isset($_POST['LoginForm']))
	$model->attributes=$_POST['LoginForm'];
~~~

Statement terakhir adalah *massive assignment* yang menempatkan setiap entri
dalam `$_POST['LoginForm']` ke atribut model bersangkutan dalam skenario
`login`. Ini sama dengan assignment berikut:

~~~
[php]
foreach($_POST['LoginForm'] as $name=>$value)
{
	if($name is a safe attribute)
		$model->$name=$value;
}
~~~

Sangat krusial untuk menentukan apakah atribut safe (aman) atau tidak. Misalnya,
mengekspos primary key suatu tabel menjadi safe, maka penyerang
memiliki kesempatan mengubah primary key dari record yang diberikan dan sehingga
memalsukan data yang seharusnya dia tidak memiliki hak untuk itu.


###Deklarasi Atribut Safe

Sebuah atribut dianggap aman jika muncul dalam rule validasi
yang dapat diaplikasikan pada skenario yang diberikan. Misalnya,

~~~
[php]
array('username, password', 'required', 'on'=>'login, register'),
array('email', 'required', 'on'=>'register'),
~~~

Dalam contoh di atas, atribut `username` dan `password` diperlukan dalam skenario `login`
sedangkan atribut `username`, `password` dan `email` diperlukan dalam skenario `register`. 
Sebagai hasilnya, jika kita melakukan penempatan massal (massive assign) di dalam skenario
`login`, hanya `username` dan `password` yang akan ditempatkan secara massal karena
hanya mereka yang muncul dalam aturan validasi untuk `login`. Di lain sisi, jika skenarionya adalah
`register`, maka ketiga atribut dapat
ditempatkan secara massal semuanya.

~~~
[php]
// in login scenario
$model=new User('login');
if(isset($_POST['User']))
	$model->attributes=$_POST['User'];

// in register scenario
$model=new User('register');
if(isset($_POST['User']))
	$model->attributes=$_POST['User'];
~~~

Jadi mengapa kita menggunakan kebijakan demikian untuk menentukan apakah sebuah atribut aman atau tidak?
Logika dibelakangnya adalah jika sebuah atribut sudah memiliki satu atau beberapa aturan validasi
untuk mengecek validasinya, apa yang harus kita khawatirkan lagi?

Sangat penting untuk diingat bahwa aturan validasi digunakan untuk mengecek data inputan user
alih-alih data yang dihasilkan kita di dalam kode (misalnya tanggal waktu, primary key yang di-generate otomatis).
Oleh karenanya, JANGAN menambah aturan validasi untuk atribut yang tidak pernah diharapkan untuk diinput
dari end-user.

Kadangkala, kita ingin mendeklarasi sebuah atribut safe, walaupun kita tidak memiliki aturan
spesifik padanya. Misalnya sebuah konten artikel yang bisa menerima inputan apapun dari user.
Kita dapat menggunakan aturan `safe` khusus untuk mendapatkan tujuan ini :

~~~
[php]
array('content', 'safe')
~~~

Untuk lebih lengkap, terdapat aturan `unsafe` juga yang digunakan secara eksplisit
untuk mendeklarasi sebuah atribut tidak aman.

~~~
[php]
array('permission', 'unsafe')
~~~

Peraturan `unsafe` jarang digunakan, dan merupakan perkecualian untuk definisi atribut
safe sebelumnya.


Untuk entri data yang tidak aman, kita perlu menempatkannya ke atribut
bersangkutan menggunakan individual assignment statement, seperti berikut:

~~~
[php]
$model->permission='admin';
$model->id=1;
~~~


Memicu Validasi
---------------

Setelah model dipopulasi dengan data yang dikirimkan pengguna, kita memanggil [CModel::validate()]
untuk memicu proses validasi data. Metode mengembalikan nilai yang menunjukan
apakah validasi sukses atau tidak. Untuk model [CActiveRecord],
validasi juga dapat dipicu secara otomatis saat kita memanggil metode
[CActiveRecord::save()].

Kita dapat mengeset skenario dengan properti [scenario|CModel::scenario] dan
dari situ mengindikasikan aturan validasi yang mana yang harus diaplikasikan.


Validasi dilakukan berdasarkan skenario. Properti [scenario|CModel::scenario]
mentukan skenario model mana yang digunakan dan aturan validasi mana yang
Misalnya, untuk skenario `login`, kita hanya ingin
memvalidasi input `username` dan `password` pada user model; sedangkan pada skenario
`register`, kita peru memvalidasi inputan yang lebih banyak seperti `email`,`address` dan lain-lain.
Contoh berikut menunjukkan bagaimana melakukan validasi pada skenario `register`:

~~~
[php]
// creates a User model in register scenario. It is equivalent to:
// $model=new User;
// $model->scenario='register';
$model=new User('register');

// populates the input values into the model
$model->attributes=$_POST['User'];

// performs the validation
if($model->validate())   // if the inputs are valid
    ...
else
    ...
~~~

Skenario yang dapat diaplikasikan pada aturan bersangkutan dapat dispesifikasikan
dengan opsi `on` pada aturan. Jika opsi `on` tidak diset, artinya aturan bersangkutan
akan digunakan di seluturh skenario. Contohnya,

~~~
[php]
public function rules()
{
	return array(
		array('username, password', 'required'),
		array('password_repeat', 'required', 'on'=>'register'),
		array('password', 'compare', 'on'=>'register'),
	);
}
~~~

Aturan pertama akan diaplikasikan pada semua skenario,
sedangkan dua aturan berikutnya diaplikasikan pada skenario `register`.


Mengambil Kesalahan Validasi
----------------------------

Begitu validasi dilakukan, apabila terdapat kesalahan (error) akan disimpan
dalam objek model. Kita bisa mengambil pesan error dengan memanggil fungsi [CModel::getErrors()]
dan [CModel::getError()]. Perbedaan antara kedua metode ini adalah yang pertama
akan mengembalikan *semua* error untuk atribut model bersangkutan
sedangkan yang metode yang kedua akan mengembalikan error *pertama*.

Label Atribut
-------------

Ketika medesain sebuah form, seringkali kita perlu menampilkan label untuk setiap field
input. Label memberitahu pengguna jenis informasi apa yang harus dimasukkan
ke dalam field. Meskipun kita dapat memberi label secara langsung dalam sebuah tampilan,
akan lebih fleksibel dan nyaman jika kita menetapkannya dalam
model terkait.

Secara default, [CModel] akan mengembalikan nama atribut sebagai labelnya.
Ini dapat dikustomisasi dengan meng-override metode
[attributeLabels()|CModel::attributeLabels]. Seperti yang akan kita lihat dalam
subbagian berikutnya, menetapkan label dalam model memungkinkan kita untuk membuat
form lebih cepat dan powerful.

<div class="revision">$Id: form.model.txt 3482 2011-12-13 09:41:36Z mdomba $</div>