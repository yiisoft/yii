Membuat Portolet Menu User
==========================

Berdasarkan pada analisis kebutuhan, kita memerlukan tiga portlet berbeda: portlet "menu user", portlet "tag cloud" dan portlet "comment terbaru". Kita akan membuat ketiga portlet ini dengan menurunkan widget [CPortlet] yang disediakan Yii.

Dalam seksi ini, kita akan membuat portlet konkrit pertama - portlet menu user yang akan menampilkan daftar item menu yang hanya ada pada user yang sudah diotentikasi. Menu tersebut terdiri atas empat item.

 * Menyetujui Comment: sebuah hyperlink yang menuju ke daftar comment yang statusnya masih menunggu persetujuan.
 * Membuat Post Baru : sebuah hyperlink yang menuju ke halaman pembuatan post
 * Menangani Post : sebuah hyperlink yang menuju halaman manajemen post
 * Logout : sebuah tombol link yang akan me-logout-kan user saat ini.


Pembuatan Kelas `UserMenu`
-------------------------

Kita membuat kelas `UserMenu` untuk mewakili bagian logis dari portlet menu user. Kelas ini disimpan di file `/wwwroot/blog/protected/components/UserMenu.php` yang isinya sebagai berikut:

~~~
[php]
Yii::import('zii.widgets.CPortlet');

class UserMenu extends CPortlet
{
	public function init()
	{
		$this->title=CHtml::encode(Yii::app()->user->name);
		parent::init();
	}

	protected function renderContent()
	{
		$this->render('userMenu');
	}
}
~~~

Kelas `UserMenu` diturunkan dari kelas `CPortlet` dari pustaka `zii`. Kelas ini mengoveride method `init()` dan method `renderContent()` dari CPortlet. Method `init()` mengeset judul dari portlet sesuai nama user saat ini; sedangkan method `renderContent()` men-generate isi dengan me-render sebuah view bernama `userMenu`.

> Tip|Tips : Perhatikan bahwa kita sudah meng-include kelas `CPortlet` secara eksplisit dengan memanggil `Yii::import()` sebelum merujuk padanya untuk pertama kali. Ini dikarenakan `CPortlet` merupakan bagian dari projek `zi` -- pustaka extension resmi untuk Yii. Untuk pertimbangan kinerja, kelas-kelas dalam projek ini bukan bagian dari kelas inti. Oleh karenanya, kita harus meng-import-kannya sebelum untuk pertama kalinya digunakan.


Membuat View `userMenu`
------------------------

Selanjutnya, kita membuat view `userMenu` yang disimpan di dalam file `/wwwroot/blog/protected/components/views/userMenu.php`:

~~~
[php]
<ul>
	<li><?php echo CHtml::link('Create New Post',array('post/create')); ?></li>
	<li><?php echo CHtml::link('Manage Posts',array('post/admin')); ?></li>
	<li><?php echo CHtml::link('Approve Comments',array('comment/index'))
		. ' (' . Comment::model()->pendingCommentCount . ')'; ?></li>
	<li><?php echo CHtml::link('Logout',array('site/logout')); ?></li>
</ul>
~~~

> Info: Secara default, file view untuk sebuah widget harus diletakkan di dalam sub-direktori `views` dari direktori yang berisi file kelas widget. Nama file harus sama dengan nama view.


Menggunakan Portlet `UserMenu`
------------------------

Saatnya kita menggunakan portlet `UserMenu` yang baru saja dibuat. Kita modifikasi file view layout `/wwwroot/blog/protected/views/layouts/column2.php` menjadi berikut:

~~~
[php]
......
<div id="sidebar">
	<?php if(!Yii::app()->user->isGuest) $this->widget('UserMenu'); ?>
</div>
......
~~~

Di atas, kita memanggil method `widget()` untuk menghasilkan dan mengeksekusi instance dari kelas `UserMenu1. Karena portlet harus ditampilkan kepada user yang terotentikasi, kita memangil `widget()` hanya ketika properti `isGuest` pada user saat ini bernilai false (yang artinya dia adalah user yang terotentikasi).


Uji Coba Portlet `UserMenu`
--------------------------

Ayo kita mencoba apa yang ada sejauh ini.

 1. Buka sebuah window browser dan masukkan URL `http://www.example.com/blog/index.php`. Pastikan bahwa tidak ada yang tertampilkan di bagian samping dari halaman.
 2. Klik hyperlink 'Login' dan isi form login untuk login. Jika sukses, verifikasi bahwa `portlet` `UserMenu` muncul di sisi sebelah kanan dan portlet memiliki username di bagian judulnya
 3. Klik hyperlink 'Logout' di dalam portlet `UserMenu`. Verifikasi bahwa action logout sukses dan portlet `UserMenu` hilang


Kesimpulan
-------

Kita sudah membuat sebuah portlet yang tingkat reusable-nya tinggi. Kita dapat dengan gampang memakai kembali di projek berlainan dengan projek yang berbeda dengan sedikit atua tanpa modifikasi. Lebih lanjut, desain dari portlet ini mengikuti  sedekat mungkin filosofi bahwa logika dan presentasi harus dipisahkan. Walaupun kita tidak menunjukkan ini di tutorial bagian sebelumnya, practice ini hampir digunakan di mana saja dalam aplikasi Yii dalam tipikal

<div class="revision">$Id: portlet.menu.txt 1739 2010-01-22 15:20:03Z qiang.xue $</div>