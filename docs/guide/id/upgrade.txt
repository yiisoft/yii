Upgrade dari Versi 1.0 ke 1.1
=============================

Perubahan yang Berhubungan dengan Skenario Model
------------------------------------------------

- Menghilangkan CModel::safeAttributes(). Atribut yang disimpan(save) sekarang
didefinisikan menjadi atribut yang divalidasi oleh rule-rule yang didefinisikan
seperti yang di CModel::rules() untuk skenario tertentu.

- Perubahan pada CModell::validate(), CModel::beforeValidate() dan CModel::afterValidate().
CModel::setAttributes(), CModel::getSafeAttributeNames()
Parameter 'scenario' dihilangkan. Anda harus get dan set scenario model dengan menggunakan
CModel::scenario.

- Perubahan pada CModel::getValidators() dan menghapus CModel::getValidatorsForAttribute().
CModel::getValidators() sekarang hanya mengembalikan validator, diterapkan pada skenario
yang terdapat pada property scenario-nya model.

- Perubahan pada CModel::isAttributeRequired() and CModel::getValidatorsForAttribute().
Parameter scenario dihilangkan. Alih-alih, properti scenario-nya model yang akan digunakan.

- Menghapus CHtml::scenario. Alih-alih, CHtml akan menggunakan scenario-nya model.


Perubahan yang Berhubungan dengan Eager Loading untuk Rekaman Aktif Relasional
------------------------------------------------------------------------------

- Secara default, sebuah statement JOIN akan dihasilkan dan dieksekusi
untuk seluruh relasi yang terlibat dalam eager loading. Jika tabel utama
memiliki opsi query `LIMIT` atau `OFFSET`, maka akan di-query sendirian terlebih dahulu,
baru diikuti oleh statement SQL lainnya yang akan menghasilkan semua objek yang berelasi padanya.
Pada versi 1.0.x sebelumnya, yang terjadi secara default adalah akan ada sebanyak `N+1` statement SQL
jika eager loading melibatkan relasi `N` `HAS_MANY` atau `MANY_MANY`.

Perubahan yang Berhubungan dengan Alias Tabel dalam Rekaman Aktif Relasional
----------------------------------------------------------------------------

- Sekarang, secara default alias pada tabel relasional sama dengan nama relasi.
Sebelumnya pada veri 1.0.x, secara default Yii akan menghasilkan sebuah alias tabel
untuk setiap tabel relasional, dan kita harus menggunakan prefiks `??.` untuk merujuk
ke alias yang dihasilkan otomatis ini.

- Nama alias untuk tabel utama dalam query AR ditetapkan sebagai `t`. Sebelumnya
di versi 1.0.x, aliasnya memiliki nama yang sama dengan nama tabel. Cara ini akan
menyebabkan kode query AR yang sudah ada akan rusak jika mereka mespesifikasi
prefiks kolom secara eksplisit dengan nama tabel. Solusinya adalah mengubah
prefiks-prefiks ini dengan 't.'.


Perubahan yang Berhubungan dengan Tabular Input
-----------------------------------------------

- Untuk nama atribut, penggunaan `Field[$i]` sudah tidak valid lagi. Sekarang
nama atribut harus kelihatan seperti ini `[$i]Field` untuk mendukung field yang berlarik(array)
(misalnya `[$i]Field[$index]`).

Perubahan Lainnya
-----------------

- Signature(definisi method) pada konstruktor [CActiveRecord] sudah berubah. Parameter pertama (daftar atribut) dihilangkan.

<div class="revision">$Id: upgrade.txt 2305 2010-08-06 10:27:11Z alexander.makarow $</div>