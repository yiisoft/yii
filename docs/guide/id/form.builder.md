Menggunakan Form Builder
========================

Ketika kita membuat form HTML, kita sering harus menulis kode view yang berulang-ulang. Hal seperti
ini biasanya cukup sulit untuk dipakai ulang pada projek lain. Contohnya, untuk setiap input field,
kita harus menghubungkannya pada sebuah label dan menampilkan pesan error validasi yang mungkin ada.
Untuk meningkatkan kemungkinan pemakaian ulang pada kode-kode ini, kita dapat menggunakan fitur form builder.


Konsep Dasar
------------

Form builder di Yii menggunakan objek [CForm] untuk mewakili spesifikasi yang diperlukan untuk
membentuk sebuah form HTML, termasuk data model yang berhubungan dengan form, input field seperti
apa yang diperlukan di dalam form, dan bagaimana me-render form secara keseluruhan.
Developer umumnya perlu membuat dan mengatur objek [CForm] ini, dan memanggil fungsi render untuk
menampilkan form.

Spesifikasi input pada form diatur pada hirarki elemen form. Pada bagian root dari hirarki tersebut,
merupakan objek [CForm]. Objek form root ini mengatur turunannya dalam dua kumpulan: [CForm::buttons] dan [CForm::elements].
Yang pertama mengandung elemen-elemen tombol(seperti tombol submit, tombol reset), sedangkan yang kedua
berisi elemen-elemen input, teks statis, dan sub-form. Sebuah sub-form adalah objek [CForm] yang mengandung
kumpulan [CForm::elements] dari form lain. Sub-form bisa memiliki data model, [CForm::buttons] dan [CForm::elements] tersendiri.

Ketika pengguna men-submit sebuah form, maka data yang dimasukkan ke dalam input field dari keseluruhan hirarki form akan dikirim,
termasuk input field milik sub-form. [CForm] menyediakan metode mudah yang secara otomatis dapat memberikan nilai input data ke atribut model bersangkutan
dan melakukan validasi pada data.


Menciptakan Sebuah Form Sederhana
---------------------------------

Pada contoh berikut, kita akan melihat bagaimana membuat sebuah form login dengan form builder.

Pertama, kita menulis sebuah kode action login:

~~~
[php]
public function actionLogin()
{
	$model = new LoginForm;
	$form = new CForm('application.views.site.loginForm', $model);
	if($form->submitted('login') && $form->validate())
		$this->redirect(array('site/index'));
	else
		$this->render('login', array('form'=>$form));
}
~~~

Pada kode di atas, kita membuat sebuah objek [CForm] yang merujuk ke jalur alias `application.views.site.loginForm`
(akan dijelaskan beberapa saat lagi).
Objek [CForm] ini berkaitan dengan model `LoginForm` seperti yang dijelaskan pada [Pembuatan Model](/doc/guide/form.model).

Begitu kode membaca, jika form sudah di-submit dan semua input tidak ada error ketika divalidasi, maka
kita dapat me-redirect browser pengguna ke halaman `site/index`. Kalau tidak, maka kita menampilkan
sebuah view `login` dengan form.

Jalur alias `application.views.site.loginForm` sebenarnya merujuk ke file PHP `protected/views/site/loginForm.php`.
File ini akan mengembalikan larik(array) PHP yang mewakiliki konfigurasi [CForm] yang diperlukan, seperti di bawah ini:

~~~
[php]
return array(
	'title'=>'Please provide your login credential',

    'elements'=>array(
        'username'=>array(
            'type'=>'text',
            'maxlength'=>32,
        ),
        'password'=>array(
            'type'=>'password',
            'maxlength'=>32,
        ),
        'rememberMe'=>array(
            'type'=>'checkbox',
        )
    ),

    'buttons'=>array(
        'login'=>array(
            'type'=>'submit',
            'label'=>'Login',
        ),
    ),
);
~~~

Konfigurasi tersebut merupakan sebuah larik yang terdiri dari pasangan nama-nilai yang digunakan
untuk menginisialisasi properti yang berhubungan pada [CForm]. Properti yang paling penting untuk diatur,
seperti yang telah disinggung, adalah [CForm::elements] dan [CForm::buttons]. Setiap dari elemen ini
memerlukan sebuah larik untuk menentukan daftar elemen form. Kita akan melihat lebih detail
bagaimana mengatur elemen form pada subbab berikutnya.

Akhirnya, kita menulis sebuah skrip view, yang dapat sesederhana berikut ini,

~~~
[php]
<h1>Login</h1>

<div class="form">
<?php echo $form; ?>
</div>
~~~

> Tip: Kode di atas `echo $form;` adalah ekuivalen dengan `echo $form->render();`.
> Alasannya karena [CForm] mengimplementasi metode `__toString` yang akan memanggil
> `render()` dan mengembalikan hasilnya sebagai string yang berisi objek form.


Menspesifikasi Elemen Form
--------------------------

Ketika menggunakan sebuah form builder, tugas utama kita akan beralih dari menulis kode skrip view menjadi
menentukan elemen form. Pada subbab ini, kita akan mempelajari bagaimana menspesifikasi properti [CForm::elements].
Kita tidak akan menjelaskan [CForm::buttons] karena konfigurasinya hampir sama dengan [CForm::elements]

Properti [CForm::elements] menerima sebuah larik sebagai nilainya. Setiap elemen larik menspesifikasi sebuah
elemen form yang akan menjadi elemen input, sebuah teks statik atau sebuah sub-form.

### Menspesifikasi Elemen Input

Sebuah input elemen terdiri dari sebuah label, sebuah field input, dan sebuah teks petunjuk dan sebuah tampilan error.
Elemen input harus berasosiasi dengan atribut model. Spesifikasi untuk sebuah elemen input diwakilkan
sebagai sebuah instance dari [CFormInputElement]. Kode berikut akan menspesifikasi input tunggal dalam larik [CForm::elements]:

~~~
[php]
'username'=>array(
    'type'=>'text',
    'maxlength'=>32,
),
~~~

Di situ menjelaskan bahwa atribut model bernama `username`, dan input field berjenis `text` yang nilai atribut `maxlength` adalah 32

Setiap properti [CFormInputElement] yang dapat ditulis dapat dikonfigurasi dengan cara demikian. Misalnya, kita dapat
menspesifikasi opsi [hint|CFormInputElement::hint] untuk menampilkan teks petunjuk, atau kita dapat menspesifikasi
opsi [items|CFormInputElement::items] jika field input-nya berupa sebuah list box, drop-down list, sebuah list check-box
atau sebuah list radio-button. Jika sebuah nama opsi bukan properti dari [CFormInputElement], maka akan dianggap
sebagai atribut HTML dari elemen input bersangkutan. Misalnya, karena `maxlength` di contoh di atas bukanlah sebuah
properti dari [CFormInputElement], maka `maxlength` akan di-render sebagai `maxlength` dari atribut HTML field input teks.

Opsi [type|CFormInputElement::type] patut mendapat perhatian tambahan. Opsi ini menspesifikasi jenis field input
yang akan di-render. Misalnya, jenis `text` artinya adalah field input teks normal yang harus di-render;
jenis `password` artinya sebuah input field password yang harus di-render. [CFormInputElement] mampu mengenali jenis-jenis built-in seperti:

 - text
 - hidden
 - password
 - textarea
 - file
 - radio
 - checkbox
 - listbox
 - dropdownlist
 - checkboxlist
 - radiolist

Di antara jenis-jenis built-in tersebut, kita akan mempelajari sedikit lebih dalam mengenai penggunaan jenis "list",
yakni `dropdownlist`, `checkboxlist` dan `radiolist`. Jenis-jenis ini memerlukan pengaturan properti [items|CFormInputElement::items]
pada elemen input yang bersangkutan. Kita dapat mengaturnya demikian:

~~~
[php]
'gender'=>array(
    'type'=>'dropdownlist',
    'items'=>User::model()->getGenderOptions(),
    'prompt'=>'Please select:',
),

...

class User extends CActiveRecord
{
	public function getGenderOptions()
	{
		return array(
			0 => 'Male',
			1 => 'Female',
		);
	}
}
~~~

Kode di atas akan menghasilkan sebuah selector berupa drop-down list dengan teks "please select:". Opsi selector
termasuk "Male" dan "Female", yang dikembalikan oleh metode `getGenderOptions` di dalam kelas model `User`.

Selain jenis-jenis built-in, opsi [type|CFormInputElement::type] juga dapat mengambil sebuah nama kelas widget
atau jalur alias ke sana. Kelas widget haruslah diturunkan dari [CInputWidget] atau [CJuiInputWidget]. Ketika me-render elemen input,
sebuah instance dari kelas widget yang dispesifikasi akan diciptakan dan di-render. Widget tersebut akan diatur supaya
menggunakan spesifikasi yang diberikan untuk elemen input.


### Menspesifikasi Teks Statik

Dalam banyak kasus, sebuah form bisa saja mengandung beberapa kode HTML yang bersifat dekorasi selain field input. Misalnya, sebuah
garis horizontal(horizontal line) mungkin diperlukan untuk memisahkan bagian pada form, sebuah gambar diperlukan di beberapa tempat
untuk meningkatkan sisi visual pada form. Kita dapat menspesifikasi kode HTML ini sebagai teks statik di kumpulan [CForm::elements].
Misalnya:

~~~
[php]
return array(
    'elements'=>array(
		......
        'password'=>array(
            'type'=>'password',
            'maxlength'=>32,
        ),

        '<hr />',

        'rememberMe'=>array(
            'type'=>'checkbox',
        )
    ),
	......
);
~~~

Pada kode di atas, kita memasukkan sebuah garis horizontal di antara input `password` dan input `rememberMe`.

Teks statik paling cocok digunakan ketika konten berisi teks dan posisi mereka tidak menentu. Jika setiap
elemen input dalam sebuah form diperlukan untuk dekorasi sejenisnya, kita harus mengkustomisasi pendekatan
cara me-render form. Hal ini akan dijelaskan setelah bagian ini.


### Menspesifikasi Sub-form

Sub-form dipakai untuk memisahkan sebuah form panjang menjadi beberapa bagian yang secara logikal terkoneksi.
Misalnya, kita mungkin ingin memisahkan form registrasi user menjadi dua buah sub-form: informasi login
dan informasi pribadi. Setiap sub-form bisa saja berhubungan dengan sebuah data model ataupun tidak. Dalam contoh form registrasi user,
jika kita menyimpan informasi login user dan informasi pribadi ke dalam dua buah tabel database yang berbeda
(yang berarti dua buah model data), maka tiap sub-form akan dihubungkan dengan data model bersangkutan. Jika kita menyimpan
semuanya ke dalam sebuah tabel database, maka tidak ada satu sub-form pun yang memiliki data model karena mereka
memiliki model yang sama dari form root-nya.

Sebuah sub-form juga mewakili objek [CForm]. Untuk menspesifikasi sebuah sub-form, kita harus mengatur
properti [CForm::elements] dengan elemen yang tipenya `form`:

~~~
[php]
return array(
    'elements'=>array(
		......
        'user'=>array(
            'type'=>'form',
            'title'=>'Login Credential',
            'elements'=>array(
            	'username'=>array(
            		'type'=>'text',
            	),
            	'password'=>array(
            		'type'=>'password',
            	),
            	'email'=>array(
            		'type'=>'text',
            	),
            ),
        ),

        'profile'=>array(
        	'type'=>'form',
        	......
        ),
        ......
    ),
	......
);
~~~

Seperti mengkonfigurasi form root, kita mengatur properti [CForm::elements] untuk sebuah sub-form. Jika
sebuah sub-form perlu diasosiasikan dengan sebuah data model, maka kita mengkonfigurasi properti [CForm::model] juga.

Kadang-kadang, kita ingin mewakili sebuah form dengan menggunakan kelas yang bukan dari [CForm]. Misalnya,
seperti yang akan dipelajari sebentar lagi, kita ingin menurunkan [CForm] untuk mengatur logikal rendering dari form.
Dengan menspesifikasi jenis elemen input menjadi `form`, sebuah sub-form akan otomatis mewakili sebuah
ojbek yang kelasnya sama dengan form induknya. Jika kita menspesifikasi jenis elemen input menjadi sesuatu
seperti `XyzForm` (sebuah string yang diakhiri dengan `Form`), maka sub-form akan diwakilkan sebagai objek `XyzForm`.


Mengakses Elemen Form
---------------------

Mengakses elemen form segampang mengakses elemen larik(array). Properti [CForm::elements] mengembalikan
sebuah objek [CFormElementCollection], yang diturunkan dari [CMap] dan memungkinkan mengakses elemennya seperti
sebuah larik normal. Misalnya, untuk mengakses elemen `username` di contoh form login, kita dapat menggunakan
kode berikut:

~~~
[php]
$username = $form->elements['username'];
~~~

Dan untuk mengakses elemen `email` di contoh form registrasi user, kita dapat menggunakan

~~~
[php]
$email = $form->elements['user']->elements['email'];
~~~

Karena [CForm] mengimplementasi pengaksesan larik untuk properti [CForm::elements]-nya, kode di atas dapat 
disederhanakan lagi menjadi:

~~~
[php]
$username = $form['username'];
$email = $form['user']['email'];
~~~


Membuat sebuah Nested Form(Form Bersarang)
------------------------------------------

Kita sudah mempelajari sub-form. Kita memanggil sebuah form dengan sub-form sebagai nested form. Pada
bagian ini kita akan menggunakan form registrasi user sebagai contoh untuk melihat bagaimana membuat
sebuah nested form yang berhubungan dengan beberapa model data. Kita mengasumsi bahwa informasi penting
di model `User` dan informasi pribadi user di dalam model `Profile`.

Pertama-tama kita membuat action `register`:

~~~
[php]
public function actionRegister()
{
	$form = new CForm('application.views.user.registerForm');
	$form['user']->model = new User;
	$form['profile']->model = new Profile;
	if($form->submitted('register') && $form->validate())
	{
		$user = $form['user']->model;
		$profile = $form['profile']->model;
		if($user->save(false))
		{
			$profile->userID = $user->id;
			$profile->save(false);
			$this->redirect(array('site/index'));
		}
	}

	$this->render('register', array('form'=>$form));
}
~~~

Pada contoh di atas, kita membuat sebuah form dengan menggunakan konfigurasi yang dispesifikasi oleh `application.views.user.registerForm`.
Setelah form di-submit dan divalidasi dengan sukses, kita mencoba untuk menyimpan model user dan profile.
Kita mendapatkan model user dan profile dengan mengakses properti `model` dari objek sub-form bersangkutan.
Karena validasi input sudah dilakukan, kita memanggil `$user->save(false)` untuk melewati validasi. Kita melakukan
hal yang sama untuk model profile.

Berikutnya, kita menulis sebuah file konfigurasi form `protected/views/user/registerForm.php`:

~~~
[php]
return array(
	'elements'=>array(
		'user'=>array(
			'type'=>'form',
			'title'=>'Login information',
			'elements'=>array(
		        'username'=>array(
		            'type'=>'text',
		        ),
		        'password'=>array(
		            'type'=>'password',
		        ),
		        'email'=>array(
		            'type'=>'text',
		        )
			),
		),

		'profile'=>array(
			'type'=>'form',
			'title'=>'Profile information',
			'elements'=>array(
		        'firstName'=>array(
		            'type'=>'text',
		        ),
		        'lastName'=>array(
		            'type'=>'text',
		        ),
			),
		),
	),

    'buttons'=>array(
        'register'=>array(
            'type'=>'submit',
            'label'=>'Register',
        ),
    ),
);
~~~

Pada contoh di atas, ketika kita menspesifikasi setiap sub-form, kita juga menspesifikasi properti [CForm::title].
Logika render form yang default akan disertakan pada tiap sub-form dalam sebuah field-set yang menggunakan properti ini sebagai title-nya.

Terakhir, kita menulis sebuah skrip view `register`:

~~~
[php]
<h1>Register</h1>

<div class="form">
<?php echo $form; ?>
</div>
~~~


Kustomisasi Tampilan Form
------------------------

Manfaat utama menggunakan form builder adalah pemisahan logika (konfigurasi form disimpan di file terpisah)
dan presentasi (metode [CForm::render]). Sebagai hasilnya, kita dapat mengkustomisasi tampilan form dengan cara meng-override
[CForm::render] atau menyediakan sebuah partial view untuk me-render form. Kedua pendekatan ini dapat memastikan
bahwa konfigurasi tetap utuh dan bisa digunakan ulang secara gampang.

Ketika meng-override [CForm::render], tujuan utamanya adalah melewati kumpulan [CForm::elements] dan [CForm::buttons]
dan memanggil metode [CFormElement::render] untuk setiap elemen. Misalnya,

~~~
[php]
class MyForm extends CForm
{
	public function render()
	{
		$output = $this->renderBegin();

		foreach($this->getElements() as $element)
			$output .= $element->render();

		$output .= $this->renderEnd();

		return $output;
	}
}
~~~

Kita juga bisa menulis sebuah skrip view `_form` untuk me-render form

~~~
[php]
<?php
echo $form->renderBegin();

foreach($form->getElements() as $element)
	echo $element->render();

echo $form->renderEnd();
~~~

Untuk menggunakan skrip view, kita cukup memanggil:

~~~
[php]
<div class="form">
$this->renderPartial('_form', array('form'=>$form));
</div>
~~~

Jika sebuah form umum tidak bekerja pada form tertentu (misalnya, form-nya memerlukan
dekorasi yang tidak beraturan untuk elemen tertentu), kita dapat melakukan hal berikut pada skrip view:

~~~
[php]
some complex UI elements here

<?php echo $form['username']; ?>

some complex UI elements here

<?php echo $form['password']; ?>

some complex UI elements here
~~~

Pendekatan terakhir, form builder kelihatannya seperti tidak terlalu banyak membawa manfaat, karena
kita masih harus menulis sebagian besar kode form. Tetapi sebetulnya masih membawa faedah, karena kita
menspesifikasi form dengan menggunakan file konfigurasi yang terpisah sehingga membantu developer untuk
lebih fokus ke logika.


<div class="revision">$Id: form.builder.txt 2890 2011-01-18 15:58:34Z qiang.xue $</div>