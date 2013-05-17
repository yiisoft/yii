Membuat dan Menampilkan Comment
================================

Pada bagian ini, kita mengimplementasikan fitur penampilan dan pembuatan comment.

Guna meningkatkan interaksi pengguna, kita akan menampilkan error yang mungkin terjadi kepada user setiap kali mereka selesai memasukkan satu field. Ini dikenal dengan validasi input client-side. Kita akan melihat bagaimana ini dapat dilakukan dengan sangat gampang. Harap diperhatikan bahwa ini memerlukan versi Yii 1.1.1 atau lebih tinggi.


Menampilkan Comment
-------------------

Alih-alih menampilkan dan membuat comment pada halaman tersendiri, kita akan menggunakan halaman detail post (yang digenerate oleh action `view` dari `PostController`). Di bawah tampilan isi post, kita akan meletakkan daftar comment milik post bersangkutan dan sebuah form pembuatan comment.

Guna menampilkan comment di halaman detail post, kita memodifikasi script view `/wwwroot/blog/protected/views/post/view.php` sebagai berikut.

~~~
[php]
..view post di sini..

<div id="comments">
	<?php if($model->commentCount>=1): ?>
		<h3>
			<?php echo $model->commentCount . 'comment(s)'; ?>
		</h3>

		<?php $this->renderPartial('_comments',array(
			'post'=>$model,
			'comments'=>$model->comments,
		)); ?>
	<?php endif; ?>
</div>
~~~

Di atas, kita memanggil `renderPartial()` untuk merender sebuah view partial bernama `_comments` untuk menampilkan daftar comment milik post sekarang. Perhatikan di dalam view kita menggunakan ekspresi `$model->comments` untuk mengambil comment untuk post. Ekspresi ini valid karena kita telah mendeklarasi relasi `comments` di kelas `Post`. Dengan mengevaluasi ekspresi ini akan menjalankan query database JOIN secara implisit untuk menarik comment-comment yang benar. Fitur ini dikenal sebagai [lazy relational query](http://www.yiiframework.com/doc/guide/database.arr).

View partial `_comments` tidak terlalu menarik. Tujuan utamanya adalah melewati setiap comment dan menampilkan detail-detailnya. Pembaca yang tertarik dengan topik ini bisa merujuk ke `/wwwroot/yii/demos/blog/protected/views/post/_comments.php`.


Membuat Comment
-----------------

Untuk menangani pembuatan comment, pertama-tama kita harus memodifikasi method `actionView()` dari kelas `PostController` sebagai berikut,

~~~
[php]
public function actionView()
{
	$post=$this->loadModel();
	$comment=$this->newComment($post);

	$this->render('view',array(
		'model'=>$post,
		'comment'=>$comment,
	));
}

protected function newComment($post)
{
	$comment=new Comment;
	if(isset($_POST['Comment']))
	{
		$comment->attributes=$_POST['Comment'];
		if($post->addComment($comment))
		{
			if($comment->status==Comment::STATUS_PENDING)
				Yii::app()->user->setFlash('commentSubmitted','Thank you...');
			$this->refresh();
		}
	}
	return $comment;
}
~~~

Di atas, kita memanggil method `newComment()` sebelum kita merender `view`. Di dalam method `newComment()` kita mengenerate instance `Comment` dan mengecek apakah form comment telah di-submit. Jika iya, kita mencoba menambahkan comment untuk post dengan memanggil `$post->addComment($comment)`. Jika dia lewat, maka kita akan me-refresh halaman detail post. Andaikata comment harus di-approve terlebih dahulu, kita akan menampilkan flash message untuk mengindikasikannya. Sebuah flash message umumnya berupa pesan konfirmasi yang ditampilkan ke user. Jika user mengklik tombol refresh di browsernya, maka pesannya akan hilang.

Kita jga perlu memodifikasi `/wwwroot/blog/protected/views/post/view.php` lebih lanjut.

~~~
[php]
......
<div id="comments">
	......
	<h3>Leave a Comment</h3>

	<?php if(Yii::app()->user->hasFlash('commentSubmitted')): ?>
		<div class="flash-success">
			<?php echo Yii::app()->user->getFlash('commentSubmitted'); ?>
		</div>
	<?php else: ?>
		<?php $this->renderPartial('/comment/_form',array(
			'model'=>$comment,
		)); ?>
	<?php endif; ?>

</div><!-- comments -->
~~~

Di kode di atas, kita menampilkan flash message jika ada. Jika tidak, kita akan menampilkan form input dengan merender view partial `/wwwroot/blog/protected/views/comment/_form.php`.


Validasi Sisi Client
----------------------

Supaya mampu melakukan validasi di sisi client, kita harus melakukan beberapa perubahan kecil untuk view form comment `/wwwroot/blog/protected/views/comment/_form.php` dan method `newComment()`.

Di file `_form.php`, kita akan perlu mengatur [CActiveForm::enableAjaxValidation] menjadi true ketika kita membuat widget [CActiveForm]:

~~~
[php]
<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'comment-form',
	'enableAjaxValidation'=>true,
)); ?>
......
<?php $this->endWidget(); ?>

</div><!-- form -->
~~~

Dan di method `newComment()`, kita akan memasukkan sebuah kode untuk merespon request validasi AJAX. Kode mengecek apakah terdapat variabel `POST` yang dinamakan `ajax`. Jika ada, maka akan menampilkan hasil validasi dengan memanggil [CActiveForm::validate].

~~~
[php]
protected function newComment($post)
{
	$comment=new Comment;

	if(isset($_POST['ajax']) && $_POST['ajax']==='comment-form')
	{
		echo CActiveForm::validate($comment);
		Yii::app()->end();
	}

	if(isset($_POST['Comment']))
	{
		$comment->attributes=$_POST['Comment'];
		if($post->addComment($comment))
		{
			if($comment->status==Comment::STATUS_PENDING)
				Yii::app()->user->setFlash('commentSubmitted','Thank you...');
			$this->refresh();
		}
	}
	return $comment;
}
~~~

<div class="revision">$Id: comment.create.txt 2772 2010-12-24 16:24:12Z alexander.makarow $</div>