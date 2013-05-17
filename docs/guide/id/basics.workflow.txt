Alur Kerja Pengembangan
=======================

Setelah menjelaskan konsep fundamental Yii, kita akan melihat alur kerja
umum untuk pengembangan aplikasi web menggunakan Yii. Diasumsikan
bahwa kita sudah menyelesaikan analisis persyaratan 
dan juga analisis desain aplikasi.

   1. Membuat kerangka struktur direktori. Tool bernama `yiic` yang dijelaskan dalam
[Membuat Aplikasi Pertama Yii](/doc/guide/quickstart.first-app) dapat
dipakai untuk mempercepat langkah ini.

   2. Mengkonfigurasi [aplikasi](/doc/guide/basics.application). Ini dilakukan
dengan memodifikasi file konfigurasi aplikasi. Langkah ini juga
memerlukan penulisan beberapa komponen aplikasi (misalnya komponen pengguna).

   3. Membuat sebuah kelas [model](/doc/guide/basics.model) untuk setiap tipe data
yang diatur. Tool `Gii` yang dijelaskan di
[Membuat Aplikasi Yii Pertama](/doc/guide/quickstart.first-app#implementing-crud-operations)
dan di [Pembuat Code Otomatis]](/doc/guide/topics.gii) dapat digunakan
untuk men-generate code kelas [active record](/doc/guide/database.ar) secara otomatis
untuk setiap tabel database.

   4. Membuat kelas [controller](/doc/guide/basics.controller) untuk setiap jenis permintaan pengguna.
Bagaimana untuk mengklasifikasikan permintaan pengguna tergantung pada
kebutuhan sebenarnya. Secara umum, jika perlu diakses oleh pengguna, kelas model
harus memiliki kelas controller terkait. Piranti `Gii` dapat mengotomatisasi
langkah ini juga.

   5. Mengimplementasikan [aksi](/doc/guide/basics.controller#action) dan
[view](/doc/guide/basics.view) terkait. Di sinilah pekerjaan sebenarnya
yang perlu dilakukan.

   6. Mengkonfigurasi aksi yang diperlukan
[filter](/doc/guide/basics.controller#filter) dalam kelas.

   7. Membuat [tema](/doc/guide/topics.theming) jika fitur tema
diperlukan.

   8. Membuat pesan terjemahan jika
[internasionalisasi](/doc/guide/topics.i18n) diperlukan.

   9. Memilih data dan view yang dapat di-cache dan menerapkan teknik
[caching](/doc/guide/caching.overview) yang sesuai.

   10. Terakhir, [optimasi](/doc/guide/topics.performance) dan deployment.

Untuk setiap langkah di atas, pengujian kasus mungkin perlu dibuat dan
dilakukan test case .

<div class="revision">$Id: basics.workflow.txt 2718 2010-12-07 15:17:04Z qiang.xue $</div>
