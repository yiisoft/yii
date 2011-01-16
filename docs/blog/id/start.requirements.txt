Analisis Kebutuhan
=====================

Sistem blog yang akan kita buatkan merupakan sistem pengguna tunggal (single user system). Pemilik sistem akan melakukan beberapa aksi:

 * Login dan logout
 * Buat, ubah dan hapus post
 * Publikasi, tarik kembali, serta mengarsip post
 * Menyetujui dan menghapus komentar

Sisa penguna lain adalah pengguna tamu yang dapat melakukan aksi-aksi:

 * Membaca post
 * Membuat komentar

Tambahan keperluan untuk sistem ini yaitu:

 * Halaman utama dari sistem harus menampilkan daftar post terbaru.
 * Jika sebuah halaman berisi lebih dari 10 post, maka harus ditampilkan dalam beberapa halaman.
 * Sistem harus menampilkan serangkaian post dengan tag tertentu.
 * Sistem harus memperlihatkan kumpulan tag (cloud of tags) yang mengindikasikan frekuensi penggunaan.
 * Sistem harus menampilkan komentar terbaru
 * Sistem harus dapat ditemakan (themeable).
 * Sistem harus menggunakan URL ramah SEO.

<div class="revision">$Id: start.requirements.txt 683 2009-02-16 05:20:17Z qiang.xue $</div>