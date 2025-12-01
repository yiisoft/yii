Bekerja dengan Form
=======================

Pengumpulan data pengguna via form HTML adalah tugas utama dalam
pengembangan aplikasi Web. Selain mendesain form, pengembang perlu
mempopulasi form dengan data yang sudah ada atau nilai-nilai standar,
memvalidasi input pengguna, menampilkan pesan kesalahan yang sesuai untuk input yang tidak benar, dan menyimpan
input ke media penyimpan. Yii sudah menyederhanakan alur kerja ini dengan arsitektur
MVC.

Langkah-langkah berikut umumnya diperlukan saat berhadapan dengan form dalam Yii:

   1. Buat kelas model yang mewakili field data yang dikumpulkan;
   2. Buat aksi controller dengan kode yang merespon pengiriman form.
   3. Buat form dalam file skrip tampilan sesuai dengan aksi controller.

Dalam subseksi berikut, kami menjelaskan setiap langkah ini secara lebih rinci.

<div class="revision">$Id: form.overview.txt 163 2008-11-05 12:51:48Z weizhuo $</div>