Mengotentikasi User
===================

Aplikasi blog kita perlu membedakan pemilik sistem dengan user tamu. Oleh karena itu, kita harus mengimplementasikan fitur [autentikasi user](http://www.yiiframework.com/doc/guide/topics.auth).

Seperti yang mungkin sudah anda temukan bahwa aplikasi kerangka sudah menyediakan otentikasi user dengan mengecek apakah username dan password sama-sama `demo` atau sama-sama `admin`. Untuk bagian ini, kita akan memodifikasi kode bersangkutan supaya otentikasi dilakukan terhadap tabel database `User`.

Otentikasi user dilakukan di dalam sebuah kelas yang meng-implement interface [IUserIdentity]. Aplikasi skeleton menggunakan kelas `UserIdentity` untuk tujuan ini. Kelas disimpan di dalam file `/wwwroot/blog/protected/components/UserIdentity.php`.

> Tip: Sesuai konvensi, nama file kelas harus sama dengan nama kelas yang diakhiri dengan ekstensi `.php`. Dengan mengikuti konvensi ini, siapapun dapat merujuk sebuah kelas cukup dengan sebuah [path alias](http://www.yiiframework.com/doc/guide/basics.namespace). Misalnya, kita dapat merujuk kelas `UserIdentity` dengan menggunakan alias `application.components.UserIdentity`. Banyak API di dalam Yii mengenali alias path (misalnya [Yii::createComponent()||YiiBase::createComponent]), dan menggunakan path alias akan menhindari kewajiban untuk meng-embed path file absolut ke dalam kode. Kalau misalnya diembed path file abslut maka akan menyebabkan masalah ketika memasang aplikasi.

Kita memodifikasi kelas `UserIdentity` sebagai berikut,

~~~
[php]
<?php
class UserIdentity extends CUserIdentity
{
	private $_id;

	public function authenticate()
	{
		$username=strtolower($this->username);
		$user=User::model()->find('LOWER(username)=?',array($username));
		if($user===null)
			$this->errorCode=self::ERROR_USERNAME_INVALID;
		else if(!$user->validatePassword($this->password))
			$this->errorCode=self::ERROR_PASSWORD_INVALID;
		else
		{
			$this->_id=$user->id;
			$this->username=$user->username;
			$this->errorCode=self::ERROR_NONE;
		}
		return $this->errorCode==self::ERROR_NONE;
	}

	public function getId()
	{
		return $this->_id;
	}
}
~~~

Dalam method `authenticate()`, kita menggunakan kelas `User` untuk mencari sebaris dalam tabel `tbl_user` yang kolom `username` sama dengan username yang diberikan tanpa memperhatikan case-sensitive. Ingat kelas `User` dibuat dengan menggunakan tool `gii` pada bagian sebelumnya. Di karenakan kelas `User` diturunkan dari [CActiveRecord], kita dapat memanfaatkan [fitur CActiveRecord](http://www.yiiframework.com/doc/guide/database.ar) untuk mengakses tabel `tbl_user` dengan gaya OOP.

Guna mengecek apakah user telah memasukkan sebuah password yang valid, kita memanggil method `validatePassword` dari kelas `User`. Kita perlu memodifikasi file `/wwwroot/blog/protected/models/User.php` menjadi berikut ini. Sebelumnya, harap diperhatikan bahwa alih-alih menyimpan password polos di dalam database, kita simpan hasil hash dari password dan sebuah salt yang dihasilkan secara random. Ketika validasi password yang dimasukkan user, kita harus membandingkan hasil hash.

~~~
[php]
class User extends CActiveRecord
{
	......
	public function validatePassword($password)
	{
		return $this->hashPassword($password,$this->salt)===$this->password;
	}

	public function hashPassword($password,$salt)
	{
		return md5($salt.$password);
	}
}
~~~

Di dalam kelas `UserIdentity`, kita juga meng-override method `getId()` yang mengembalikan nilai `id` dari user yang ditemukan di tabel `tbl_user`. Implementasi kelas induk akan mengembalikan username. Baik properti `username` dan `id` akan disimpan di dalam session user dan bisa diakses dengan `Yii::app()->user` dari manapun di kode kita.

> Tip: Di dalam kelas `UserIdentity`, kita mereferensikan kelas [CUserIdentity] tanpa secara eksplisit meng-include file tersebut. Ini dikarenakan [CUserIdentity] adalah kelas inti yang disediakan oleh framework Yii. Yii akan meng-include file kelas secara otomatis untuk setiap kelas inti yang direferensikan pada saat pertama kali.
>
> Kita juga melakukan hal yang sama pada kelas `User`. Ini dikarenakan file kelas `User` diletakkan di dalam direktori `/wwwroot/blog/protected/models` yang telah ditambahkan ke `include_path` PHP menurut baris yang ditemukan di dalam konfigurasi aplikasi berikut:
>
> ~~~
> [php]
> return array(
>     ......
>     'import'=>array(
>         'application.models.*',
>         'application.components.*',
>     ),
>     ......
> );
> ~~~
>
> Konfigurasi di atas menjelaskan bahwa setiap kelas yang filenya terletak entah di `/wwwroot/blog/protected/models` atau `/wwwroot/blog/protected/components` akan disertakan secara otomatis ketika kelas direferensikan untuk pertama kalinya.

Kelas `UserIdentity` pada umumnya digunakan oleh kelas `LoginForm` untuk otentikasi seorang user berdasarkan username dan password yang diinput dari halaman login. Berikut merupakan bagian-bagian dari kode yang menunjukkan bagaimana `UserIdentity` digunakan:

~~~
[php]
$identity=new UserIdentity($username,$password);
$identity->authenticate();
switch($identity->errorCode)
{
	case UserIdentity::ERROR_NONE:
		Yii::app()->user->login($identity);
		break;
	......
}
~~~

> Info: Orang-orang sering dibingungkan dengan komponen aplikasi identity dengan `user`. Identity mewakili cara melakukan otentikasi, sedangkan `user` digunakan untuk mewakili informasi yang berhubungan dengan user sekarang. Sebuah aplikasi hanya dapat memiliki sebuat komponen `user`, tetapi bisa memiliki kelas identity satu atau lebih, tergantung pada apa otentikasi apa yang didukung. Begitu terotentikasi, sebuah instance identity akan di-pass state information-nya kepada komponen `user` sehingga dapat diakses secara global melalui `user`.

Untuk mencoba kelas `UserIdentity` yang telah termodifikasi, kita dapat browse URL `http://www.example.com/blog/index.php` dan mencoba melakukan login dengan username dan password yang tersimpan di dalam tabel `tbl_user`. Jika kita menggunakan database yang disediakan [demo blog](http://www.yiiframework.com/demos/blog/), kita seharusnya bisa login dengan username `demo` dan password `demo`. Perhatikan bahwa sistem blog ini tidak menyediakan fitur manajemen user. Akibatnya, seorang user tidak bisa mengubah account-nya ataupun membuat satu yang baru melalui aplikasi Web bersangkutan. Fitur manajemen user dianggap sebagai peningkatan kedepannya pada aplikasi blog.

<div class="revision">$Id: prototype.auth.txt 2333 2010-08-24 21:11:55Z mdomba $</div>