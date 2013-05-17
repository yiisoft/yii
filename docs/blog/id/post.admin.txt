Mengatur Post
==============
Mengatur post utamanya mengacu pada kegiatan menampilkan post yang terdapat dalam view administratif, yang memungkinkan kita melihat seluruh status post, mengubahnya dan menghapusnya. Semua kegiatan ini dapat diselesaikan dengan operasi `admin` dan operasi `delete`. Kode yang di-generate `yiic` tidak memerlukan modifikasi yang terlalu banyak. Di bawah ini kita akan melihat bagaimana dua operasi tersebut dilakukan.

Menampilkan Post dalam tampilan tabel
-----------------------------

Operasi `admin` menampilkan post-post dengan seluruh status dalam tampilan tabular. View tersebut mampu melakukan sorting (penyusunan) dan paginasi (pemberian nomor halaman). Berikut merupakan metode `PostController` bernama `actionAdmin()`;

~~~
[php]
public function actionAdmin()
{
	$model=new Post('search');
	if(isset($_GET['Post']))
		$model->attributes=$_GET['Post'];
	$this->render('admin',array(
		'model'=>$model,
	));
}
~~~

Kode di atas dihasilkan oleh tool `yiic` tanpa modifikasi apapun. Pertama-tama tool ini membuatkan sebuah model `Post` dengan [scenario](/doc/guide/form.model) `search`. KIta akan menggunakan model ini untuk mengumpulkan kondisi pencarian yang user tentukan. Kita maka kemudian assign data model dan yang disuplai user, jika ada. Terakhir kita merender view `admim` dengna model.

Berikut merupakan kode di view `admin`:

~~~
[php]
<?php
$this->breadcrumbs=array(
	'Manage Posts',
);
?>
<h1>Manage Posts</h1>

<?php $this->widget('zii.widgets.grid.CGridView', array(
	'dataProvider'=>$model->search(),
	'filter'=>$model,
	'columns'=>array(
		array(
			'name'=>'title',
			'type'=>'raw',
			'value'=>'CHtml::link(CHtml::encode($data->title), $data->url)'
		),
		array(
			'name'=>'status',
			'value'=>'Lookup::item("PostStatus",$data->status)',
			'filter'=>Lookup::items('PostStatus'),
		),
		array(
			'name'=>'create_time',
			'type'=>'datetime',
			'filter'=>false,
		),
		array(
			'class'=>'CButtonColumn',
		),
	),
)); ?>
~~~

Kita menggunakan [CGridView] untuk menampilkan post. CGridView memungkinkan kita untuk menyusun kolom dan melakukan paginasi sepanjang post jika terlalu banyak untuk ditampilkan dalam satu halaman. Perubahan yang akan kita lakukan utamanya pada bagaimana menampilkan setiap kolom. Misalnya, untuk kolom `title`, kita ingin dia ditampilkan sebagai sebuah hyperlink yang akan berpindah ke tampilan detail dari post. Ekpresi `$data->url` mengembalikan nilai dari properti `url` yang kita tentukan di kelas `Post`.

> Tip|Tips: Ketika menampilkan teks, kita memanggil [CHtml::encode()] untuk meng-encode entiti HTML di dalamnya. Tujuannya untuk mencegah terjadinya [serangan cross-site scripting](http://www.yiiframework.com/doc/guide/topics.security).


Menghapus Post
--------------

Dalam data grid `admin`, terdapat sebuah tombol delete di tiap baris. Dengan mengklik tombol ini akan menghapus post bersangkutan. Secara internal, mengklik tombol delete akan memicu untuk menjalankan action `delete` yang diimplementasi dengan :

~~~
[php]
public function actionDelete()
{
	if(Yii::app()->request->isPostRequest)
	{
		// we only allow deletion via POST request
		$this->loadModel()->delete();

		if(!isset($_POST['ajax']))
			$this->redirect(array('index'));
	}
	else
		throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
}
~~~

Kode di atas merupakan salah satu hasil generate dari tool `yiic` tanpa perubahan. Kita ingin menjelaskan sedikit tentang pengecekan pada `$_POST['ajax']`. Widget [CGridView] memiliki sebuah fitur yang sangat bagus sekali untuk sorting (pengurutan), pagination (sistem halaman) dan operasi delete yang semuanya dilakukan dalam mode AJAX secara default. Itu artinya, seluruh halaman tidak akan di-reload apalagi melakukan operasi yang disebutkan tadi. Namun, mungkin widget berjalan dengan mode non-AJAX (dengan mengatur properti `ajaxUpdate` menjadi false atau menonaktifkan JavaScript di sisi klien). Sudah merupakan kewajiban untuk membedakan action `delete` dalam dua skenario: jika request delete dibuat melalui AJAX, kita tidak akan me-redirect browser, kalau tidak maka kita harus me-redirect.

Menghapus sebuah post juga harus menyebabkan terhapusnya semua comment yang berhubungan dengan post. Sebagai tambahan, kita juga harus mengupdate tabel `tbl_tag` mengenai post yang dihapus. Masing-masing tugas ini dapat dilakukan dengan menulis method `afterDelet` di dalam kelas model `Post` sebagai berikut,

~~~
[php]
protected function afterDelete()
{
	parent::afterDelete();
	Comment::model()->deleteAll('post_id='.$this->id);
	Tag::model()->updateFrequency($this->tags, '');
}
~~~

Kode di atas sangat jelas: pertama kita menghapus semua komentar yang `post_id`-nya sama dengan ID pada post yang di-delete: kemudian mengupdate `tbl_tag` pada `tags` dari post yang dihapus.

> Tip|Tips: Kita harus secara eksplisit menghapus seluruh komentar untuk post yang di-delete dikarenakan SQLite tidak memiliki dukungan terhadap constaint foreign key. Di DBMS yang mendukung constraint ini (seperti MySQL, PostgreSQL), constraint foreign key dapat diatur sehingga DBMS akan secara otomatis menghapus comments yang berhubungan dengan post yang didelete. Jika memang demikian, maka kita tidak perlu lagi memanggil delete secara eksplisit dalam kode kita.

<div class="revision">$Id: post.admin.txt 2425 2010-09-05 01:30:14Z qiang.xue $</div>