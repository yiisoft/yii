Mengumpulkan Input Tabular
==========================

Adakalanya kita ingin mengumpulkan input pengguna dalam mode batch. Yakni, pengguna
dapat memasukan informasi untuk instance model secara multipel dan mengirimkannya
sekaligus. Ini kita menyebutnya sebagai *input tabular* karena field input sering ditampilkan
dalam tabel HTML.

Untuk bekerja dengan input tabular, pertama kita perlu membuat atau mempopulasikan
array instance model, tergantung pada apakah kita menyisipkan atau mengupdate
data. Selanjutnya kita dapat mengambil data input pengguna dari variabel `$_POST` dan
menempatkannya ke setiap model. Perbedaan utama dari model input tunggal adalah
bahwa kita mengambil data input menggunakan `$_POST['ModelClass'][$i]` alih-alih
`$_POST['ModelClass']`.

~~~
[php]
public function actionBatchUpdate()
{
	// ambil item yang akan dipopulasi dalam mode batch
	// menganggap setiap item adalah kelas model 'Item'
	$items=$this->getItemsToUpdate();
	if(isset($_POST['Item']))
	{
		$valid=true;
		foreach($items as $i=>$item)
		{
			if(isset($_POST['Item'][$i]))
				$item->attributes=$_POST['Item'][$i];
			$valid=$item->validate() && $valid;
		}
		if($valid)  // seluruh item valid
			// ...lakukan sesuatu di sini
	}
	// tampilkan tampilan untuk mengumpulkan input tabular
	$this->render('batchUpdate',array('items'=>$items));
}
~~~

Setelah action siap, kita perlu bekerja pada tampilan `batchUpdate` untuk
menampilkan field input dalam sebuah tabel HTML.

~~~
[php]
<div class="form">
<?php echo CHtml::beginForm(); ?>
<table>
<tr><th>Name</th><th>Price</th><th>Count</th><th>Description</th></tr>
<?php foreach($items as $i=>$item): ?>
<tr>
<td><?php echo CHtml::activeTextField($item,"[$i]name"); ?></td>
<td><?php echo CHtml::activeTextField($item,"[$i]price"); ?></td>
<td><?php echo CHtml::activeTextField($item,"[$i]count"); ?></td>
<td><?php echo CHtml::activeTextArea($item,"[$i]description"); ?></td>
</tr>
<?php endforeach; ?>
</table>

<?php echo CHtml::submitButton('Save'); ?>
<?php echo CHtml::endForm(); ?>
</div><!-- form -->
~~~

Catatan dalam contoh di atas kita menggunakan `"[$i]name"` alih-alih `"name"` sebagai
parameter kedua saat memanggil [CHtml::activeTextField].

Jika ada kesalahan validasi, field input terkait akan ditandai (highlight) secara
otomatis, seperti halnya model input tunggal yang kami jelaskan
sebelumnya.

<div class="revision">$Id: form.table.txt 2783 2010-12-28 16:20:41Z qiang.xue $</div>