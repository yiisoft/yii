Menggunakan Sintaks Template Alternatif
=======================================

Yii mengijinkan pengembang untuk menggunakan sintaks template favorit sendiri (misalnya
Prado, Smarty) untuk menulis controller atau tampilan widget. Ini dilakukan dengan
menulis dan menginstalasi komponen aplikasi [viewRenderer|CWebApplication::viewRenderer].
Pembuat tampilan mengintersepsi penyertaan
[CBaseController::renderFile], sesuai dengan file tampilan dengan sintaks
template yang dikustomisasi, dan menampilkan hasil kompilasi.

> Info: Direkomendasikan untuk menggunakan sintaks template dikustomisasi hanya saat
menulis tampilan yang jarang dipakai ulang. Jika tidak, orang yang menggunakan
kembali tampilan akan dipaksa untuk memakai sintaks template dikustomisasi
yang sama dalam aplikasinya.

Berikutnya kami perkenalkan bagaimana menggunakan [CPradoViewRenderer], pembuat
tampilan yang mengijinkan para pengembang untuk memakai sintaks  template mirip dengan
[Prado framework](http://www.pradosoft.com/). Bagi orang yang ingin mengembangkan
pembuat tampilan sendiri, [CPradoViewRenderer] adalah referensi yang baik.

Menggunakan `CPradoViewRenderer`
--------------------------

Untuk menggunakan [CPradoViewRenderer], kita cukup mengkonfigurasi aplikasi
seperti berikut:

~~~
[php]
return array(
	'components'=>array(
		......,
		'viewRenderer'=>array(
			'class'=>'CPradoViewRenderer',
		),
	),
);
~~~

Secara default, [CPradoViewRenderer] akan mengkompilasi file sumber tampilan dan
menyimpan file hasil PHP di bawah direktori
[runtime](/doc/guide/basics.convention#directory). Hanya saat file
sumber tampilan diubah, file PHP akan dibuat ulang.
Oleh karenanya, menggunakan [CPradoViewRenderer] hanya menyebabkan sedikit degradasi
kinerja.

> Tip: Karena [CPradoViewRenderer] memperkenalkan beberapa tag template baru
agar penulisan tampilan lebih mudah dan lebih cepat, Anda masih bisa menulis kode PHP seperti
biasa dalam sumber tampilan.

Berikutnya kami perkenalkan tag template yang didukung oleh
[CPradoViewRenderer].

### Tag PHP Pendek (Short PHP Tag)

Tag PHP pendek adalah jalan pintas untuk menulis ekspresi dan pernyataan PHP dalam sebuah
tampilan. Tag ekspresi `<%= expression %>` diterjemahkan ke dalam
`<?php echo expression ?>`; sementara tag pernyataan `<% statement
%>` menjadi `<?php statement ?>`. Sebagai contoh,

~~~
[php]
<%= CHtml::textField($name,'value'); %>
<% foreach($models as $model): %>
~~~

diubah menjadi

~~~
[php]
<?php echo CHtml::textField($name,'value'); ?>
<?php foreach($models as $model): ?>
~~~

### Tag Komponen

Tag komponen dipakai untuk menyisipkan
[widget](/doc/guide/basics.view#widget) dalam tampilan. Ia menggunakan sintaks
berikut:

~~~
[php]
<com:WidgetClass property1=value1 property2=value2 ...>
	// konten untuk widget
</com:WidgetClass>

// widget tanpa konten
<com:WidgetClass property1=value1 property2=value2 .../>
~~~

di mana `WidgetClass` menetapkan nama kelas widget atau kelas [alias
path](/doc/guide/basics.namespace), dan nilai awal properti bisa berupa
string bertanda kutip atau ekspresi PHP berkurung di dalam pasangan kurung kurawal.
Sebagai contoh,

~~~
[php]
<com:CCaptcha captchaAction="captcha" showRefreshButton={false} />
~~~

akan diubah menjadi

~~~
[php]
<?php $this->widget('CCaptcha', array(
	'captchaAction'=>'captcha',
	'showRefreshButton'=>false)); ?>
~~~

> Note|Catatan: Nilai `showRefreshButton` ditetapkan sebagai `{false}`
alih-alih `"false"` karena "false" berarti string daripada
boolean.

### Tag Cache

Tag cache adalah jalan pintas menggunakan [cache
fragmen](/doc/guide/caching.fragment). Sintaksnya seperti berikut,

~~~
[php]
<cache:fragmentID property1=value1 property2=value2 ...>
	// content being cached
</cache:fragmentID >
~~~

di mana `fragmentID` harus berupa pembeda yang secara unik mengidentifikasi
konten yang sedang di-cache, dan pasangan properti-nilai dipakai untuk mengkonfigurasi
cache fragmen. Sebagai contoh,

~~~
[php]
<cache:profile duration={3600}>
	// informasi profil pengguna di sini
</cache:profile >
~~~

akan diubah menjadi

~~~
[php]
<?php if($this->beginCache('profile', array('duration'=>3600))): ?>
	// informasi profil pengguna di sini
<?php $this->endCache(); endif; ?>
~~~

### Tag clip

Seperti tag cache, tag clip adalah jalan pintas untuk memanggil
[CBaseController::beginClip] dan [CBaseController::endClip] dalam tampilan. Sintaksnya
adalah sebagai berikut,

~~~
[php]
<clip:clipID>
	// konten untuk klip ini
</clip:clipID >
~~~

di mana `clipID` adalah pembeda yang secara unik mengidentifikasi konten klik.
Tag clip akan diubah menjadi

~~~
[php]
<?php $this->beginClip('clipID'); ?>
	// konten untuk klip ini
<?php $this->endClip(); ?>
~~~

### Tag Komentar

Tag komentar dipakai untuk menulis komentar tampilan yang hanya terlihat bagi
pengembang. Tag komentar akan dihilangkan saat tampilan diperlihatkan ke
pengguna akhir. Sintaks untuk tag komentar adalah sebagai berkut,

~~~
[php]
<!---
komentar tampilan yang akan dibuang
--->
~~~

Menggabungkan Format Template
-----------------------

Mulai dari versi 1.1.2, dimungkinkan untuk menggabungkan penggunaan beberapa
sintaks alternatif template dengan sintaks PHP normal. Untuk melakukannya, properti
[CViewRenderer::fileExtension] yang terinstalasi pada pe-render view harus dikonfigurasi
dengan sebuah nilai selain `.php`. Misalnya, jika properti di-set sebagai `.tpl`, maka setiap
view yang berakhiran `.tpl` akan di-render dengan menggunakan pe-render view yang terinstalasi,
sedangkan file view lain yang berextension `.php` akan dianggap sebagai skrip view PHP biasa.


<div class="revision">$Id: topics.prado.txt 3226 2011-05-18 10:37:47Z mdomba $</div>