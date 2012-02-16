Membuat dan Mengupdate Post
===========================

Dengan model `Post` yang siap, kita perlu mengatur action dan view dari controller `PostController`. Dalam bagian ini, kita pertama akan mengkustomisasi access control dari operasi CRUD, kita kemudian mengubah kode yang diimplementasi operasi `create` dan `update`.


Mengkustomisasi Access Control
--------------------------

Hal pertama yang ingin kita lakukan adalah mengkustomisasi  [access control](http://www.yiiframework.com/doc/guide/topics.auth#access-control-filter dikarenakan kode yang di-generate `yiic` tidak sesuai dengan keperluannya.

Kita memodifikasi metode `accessRule()` di dalam file `/wwwroott/blog/protected/controllers/PostController.php` sebagai berikut,

~~~
[php]
public function accessRules()
{
	return array(
		array('allow',  // memungkinkan semua user melakukan action `list` dan `show`
			'actions'=>array('index', 'view'),
			'users'=>array('*'),
		),
		array('allow', // memungkinkan user yang terotentikasi ke dalam melakukan action apapun
			'users'=>array('@'),
		),
		array('deny',  // tolak seluruh user.
			'users'=>array('*'),
		),
	);
}
~~~

Pada aturan di atas menetapkan bahwa semua user dapat mengakses aksi `index` dan `view` dan user yang terotentikasi pada action manapun saja,  termasuk kelas`admin`. User harus dilaran mengakses di scenario lain. Harap catat bahwa aturan-aturan ini dievaluasi dalam cara  evaluasi aturan order sekarang. Aturan pertama cocok dengan konteks sekarang dalam membuat keputusan akses. Misalnya, jika user sekarang adalah pemilik sistem yang ingin mengunjungi halaman pembuatan post, aturan kedua akan cocok dan memberikan akses kepada user.


Kustomisasi Operasi `create` dan `update` Operations
--------------------------------------------

Operasi `create` dan `update` sangat mirip. Mereka sama-sama membutuhkan sebuah tampilan sebuah form HTML untuk mengumpulkan input user, memvalidasi mereka, dan menyimpan mereka ke dalam database. Perbedaan utama antara operasi `update` akan mempopulasi bentuk form dengan data post yang sudah ada di dalam database. Untuk alasan ini, `yiic` mengenerate partial view `/wwwroot/blog/protected/views/post/_form.php` yang dapat diembed baik di view `create` dan `update` untuk merender form HTML.

Pertama-tama kita mengubah file `_form.php` sehingga form HTML hanya mengumpulkan input yang kita inginkan: `title`, `content`, `tags` dan `status`. Kita akan menggunakan field teks polos untuk mengumpulkan input-input untuk tiga atribut pertama, dan sebuah dropdown list untuk input `status`. Opsi dropdown list adalah tampilan teks untuk menampilkan status yang ada:

~~~
[php]
<?php echo $form->dropDownList($model,'status',Lookup::items('PostStatus')); ?>
~~~

Di atas, kita memanggil `Lookup::items('PostStatus')` untuk membawa daftar status post.

Kemudian kita memodifikasi kelas `Post` sehingga dapat secara otomatis mengatur beberapa atribut (seperti `create_time dan `author_id`) sebelum sebuah post dapat disimpan ke database. Kita meng-override metode `beforeSave()` sebagai berikut,

~~~
[php]
protected function beforeSave()
{
	if(parent::beforeSave())
	{
		if($this->isNewRecord)
		{
			$this->create_time=$this->update_time=time();
			$this->author_id=Yii::app()->user->id;
		}
		else
			$this->update_time=time();
		return true;
	}
	else
		return false;
}
~~~

Ketika kita menyimpan sebuah post, kita ingin mengupdate tabel `tbl_tag` yang merefleksikan perubahan frekuensi tag. Kita dapat melakukan tugas ini di metode `afterSave()`, yang akan secara otomatis dipanggil ketika Yii berhasil menyimpan post ke dalam database.

~~~
[php]
protected function afterSave()
{
	parent::afterSave();
	Tag::model()->updateFrequency($this->_oldTags, $this->tags);
}

private $_oldTags;

protected function afterFind()
{
	parent::afterFind();
	$this->_oldTags=$this->tags;
}
~~~

Dalam implementasi, karena kita ingin mendeteksi perubahan tag yang dilakukan user ketika dia mengupdate post yang ada, kita perlu mengetahui apa tag lamanya. Untuk alasan ini, kita juga menulis sebuah metode `afterFind()` untuk menyimpan tag lama dalam variabel `_oldTags`. Jika metode `afterFind()` jika memanggil secara otomatis oleh Yii ketika record AR dipopulasi dengan data dari database.

Kita tidak akan menjelaskan secara detail metode `Tag::updateFrequency()` di sini, bagi yang tertarik maka bisa merujuk ke file `/wwwroot/yii/demos/blog/protected/models/Tag.php`.


<div class="revision">$Id: post.create.txt 2120 2010-05-10 01:29:41Z qiang.xue $</div>