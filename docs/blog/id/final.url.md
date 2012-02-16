Mempercantik URL
================

URL yang menghubungkan berbagai halaman untuk aplikasi blog kita masih terlihat kurang  bagus. Misalnya, untuk URL halaman yang menampilkan sebuah post terlihat sebagai berikut:

~~~
/index.php?r=post/show&id=1&title=A+Test+Post
~~~

Pada bagian ini, kita akan melihat bagaimana memperindah URL-URL ini dan membuatnya lebih ramah mesin pencari (search engine optimazation friendly). Tujuan kita adalah menggunakan URL berikut ini dalam aplikasi:

 1. `/index.php/posts/yii`: akan mengarah ke halaman dengan post yang memiliki tag `yii`;
 2. `/index.php/post/2/A+Test+Post`: mengarah ke halaman yang menampilkan detail dari post ID 2 dengan judul `A Test Post`;
 3. `/index.php/post/update?id=1`: mengarahkan ke halaman yang memungkinkan update post ber-ID 1

Perhatikan bahwa form URL kedua, kita akan menyertakan judul post ke dalam URL. Tujuan utamanya adalah membuat URL menjadi ramah SEO. Dikatakan bahwa search engine juga menghormati tulisan yang ditemukan di URL ketika di-indeks.

Untuk mencapai tujuan, kita memodifikasikan [konfigurasi aplikasi](http://www.yiiframework.com/doc/guide/basics.application#application-configuration) sebagai berikut,

~~~
[php]
return array(
	......
	'components'=>array(
		......
		'urlManager'=>array(
			'urlFormat'=>'path',
			'rules'=>array(
        		'post/<id:\d+>/<title:.*?>'=>'post/view',
        		'posts/<tag:.*?>'=>'post/index',
        		'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
			),
		),
	),
);
~~~

Di atas, kita mengkonfigurasi komponen [urlManager](http://www.yiiframework.com/doc/guide/topics.url) dengan mengatur properti `urlFormat` menjadi `path` dan menambahkan sekumpulan `rules`.

Rules (aturan) digunakan oleh `urlManager` untuk parsing dan membuat URL menjadi format yang diinginkan. Misalnya, rule kedua mengatakan bahwa jika sebuah URL `/index.php/posts/yii` diminta, maka komponen `urlManager` harus bertanggung jawab untuk mengarahkan request ke [route](http://www.yiiframework.com/doc/guide/basics.controller#route) `post/index` dan menghasilkan sebuah parameter GET `tag` dengan nilai `yii`. Di lain sisi, ketika membuat URL dengan rute `post/index` dan parameter `tag`, maka komponen `urlManager` juga akan menggunakan aturan ini untuk menghasilkan URL yang diinginkan. Untuk alasan ini, kita mengatakan bahwa `urlManager` merupakan manajer URL dua arah.

Komponen `urlManager` dapat mempercantik URL lebih lanjut, seperti menyembunyikan `index.php` di dalam URL, menambah akhiran seperti `.html` ke URL. Kita juga dapat mengambil fitur ini dengan mudah dengan mengkonfigurasi berbagai property dari `urlManager` di dalam konfigurasi aplikasi. Untuk lebih detail, silahkan merujuk ke [Guide](http://www.yiiframework.com/doc/guide/topics.url).


<div class="revision">$Id: final.url.txt 2240 2010-07-03 18:06:11Z alexander.makarow $</div>