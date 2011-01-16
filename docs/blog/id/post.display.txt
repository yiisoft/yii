Menampilkan Post
================

Di dalam aplikasi blog kita, sebuah post dapat ditampilkan bersama post yang lain, bisa juga ditampilkan sendirian. Untuk menampilkan bersama-sama yang lain maka kita mengimplementasikan operasi `index`, sedangkan untuk menampilkan post tersebut sendiri kita gunakan operasi `view`. Dalam bagian ini kita akan mengkustomisasi kedua operasi ini untuk memenuhi requirement awal kita.


Kustomisasi Operasi `view`
----------------------------

Operasi `view` diimplementasikan oleh metode `actionView()` di dalam `PostController`. Operasi ini menampilkan view dari `view` dengan file `/wwwroot/blog/protected/views/post/view.php`.

Di bawah ini merupakan kode bersangkutan yang mengimplementasikan operasi `view` di dalam `PostController`:

~~~
[php]
public function actionView()
{
	$post=$this->loadModel();
	$this->render('view',array(
		'model'=>$post,
	));
}

private $_model;

public function loadModel()
{
	if($this->_model===null)
	{
		if(isset($_GET['id']))
		{
			if(Yii::app()->user->isGuest)
				$condition='status='.Post::STATUS_PUBLISHED
					.' OR status='.Post::STATUS_ARCHIVED;
			else
				$condition='';
			$this->_model=Post::model()->findByPk($_GET['id'], $condition);
		}
		if($this->_model===null)
			throw new CHttpException(404,'The requested page does not exist.');
	}
	return $this->_model;
}
~~~

Perubahan utama kita terletak pada metode `loadModel()`. Dalam metode ini, kita meng-query tabel `Post` berdasarkan parameter GET `id`. Jika post tidak ditemukan atau tidak dipublikasikan atau diarsip (ketika user adalah guest), kita melempar sebuah error HTTP 404. Di luar masalah tadi, maka sebuah objek post akan dikembalikan ke `actionView()` yang kemudian objeknya akan di-pass ke script view untuk ditampilkan

> Tip|Tips: Yii menangkap exception HTTP (instance dari [CHttpException]) dan menampilkannya baik di template yang sudah ditetapkan ataupun di view error buatan sendiri. Kerangka aplikasi yang dihasilkan `yiic` sudah mengandung sebuah view error buatan sendiri di `/wwwroot/blog/protected/views/site/error.php`. Kita dapat memodifikasikan fil ini jika kita ingin kustomisasi lebih lanjut untuk tampilan error.

Perubahan script `view` pada umumnya terletak bagaimana mengatur format dan style dari tampilan post. Kita tidak akan membahas detail ini. Jika anda tertarik maka dapat merujuk ke `/wwwroot/blog/protected/views/post/view.php`.


Kustomisasi Operasi `index`
----------------------------

Mirip seperti operasi `view`, kita terbiasa dengan menggunakan operasi `index` pada dua tempat: metode `actionIndex()` yang ada di dalam `PostController` dan file view `/wwwroot/blog/protected/views/post/index.php`. Kita pada umumnya butuh bantuan untuk menampilkan daftar post yang berasosiasi dengna tag yang ditentukan.

Di bawah merupakan metode `actionIndex()` yang dimodifikasi di dalam `PostController`:

~~~
[php]
public function actionIndex()
{
	$criteria=new CDbCriteria(array(
		'condition'=>'status='.Post::STATUS_PUBLISHED,
		'order'=>'update_time DESC',
		'with'=>'commentCount',
	));
	if(isset($_GET['tag']))
		$criteria->addSearchCondition('tags',$_GET['tag']);

	$dataProvider=new CActiveDataProvider('Post', array(
		'pagination'=>array(
			'pageSize'=>5,
		),
		'criteria'=>$criteria,
	));

	$this->render('index',array(
		'dataProvider'=>$dataProvider,
	));
}
~~~

Pada kode di atas, pertama kita membuat sebuah kriteria query untuk mengambil daftar post. Kriterianya menjelaskan bahwa hanya mengembalikan post yang dipublikasi dan harus dalam keadaan tersusun berdasarkan waktu update dalam bentuk descending (besar ke kecil), kita ingin menampilkan berapa komentar telah diterima post. Dalam criteria, kita juga secara spesifik membawa kembali `commentCourt`, yang jika anda ingat, sebuah deklarasi relasi di `Post::relations()`.

Apabila user ingin melihat post dengan tag tertentu, kita akan menambahkan sebuah kondisi search ke criteria untuk dilihat dan dipelajari

Menggunakan kriteria query, kita dapat membuat sebuah data provider, yang pada umumnya melayani tiga hal. Pertama, memberikan pagination untuk data yang terlalu banyak hasilnya. Di sini kita kustomisasi pagination dengan mengatur ukuran page menjadi 5. Detik, seperti mengurutkan sesuai request user. Dan terkahir, dia melakukan feed pada data yang terpaginasi dan susun ke widget atau kode view untuk presentasi

Setelah kita menyelesaikan dengan `actionIndex()`, kita memodifikasikan view `index` sebagai berikut. Perubahan kita pada umumnya mengenai penambahan header `h1`  ketika user menentukan tampilan post sebagai tag.

~~~
[php]
<?php if(!empty($_GET['tag'])): ?>
<h1>Posts Tagged with <i><?php echo CHtml::encode($_GET['tag']); ?></i></h1>
<?php endif; ?>

<?php $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'_view',
	'template'=>"{items}\n{pager}",
)); ?>
~~~

Harap diingat bahwa contoh di atas kita menggunakan [CListView] untuk menampilkan daftar post. Widget ini memerlukan sebuah partial view untuk menampilkan detail dari setiap post individu. Di sini kita menspesifikasi partial view adalah `_view`, yang artinya file `/wwwroot/blog/protected/views/post/_view_post.php). Dalam view script ini, kita dapat mengakses instance post yang sedang ditampilkan di local varaible bernama`$data`.

<div class="revision">$Id: post.display.txt 2121 2010-05-10 01:31:30Z qiang.xue $</div>