Kustomisasi Model Comment
=========================

Untuk model `Comment`, kita utamanya perlu mengubah method `rules()` dan `attributeLabels()`. Method `attributeLabels()` mengembalikan sebuah mapping antara nama atribut dan label atribut. Kita tidak perlu menyentuh `relations()` karena kode yang dihasilkan `yiic` sudah cukup baik.


Mengkustomisasi Method `rules()`
----------------------------

Pertama-tama kita mengubah rule (aturan) validasi yang dibuat oleh `yiic`. Berikut merupakan aturan untuk comments:

~~~
[php]
public function rules()
{
	return array(
		array('content, author, email', 'required'),
		array('author, email, url', 'length', 'max'=>128),
		array('email','email'),
		array('url','url'),
	);
}
~~~

Di contoh atas, kita menentukan bahwa atribut `author`, `email` dan `content` wajib diisi; panjang dari `author`, `email` dan `url` tidak boleh melewati 128; atribut `email` harus berupa alamat email yang valid ; dan atribut `url` harus berupa URL yang valid.


Kustomisasi Method `attributeLabels()`
--------------------------------------

Kemudian kita kustomisasi method `attributeLabels()` untuk mendeklarasi tampilan label untuk setiap atribut model. Method ini mengembalikan array yang terdiri dari pasangna nama-label. Ketika memanggil [CHtml::activeLabel()] akan menampilkan label dari atribut.

~~~
[php]
public function attributeLabels()
{
	return array(
		'id' => 'Id',
		'content' => 'Comment',
		'status' => 'Status',
		'create_time' => 'Create Time',
		'author' => 'Name',
		'email' => 'Email',
		'url' => 'Website',
		'post_id' => 'Post',
	);
}
~~~

> Tip|Tips: Jika label untuk atribut tidak dideklarasikan di dalam `attributeLabels()`, maka sebuah algoritma akan digunakan untuk menghasilkan label yang sesuai. Misalnya, sebuah label `Create Time` akan digenerate dari atribut `create_time` atau `createTIme`.


Kustomisasi Proses Penyimpanan
--------------------------

Di karenakan kita ingin menyimpan waktu pembuatan dari comment, kita meng-override method `beforeSave()` pada `Comment` seperti kita melakukannya pada model `Post`:

~~~
[php]
protected function beforeSave()
{
	if(parent::beforeSave())
	{
		if($this->isNewRecord)
			$this->create_time=time();
		return true;
	}
	else
		return false;
}
~~~


<div class="revision">$Id: comment.model.txt 1733 2010-01-21 16:54:29Z qiang.xue $</div>