Model
=====

Model adalah sebuah instance dari [CModel] atau sebuah class yang menurunkan [CModel]. Model digunakan
untuk menyimpan data dan aturan bisnis yang relevan.

Sebuah model mewakili sebuah objek data tunggal. Dapat berupa sebuah baris dalam
tabel database atau sebuah form html dengan field input user. Setiap field atau objek data diwakili
oleh sebuah atribut model. Atribut memiliki sebuah label dan dapat divalidasi terhadap sekumpulan
aturan.

Yii mengimplementasi dua jenis model: Model form dan active record. Mereka
menurunkan dari kelas dasar yang sama, [CModel].

Sebuah model form adalah instance dari [CFormModel]. Model form digunakan
untuk menyimpan data yang dikumpulkan dari input user. Data ini biasanya dikumpulkan,
digunakan dan kemudian dibuang. Misalnya, halaman login, kita bisa menggunakan model form untuk 
mewakili informasi username dan password yang disediakan oleh user. Untuk informasi lebih lanjut, silahkan 
merujuk ke [Bekerja dengan Form](/doc/guide/form.overview)

Active Record (AR) merupakan sebuah pattern desain yang digunakan untuk mengabstraksi akses database
dalam bentuk orientasi-objek. Setiap objek AR adalah instance dari
[CActiveRecord] atau sebuah sub-kelas dari kelas itu, mewakili sebuah baris tunggal dalam tabel
database. Field-field dalam baris dapat direpresentasikan dengan properti pada objek AR.
Informasi lengkap mengenai AR dapat ditemukan di [Active Record](/doc/guide/database.ar).

<div class="revision">$Id: basics.model.txt 3251 2011-06-01 00:24:06Z qiang.xue $</div>
