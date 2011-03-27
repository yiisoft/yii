Membuat Aksi
============

Setelah kita memiliki model, kita dapat mulai menulis logika yang diperlukan
untuk memanipulasi model. Kita tempatkan logika ini di dalam sebuah aksi controller.
Untuk contoh form login, kode berikut diperlukan:

~~~
[php]
public function actionLogin()
{
	$model=new LoginForm;
	if(isset($_POST['LoginForm']))
	{
		// mengumpulkan data inputan dari user
		$model->attributes=$_POST['LoginForm'];
		// validasi inputan user dan redirect kembali ke halaman sebelumnya jika valid.
		if($model->validate())
			$this->redirect(Yii::app()->user->returnUrl);
	}
	// menampilkan form login.
	$this->render('login',array('model'=>$model));
}
~~~

Dalam contoh di atas, pertama kita membuat instance `LoginForm`; jika permintaannya adalah
permintaan POST (berarti form login dikirimkan), kita mempopulasikan `$form`
dengan data yang dikirimkan `$_POST['LoginForm']`; kemudian kita memvalidasi input
dan jika sukses, mengalihkan browser pengguna ke halaman sebelumnya yang
memerlukan otentikasi. Jika validasi gagal, atau jika aksi diakses dari
awal, kita menyajikan tampilan `login` di mana isinya akan dijelaskan dalam
subseksi berikut.

> Tip: Dalam aksi `login`, kita menggunakan `Yii::app()->user->returnUrl` untuk mendapatkan
URL halaman sebelumnya yang memerlukan otentikasi. Komponen
`Yii::app()->user` adalah jenis [CWebUser] (atau kelas turunannya) yang
mewakili informasi sesi pengguna (misalnya username, status). Untuk lebih jelasnya,
lihat [Otentikasi dan Otorisasi](/doc/guide/topics.auth).

Mari kita perhatikan pernyataan PHP berikut yang muncul dalam aksi
`login`:

~~~
[php]
$model->attributes=$_POST['LoginForm'];
~~~

Seperti yang kami jelaskan dalam [Mengamankan Penempatan Atribut](/doc/guide/form.model#securing-attribute-assignments),
baris kode ini mempopulasi model dengan data yang dikirimkan pengguna.
Properti `attributes` didefinisikan oleh [CModel] yang
mengharapkan array pasangan nama-nilai dan menempatkan setiap nilai ke
atribut model terkait. Maka jika `$_POST['LoginForm']` menghasilkan
array seperti itu, kode di atas akan sama dengan kode panjang berikut
(menganggap setiap atribut ada dalam array):

~~~
[php]
$model->username=$_POST['LoginForm']['username'];
$model->password=$_POST['LoginForm']['password'];
$model->rememberMe=$_POST['LoginForm']['rememberMe'];
~~~

> Note|Catatan: Agar `$_POST['LoginForm']` menghasilkan array alih-alih
string, kita tetap pada konvensi penamaan field input dalam tampilan. Pada
keadaan tertentu, sebuah field input berkaitan dengan atribut `a` pada kelas model
`C`, kita namai sebagai `C[a]`. Sebagai contoh, kita ingin menggunakan
`LoginForm[username]` untuk menamai field input yang berkaitan dengan atribut
`username`.

Tugas selanjutnya sekarang adalah membuat tampilan `login` yang harus berisi
form HTML dengan field input yang dibutuhkan.

<div class="revision">$Id: form.action.txt 1837 2010-02-24 22:49:51Z qiang.xue $</div>