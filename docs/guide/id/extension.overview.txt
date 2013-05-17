Meng-extend Yii
========

Memperluas Yii merupakan kegiatan umum selama pengembangan. Contohnya, saat
Anda membuat controller baru, Anda memperluas Yii dengan menurunkan kelas [CController]
dan saat membuat widget baru, Anda memperluas [CWidget] atau kelas widget
lain yang sudah ada. Jika kode yang diperluas, didesain untuk dipakai ulang, oleh pengembang
pihak ketiga, kita menyebutnya sebagai *extension*.

Extension biasanya melakukan satu pekerjaan tertentu. Dalam batasan Yii, extension dapat
diklasifikasikan sebagai berikut,

 * [komponen aplikasi](/doc/guide/basics.application#application-component)
 * [behavior](/doc/guide/basics.component#component-behavior)
 * [widget](/doc/guide/basics.view#widget)
 * [controller](/doc/guide/basics.controller)
 * [aksi](/doc/guide/basics.controller#action)
 * [filter](/doc/guide/basics.controller#filter)
 * [perintah konsol](/doc/guide/topics.console)
 * validator: validator adalah kelas komponen yang memperluas [CValidator].
 * helper: helper adalah kelas yang hanya dengan metode statis saja. Kelas ini mirip fungsi global
   yang menggunakan nama kelas sebagai namespace-nya.
 * [module](/doc/guide/basics.module): module adalah unit software berdiri sendiri yang terdiri dari [model](/doc/guide/basics.model), [tampilan](/doc/guide/basics.view), [controller](/doc/guide/basics.controller) dan komponen pendukung lainnya. Dalam banyak aspek, module mirip dengan [aplikasi](/doc/guide/basics.application). Perbedaan utamanya adalah bahwa module ada di dalam aplikasi. Sebagai contoh, kita dapat memiliki module yang menyediakan fungsionalitas manajemen pengguna.

Extension juga dapat berupa komponen yang tidak masuk ke dalam kategori
di atas. Bahkan, Yii didesain secara teliti sehingga hampir seluruh kodenya
dapat diperluas dan diatur agar sesuai dengan kebutuhan
setiap individu.

<div class="revision">$Id: extension.overview.txt 2739 2010-12-14 01:50:04Z weizhuo $</div>
