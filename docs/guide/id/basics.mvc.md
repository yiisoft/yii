Model-View-Controller (MVC)
===========================

Yii mengimplementasikan pola desain model-view-controller (MVC), yang
diadopsi secara luas dalam pemrograman Web. MVC bertujuan untuk memisahkan logika bisnis dari
pertimbangan antar muka pengguna agar para pengembang bisa lebih mudah mengubah
setiap bagian tanpa mempengaruhi yang lain. Dalam MVC, model menggambarkan 
informasi (data) dan aturan bisnis; view(tampilan) berisi elemen
antar muka pengguna seperti teks, input form; sementara controller mengatur
komunikasi antar model dan view.

Selain implementasi MVC, Yii juga memperkenalkan front-controller(controller-depan), yang disebut `Aplikasi`,
yang mengenkapsulasi konteks eksekusi untuk memproses sebuah request. Aplikasi 
mengumpulkan beberapa informasi mengenai request pengguna dan kemudian mengirimnya ke controller yang sesuai
untuk penanganan selanjutnya.

Diagram berikut memperlihatkan struktur statis sebuah aplikasi Yii:

![Struktur statis aplikasi Yii](structure.png)


Alur kerja Umum
---------------
Diagram berikut memperlihatkan alur kerja umum sebuah aplikasi Yii saat
menangani permintaan pengguna:

![Alur kerja umum aplikasi Yii](flow.png)

   1. Pengguna membuat permintaan dengan URL `http://www.example.com/index.php?r=post/show&id=1`
dan server Web menangani permintaan dengan menjalankan skrip bootstrap `index.php`.
   2. Skrip bootstrap membuat sebuah instance [Aplikasi](/doc/guide/basics.application)
dan menjalankannya.
   3. Aplikasi mendapatkan rincian informasi permintaan pengguna dari
[komponen aplikasi](/doc/guide/basics.application#application-component)
bernama `request`.
   4. Aplikasi menentukan [controller](/doc/guide/basics.controller)
dan [aksi](/doc/guide/basics.controller#action) yang diminta dengan bantuan
komponen aplikasi bernama `urlManager`. Dalam contoh ini, controller adalah
`post` yang merujuk pada kelas `PostController`; dan aksi adalah `show`
yang arti sebenarnya ditentukan oleh controller.
   5. Aplikasi membuat instance controller yang diminta
untuk selanjutnya menangani permintaan pengguna. Controller menentukan aksi
`show` merujuk pada sebuah metode bernama `actionShow` dalam kelas controller. Kemudian membuat dan menjalankan filter (contoh kontrol akses, pengukuran) 
terkait dengan aksi ini. Aksi dijalankan jika diijinkan oleh filter.
   6. Aksi membaca `Post` [model](/doc/guide/basics.model) di mana ID adalah `1` dari database.
   7. Aksi meyiapkan [view(tampilan)](/doc/guide/basics.view) bernama `show` dengan model `Post`.
   8. View membaca dan menampilkan atribut model `Post`.
   9. View menjalankan beberapa [widget](/doc/guide/basics.view#widget).
   10. View menyiapkan hasil yang dipasangkan dalam [layout(tata letak)](/doc/guide/basics.view#layout).
   11. Aksi mengakhiri pembuatan view dan menampilkan hasil akhir kepada pengguna.


<div class="revision">$Id: basics.mvc.txt 3321 2011-06-26 12:54:22Z mdomba $</div>
