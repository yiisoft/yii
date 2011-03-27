Membuat Form
================

Menulis tampilan `login` adalah pekerjaan langsung. Kita mulai dengan sebuah tag `form`
yang atribut aksinya berupa URL atas aksi `login` seperti dijelaskan
sebelumnya. Kemudian kita menyisipkan label dan field input untuk atribut
yang dideklarasikan dalam kelas `LoginForm`. Setelah itu kita menyisipkan tombol kirim
yang dapat diklik oleh pengguna untuk mengirimkan form. Semua ini dapat dikerjakan dalam
kode murni HTML.

Yii menyediakan beberapa kelas pembantu guna memfasilitasi komposisi tampilan. Sebagai
contoh, untuk membuat sebuah field input teks, kita dapat memanggil [CHtml::textField()]; untuk
membuat daftar drop-down, panggil [CHtml::dropDownList()].

> Info: Orang mungkin heran apa untungnya menggunakan helper jika mereka
> memerlukan sejumlah kode yang mirip dibandingkan dengan kode HTML langsung.
> Jawabannya adalah bahwa helper dapat menyediakan lebih dari sekedar kode HTML. Sebagai
> contoh, kode berikut akan menghasilkan field input teks yang
> memicu pengiriman form jika nilainya diubah oleh pengguna.
> ~~~
> [php]
> CHtml::textField($name,$value,array('submit'=>''));
> ~~~
> Ini akan memerlukan penulisan sejumlah JavaScript dimana-mana.

Dalam contoh berikut, kita menggunakan [CHtml] untuk membuat form login. Kita beranggapan bahwa
variabel `$user` mewakili turunan `LoginForm`.

~~~
[php]
<div class="form">
<?php echo CHtml::beginForm(); ?>

	<?php echo CHtml::errorSummary($model); ?>

	<div class="row">
		<?php echo CHtml::activeLabel($model,'username'); ?>
		<?php echo CHtml::activeTextField($model,'username') ?>
	</div>

	<div class="row">
		<?php echo CHtml::activeLabel($model,'password'); ?>
		<?php echo CHtml::activePasswordField($model,'password') ?>
	</div>

	<div class="row rememberMe">
		<?php echo CHtml::activeCheckBox($model,'rememberMe'); ?>
		<?php echo CHtml::activeLabel($model,'rememberMe'); ?>
	</div>

	<div class="row submit">
		<?php echo CHtml::submitButton('Login'); ?>
	</div>

<?php echo CHtml::endForm(); ?>
</div><!-- form -->
~~~

Kode di atas menghasilkan form lebih dinamis. Sebagai contoh,
[CHtml::activeLabel()] menghasilkan label terkait dengan atribut model yang
ditetapkan. Jika atribut memiliki kesalahan input, label kelas CSS akan diubah
ke `error`, yang mengubah tampilan label dengan gaya CSS terkait.
Hal yang sama, [CHtml::activeTextField()] menghasilkan field input teks
untuk atribut model yang ditetapkan dan mengubah kelas CSS jika
ada kesalahan pada input.

Jika kita menggunakan file style CSS `form.css` yang disediakan oleh skrip `yiic`, form
yang dihasilkan akan terlihat seperti berikut:

![Halaman login](login1.png)

![Login dengan halaman kesalahan](login2.png)

Mulai dari versi 1.1.1, disediakan sebuah widget baru yang dinamakan [CActiveForm]
untuk memfasilitasi pembuatan form. Widget ini mampu mendukung secara langsung
dan konsisten untuk validasi di bagian server maupun klien. Dengan menggunakan [CActiveForm],
kode di atas dapat ditulis ulang seperti :

~~~
[php]
<div class="form">
<?php $form=$this->beginWidget('CActiveForm'); ?>

	<?php echo $form->errorSummary($model); ?>

	<div class="row">
		<?php echo $form->label($model,'username'); ?>
		<?php echo $form->textField($model,'username') ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'password'); ?>
		<?php echo $form->passwordField($model,'password') ?>
	</div>

	<div class="row rememberMe">
		<?php echo $form->checkBox($model,'rememberMe'); ?>
		<?php echo $form->label($model,'rememberMe'); ?>
	</div>

	<div class="row submit">
		<?php echo CHtml::submitButton('Login'); ?>
	</div>

<?php $this->endWidget(); ?>
</div><!-- form -->
~~~

<div class="revision">$Id: form.view.txt 1751 2010-01-25 17:21:31Z qiang.xue $</div>