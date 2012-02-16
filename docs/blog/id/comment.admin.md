Manajemen Comment
=================

Manajemen comment termasuk melakukan update, delete dan meng-approve suatu comment. Operasi-operasi ini diimplementasikan sebagai action di dalam kelas `CommentController`.


Mengupdate dan Menghapus Comment
------------------------------

Kode yang digenerate oleh `yiic` untuk mengupdate dan menghapus comment tidak mengalami perubahan.


Menyetujui Comment
------------------

Ketika comment baru saja dibuat, status mereka adalah pending approval, dan perlu di-approve supaya bisa dilihat oleh user guest. Menyetujui sebuah comment berfokus pada mengubah kolom status pada comment.

Kita membuat sebuah method `actionApprove()` di `CommentController` sebagai berikut,

~~~
[php]
public function actionApprove()
{
	if(Yii::app()->request->isPostRequest)
	{
		$comment=$this->loadModel();
		$comment->approve();
		$this->redirect(array('index'));
	}
	else
		throw new CHttpException(400,'Invalid request...');
}
~~~

Di atas, ketika action `approve` dipanggil melalui request POST, maka kita memanggil method `approve()` yang didefinisikan di model `Comment` untuk mengubah statusnya. Kemudian, kita akan me-redirect browser user ke halaman menampilkan post milik comment tersebut.

Kita juga memodifikasi method `actionIndex()` dari `Comment` untuk menampilkan seluruh comment. Kita ingin seluruh comment yang sedang pending muncul lebih awal.

~~~
[php]
public function actionIndex()
{
	$dataProvider=new CActiveDataProvider('Comment', array(
		'criteria'=>array(
			'with'=>'post',
			'order'=>'t.status, t.create_time DESC',
		),
	));

	$this->render('index',array(
		'dataProvider'=>$dataProvider,
	));
}
~~~

Perhatikan kode di atas, karena antara `tbl_post` dan `tbl_comment` memiliki kolom `status` dan `create_time`, kita perlu membedakan kedua kolom tersebut dengan memasang prefiks dengan nama alias tabel. Seperti yang dijelaskan dalam [guide](http://www.yiiframework.com/doc/guide/database.arr#disambiguating-column-names), alias untuk tabel utama di dalam relational query adalah `t`. Oleh karena itu, kita memberi awalan `t` untuk kolom `status` dan `create_time` pada code di atas.

Seperti view index post, view `index` untuk `CommentController` menggunakan [CListView] untuk menampilkan daftar comment yang pada berikutnya menggunakan partial view `/wwwroot/blog/protected/views/comment/_view.php` untuk menampilkan detail dari setiap comment individu. Kita tidak akan membahas secara detail di sini. Bagi yang tertarik dengan file ini bisa melihatnya di demo blog `/wwwroot/yii/demos/blog/protected/views/comment/_view.php`..

<div class="revision">$Id: comment.admin.txt 1810 2010-02-18 00:24:54Z qiang.xue $</div>