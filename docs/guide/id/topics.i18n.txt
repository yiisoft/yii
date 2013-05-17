Internasionalisasi
==================

Internasionalisasi (I18N) merujuk pada proses mendesain aplikasi software
agar bisa diadaptasi ke berbagai bahasa dan wilayah tanpa perubahan
proses pembuatannya. Untuk aplikasi Web, proses ini merupakan bagian penting karena
pengguna potensial mungkin datang dari seluruh dunia.

Yii menyediakan dukungan I18N dalam beberapa aspek.

   - Menyediakan data lokal untuk setiap bahasa dan varian yang mungkin.
   - Menyediakan layanan pesan dan file terjemahan.
   - Menyediakan pembentukan tanggal dan jam dependen-lokal.
   - Menyediakan pembentukan angka dependen-lokal.

Dalam subseksi berikut, kita akan mengelaborasi setiap aspek di atas.

Lokal dan Bahasa
----------------

Lokal (locale) adalah sebuah set parameter yang mendefinisikan bahasa pengguna, negara
dan setiap preferensi varian khusus yang diinginkan pengguna melihatnya dalam
antar muka mereka. Lokal biasanya diidentifikasi dengan ID yang terdiri dari ID bahasa
dan ID wilayah. Sebagai contoh, ID `en_US` singkatan dari lokal bahasa Inggris dan
Amerika. Untuk konsistensi, semua ID lokal dalam Yii adalah dikanonikal
ke  format `LanguageID` atau `LanguageID_RegionID`
dalam huruf kecil (misalnya `en`, `en_us`).

Data lokal disajikan sebagai instance dari [CLocale]. Ia menyediakan informasi
dependen-lokal, termasuk simbol kurs, simbol angka, format kurs,
format angka, format tanggal dan jam, dan nama terkait-tanggal.
Karena sudah disatukan dalam ID lokal, maka informasi bahasa  tidak
disediakan oleh [CLocale]. Untuk alasan yang sama, kita sering
saling bertukar istilah lokal dengan bahasa.

Dengan menentukan ID lokal, Anda bisa mendapatkan instance [CLocale] terkait dengan
`CLocale::getInstance($localeID)` atau `CApplication::getLocale($localeID)`.

> Info: Yii datang dengan data lokal untuk hampir semua bahasa dan wilayah.
Data diperoleh dari [Repositori Data Lokal Umum](http://unicode.org/cldr/) (CLDR). Untuk setiap lokal, hanya
sebuah subset data CLDR yang disediakan karena data aslinya berisi banyak informasi yang
jarang dipakai. Pengguna juga dapat menyediakan data lokal kustomisasi mereka sendiri.
Untuk melakukannya, konfigurasikan properti [CApplication::localeDataPath] dalam direktori yang
berisi data lokal terkustomisasi.
Silahkan merujuk file data lokal yang terletak di `framework/i18n/data`
guna membuat file data lokal kustomisasi.

Untuk aplikasi Yii, kita membedakan [target
bahasa|CApplication::language] dari [sumber
bahasa|CApplication::sourceLanguage]. Target bahasa adalah bahasa
(lokal) pengguna yang aplikasinya ditargetkan baginya, sementara sumber bahasa
merujuk ke bahasa (lokal) penulisan file sumber aplikasi.
Internasionalisasi terjadi hanya ketika dua bahasa
berbeda.

Anda dapat mengkonfigurasi [target bahasa|CApplication::language] dalam
[konfigurasi aplikasi](/doc/guide/basics.application#application-configuration), atau
mengubahnya secara dinamis sebelum internasionalisasi terjadi.

> Tip: Adakalanya kita mungkin ingin menyetel target bahasa seperti bahasa yang diinginkan
oleh pengguna (ditetapkan dalam preferensi browser pengguna). Untuk melakukannya, kita
dapat mengambil ID bahasa yang diinginkan pengguna menggunakan
[CHttpRequest::preferredLanguage].

Terjemahan
----------

Fitur I18N yang paling dibutuhkan barangkali adalah terjemahan, termasuk terjemahan
pesan dan terjemahan tampilan. Terjemahan pesan menerjemahkan pesan teks ke bahasa
yang diinginkan, sementara terjemahan tampilan menerjemahkan seluruh file ke bahasa
yang dinginkan.

Permintaan terjemahan terdiri dari objek yang diterjemahkan, sumber
bahasa di mana objek itu berada, dan target bahasa ke mana objek harus
diterjemahkan. Dalam Yii, sumber bahasa distandarkan ke
[sumber bahasa aplikasi|CApplication::sourceLanguage] sementara target bahasa
distandarkan ke [bahasa aplikasi|CApplication::language].
Jika sumber dan target bahasa sama, terjemahan tidak
terjadi.

### Terjemahan Pesan

Terjemahan pesan dikerjakan dengan memanggil [Yii::t()|YiiBase::t]. Metode
menerjemahkan pesan yang didapat dari [sumber
bahasa|CApplication::sourceLanguage] ke [target
bahasa|CApplication::language].

Saat menerjemahkan pesan, kategorinya harus ditetapkan karena
pesan mungkin diterjemahkan secara berbeda di bawah kategori
(konteks) berbeda. Kategori `yii` terpakai untuk pesan yang digunakan oleh kode inti Yii
framework.

Pesan bisa berisi penampung parameter yang akan diganti dengan nilai parameter
sebenarnya saat memanggil [Yii::t()|YiiBase::t]. Sebagai
contoh, permintaan terjemahan pesan berikut akan mengganti penampung
`{alias}` dalam pesan asli dengan nilai alias sebenarnya.

~~~
[php]
Yii::t('app', 'Path alias "{alias}" is redefined.',
	array('{alias}'=>$alias))
~~~

> Note|Catatan: Pesan yang diterjemahkan harus berupa konstan string.
Tidak boleh berisi variabel yang akan mengubah konten pesan (misalnya `"Invalid
{$message} content."`). Gunakan penampung parameter jika pesan harus bervariasi
berdasarkan pada beberapa parameter.

Pesan yang diterjemahkan disimpan dalam sebuah repositori yang disebut *sumber
pesan*. Sumber pesan disajikan sebagai instance
[CMessageSource] atau anak kelasnya. Ketika [Yii::t()|YiiBase::t] dipanggil,
ia akan mencari pesan dalam sumber pesan dan mengembalikan versi terjemahan
jika ditemukan.

Yii datang dengan jenis sumber pesan berikut ini. Anda juga boleh memperluas
[CMessageSource] untuk mengubah jenis sumber pesan Anda sendiri.

   - [CPhpMessageSource]: terjemahan pesan disimpan sebagai pasangan kunci-nilai
dalam array PHP. Pesan asli adalah kunci dan terjemahan pesan adalah nilai.
Setiap array mewakili terjemahan untuk kategori pesan tertentu dan
disimpan dalam file skrip PHP terpisah
yang namanya adalah nama kategori. File terjemahan PHP untuk bahasa yang
sama disimpan di bawah direktori yang sama dinamai sebagai ID lokal. Dan
semua direktori ini ditempatkan di bawah direktori yang ditetapkan dalam
[basePath|CPhpMessageSource::basePath].

   - [CGettextMessageSource]: terjemahan pesan disimpan sebagai file [GNU
Gettext](http://www.gnu.org/software/gettext/).

   - [CDbMessageSource]: terjemahan pesan disimpan dalam tabel database.
Untuk lebih jelasnya, lihat dokumentasi API untuk [CDbMessageSource].

Sumber pesan diambil sebagai [komponen
aplikasi](/doc/guide/basics.application#application-component). Pra-deklarasi
komponen aplikasi Yii bernama [messages|CApplication::messages] untuk menyimpan
pesan yang dipakai dalam aplikasi pengguna. Distandarkan, jenis sumber pesan ini
adalah [CPhpMessageSource] dan path basis untuk penyimpanan file terjemahan PHP
adalah `protected/messages`.

Ringkasnya, untuk menggunakan terjemahan pesan, diperlukan langkah-langkah
berikut:

   1. Panggil [Yii::t()|YiiBase::t] di tempat yang sesuai;

   2. Buat file terjemahan PHP sebagai
`protected/messages/LocaleID/CategoryName.php`. Setiap file mengembalikan sebuah
array terjemahan pesan. Harap dicatat, ini beranggapan bahwa Anda menggunakan
[CPhpMessageSource] default untuk menyimpan pesan yang diterjemahkan.

   3. Konfigurasi [CApplication::sourceLanguage] dan [CApplication::language].

> Tip: Piranti `yiic` dalam Yii bisa dipakai untuk mengatur terjemahan pesan
ketika [CPhpMessageSource] dipakai sebagai sumber pesan. Perintah `message` dapat
secara otomatis mengurai pesan yang akan diterjemahkan dari file sumber yang dipilih
dan menggabungnya dengan terjemahan yang sudah ada bila diperlukan. Untuk menggunakan
perintah `message`, silahkan menjalankan perintah `yiic help message`.

Ketika menggunakan [CPhpMessageSource] untuk mengatur sumber pesan,
pesan untuk sebuah kelas extension (seperti sebuah widget, module) dapat digunakan dan atur secara spesial.
Khususnya, jika sebuah pesan milik extension dengan nama `Xyz`,
maka kategori pesan dapat diasumsi sebagai
`BasePath/messages/LanguageID/categoryName.php`, dengan `BasePath` dirujuk
ke direktori yang berisi file kelas extension. Dan ketika menggunakan `Yii::t()` untuk
menerjemah pesan extension, format berikut harus digunakan :

~~~
[php]
Yii::t('Xyz.categoryName', 'message to be translated')
~~~

Yii mendukung [choice format|CChoiceFormat]. Choice format
merujuk ke pemilihan terjemahan berdasarkan nilai angka yang diberikan. Sebagai contoh,
dalam bahasa Inggris, kata 'book' mungkin berupa bentuk tunggal atau bentuk jamak tergantung
pada jumlah buku, sementara dalam bahasa lain, kata mungkin tidak memiliki bentuk
berbeda (misalnya tulisan China) atau memiliki aturan bentuk jamak lebih kompleks
(misalnya bahasa Rusia). Pilihan format memecahkan masalah ini dengan cara sederhana namun efektif.

Untuk menggunakan pilihan format, pesan yang diterjemahkan harus terdiri dari pasangan
urutan ekspresi-pesan dipisahkan dengan `|`, seperti terlihat di bawah ini:

~~~
[php]
'expr1#message1|expr2#message2|expr3#message3'
~~~

di mana `exprN` merujuk ke ekspresi valid PHP yang mengevaluasi ke nilai boolean
menunjukan apakah pesan terkait harus dikembalikan atau tidak. Hanya pesan
terkait ke ekspresi pertama yang hasil evaluasinya true akan dikembalikan.
Ekspresi bisa berisi variabel spesial bernama `n` (catatan, ini bukan `$n`)
yang akan mengambil jumlah nilai yang dioper sebagai parameter pesan pertama. Sebagai contoh,
menganggap pesan yang diterjemahkan adalah:

~~~
[php]
'n==1#one book|n>1#many books'
~~~

dan kita mengoper nilai angka 2 dalam array parameter pesan saat
memanggil [Yii::t()|YiiBase::t], kita akan memperoleh  `many books` sebagai pesan
yang diterjemahkan:

~~~
[php]
Yii::t('app', 'n==1#one book|n>1#many books', array(1)));
//or since 1.1.6
Yii::t('app', 'n==1#one book|n>1#many books', 1));
~~~

Sebagai notasi jalan pintas, jika ekspresi adalah angka, ia akan diperlakukan sebagai
`n==Number`. Oleh karena itu pesan yang diterjemahkan juga bisa ditulis seperti:

~~~
[php]
'1#one book|n>1#many books'
~~~

### Format Bentuk Jamak

Sejak versi 1.1.6 choice format jamak yang berbasis CLDR dapat digunakan
dengan sintaks yang lebih sederhana. Fungsi ini sangat berguna untuk bahasa yang memiliki aturan jamak yang kompleks



Aturan untuk bentuk jamak dalam bahasa Inggris dapat ditulis sebagai berikut:

~~~
[php]
Yii::t('test', 'cucumber|cucumbers', 1);
Yii::t('test', 'cucumber|cucumbers', 2);
Yii::t('test', 'cucumber|cucumbers', 0);
~~~

Code di atas akan memberikan hasil

~~~
cucumber
cucumbers
cucumbers
~~~

Jika ingin menyertakan angka Anda dapat menggunakan code berikut.

~~~
[php]
echo Yii::t('test', '{n} cucumber|{n} cucumbers', 1);
~~~

Di sini `{n}` merupakan placeholder spesial yang menyimpan nilai yang dipassing. Code ini akan mencetak `1 cucumber`.

Anda dapat pass parameter-parameter tambahan:

~~~
[php]
Yii::t('test', '{username} has a cucumber|{username} has {n} cucumbers',
array(5, '{username}' => 'samdark'));
~~~

dan bahkan menggantikan angka parameter dengan yang lainnya:

~~~
[php]
function convertNumber($number)
{
	// convert number to word
	return $number;
}

Yii::t('test', '{n} cucumber|{n} cucumbers',
array(5, '{n}' => convertNumber(5)));
~~~

Untuk bahasa Rusia akan seperti ini
~~~
[php]
Yii::t('app', '{n} cucumber|{n} cucumbers', 62);
Yii::t('app', '{n} cucumber|{n} cucumbers', 1.5);
Yii::t('app', '{n} cucumber|{n} cucumbers', 1);
Yii::t('app', '{n} cucumber|{n} cucumbers', 7);
~~~

dengan pesan yang diterjemahkan

~~~
[php]
'{n} cucumber|{n} cucumbers' => '{n} огурец|{n} огурца|{n} огурцов|{n} огурца',
~~~

akan memberikan hasil

~~~
62 огурца
1.5 огурца
1 огурец
7 огурцов
~~~


> Info: untuk mempelajari berapa banyak nilai yang harus anda sediakan dan
bagaimana urutan yang seharusnya, silahkan merujuk ke CLDR
[halaman Language Plural Rules] (http://unicode.org/repos/cldr-tmp/trunk/diff/supplemental/language_plural_rules.html).

### Terjemahan File

Terjemahan file dilakukan dengan memanggil
[CApplication::findLocalizedFile()]. Path file yang akan
diterjemahkan, metode akan mencari file dengan nama yang sama di bawah
subdirektori `LocaleID`. Jika ditemukan, path file yang akan dikembalikan;
jika tidak, path file asli yang akan dikembalikan.

Terjemahan file dipakai terutama saat membuat tampilan. Ketika memanggil salah satu
metode render dalam controller atau widget, file tampilan akan diterjemahkan
secara otomatis. Sebagai contoh, jika [target
bahasa|CApplication::language] adalah `zh_cn` sementara [sumber
bahasa|CApplication::sourceLanguage] adalah `en_us`, pembuatan tampilan bernama
`edit` akan mencari file tampilan
`protected/views/ControllerID/zh_cn/edit.php`. Jika file ditemukan, versi
terjemahan ini yang dipakai untuk pembuatan; jika tidak, file
`protected/views/ControllerID/edit.php` yang akan dibuat.

Terjemahan file juga dapat dipakai untuk keperluan lain, misalnya
menampilkan gambar terjemahan atau pengambilan file data dependen-lokal.

Pembentukan Tanggal dan Jam
---------------------------

Tanggal dan jam sering berbeda bentuk di negara atau wilayah berbeda.
Tugas pembentukan tanggal dan jam menghasilkan string tanggal dan jam
yang sesuai dengan lokal yang ditetapkan. Yii menyediakan
[CDateFormatter] untuk keperluan ini.

Setiap instance [CDateFormatter] dikaitkan dengan target lokal. Untuk mendapatkan
pembentuk yang dikaitkan dengan target lokal pada seluruh aplikasi,
kita cukup mengakses properti [dateFormatter|CApplication::dateFormatter]
pada aplikasi.

Kelas [CDateFormatter] menyediakan dua metode untuk membentuk UNIX
timestamp.

   - [format|CDateFormatter::format]: metode ini membentuk UNIX
timestamp ke dalam string berdasarkan pola yang dikustomisasi (misalnya
`$dateFormatter->format('yyyy-MM-dd',$timestamp)`).

   - [formatDateTime|CDateFormatter::formatDateTime]: metode ini membentuk
UNIX timestamp ke dalam string berdasarkan pola pradefinisi dalam data target
lokal (misalnya format tanggal `short`, format waktu
`long`).

Pemformatan Angka
-----------------

Seperti tanggal dan jam, angka juga bisa dibentuk secara berbeda di
negara atau wilayah yang berbeda. Pemformatan angka termasuk pemformatan desimal,
pemformatan kurs dan pemformatan persentase. Yii menyediakan
[CNumberFormatter] untuk tugas-tugas ini.

Untuk mendapatkan pemformat angka terkait dengan target lokal pada seluruh
aplikasi, kita dapat mengakses properti
[numberFormatter|CApplication::numberFormatter] pada
aplikasi.

Metode berikut disediakan oleh [CNumberFormatter] untuk memformat nilai
integer atau double.

   - [format|CNumberFormatter::format]: metode ini memformat angka
ke dalam string berdasarkan pada pola kustomisasi (misalnya
`$numberFormatter->format('#,##0.00',$number)`).

   - [formatDecimal|CNumberFormatter::formatDecimal]: metode ini memformat angka
menggunakan pola desimal pradefinisi dalam data lokal
target.

   - [formatCurrency|CNumberFormatter::formatCurrency]: metode ini
memformat angka dan kode kurs menggunakan pola kurs
pradefinisi dalam data lokal target.

   - [formatPercentage|CNumberFormatter::formatPercentage]: metode ini
memformat angka menggunakan pola persentase pradefinisi dalam data lokal
target.

<div class="revision">$Id: topics.i18n.txt 2890 2011-01-18 15:58:34Z qiang.xue $</div>
