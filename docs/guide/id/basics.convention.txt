Konvensi
========

Yii mendukung konvensi di atas konfigurasi. Dengan mengikuti konvensi maka seseorang
bisa membuat aplikasi Yii yang canggih tanpa harus menulis dan mengatur
konfigurasi yang rumit. Tentunya, Yii masih dapat dikustomisasi dalam hampir
setiap aspek dengan konfigurasi bila diperlukan.

Di bawah ini dijelaskan konvensi yang direkomendasikan untuk pemrograman Yii.
Demi kenyamanan, kami asumsikan bahwa `WebRoot` adalah direktori di mana 
aplikasi Yii diinstalasi.

URL
---

Secara standar, Yii mengenali URL dengan format berikut:

~~~
http://hostname/index.php?r=ControllerID/ActionID
~~~

Variabel GET `r` merujuk pada
[rute](/doc/guide/basics.controller#route) yang bisa diurai oleh Yii
menjadi controller dan aksi. Jika `ActionID` dihilangkan, controller akan
mengambil aksi default (didefinisikan via [CController::defaultAction]); dan jika
`ControllerID` juga tidak ada (atau variabel `r` tidak ada), aplikasi
akan menggunakan kontoler default (didefinsikan via
[CWebApplication::defaultController]).

Dengan bantuan [CUrlManager], memungkinkan URL dibuat dan dikenal lebih
ramah-SEO, seperti
`http://hostname/ControllerID/ActionID.html`. Fitur ini dicakup secara rinci
dalam [URL Management](/doc/guide/topics.url).

Kode
----

Yii merekomendasikan penamaan variabel, fungsi dan tipe kelas dalam camel case (kata kapital)
yang mengkapitalkan setiap huruf pertama pada nama dan menggabungkannya tanpa spasi.
Semua huruf depan pada nama variabel dan fungsi harus dalam huruf kecil,
untuk membedakannya dari nama kelas (contoh `$basePath`,
`runController()`, `LinkPager`). Untuk variabel anggota kelas private,
direkomendasikan untuk mengawali namanya dengan karakter garis bawah (contoh
`$_actionList`).

Karena namespace tidak didukung oleh PHP sebelum versi 5.3.0, direkomendasikan
kelas dinamai dalam cara yang unik guna menghindari konflik nama dengan 
kelas pihak-ketiga. Untuk alasan ini, semua kelas Yii framework diawali dengan
huruf "C".

Aturan khusus untuk nama kelas controller adalah harus diakhiri dengan kata
`Controller`. Kemudian ID controller didefinisikan sebagai nama kelas dengan
huruf pertamanya dalam huruf kecil dan kata `Controller` dibuang.
Sebagai contoh, kelas `PageController` akan memiliki ID `page`. Aturan ini
membuat aplikasi lebih aman. Ini juga menjadikan URL yang terkait dengan
controller sedikit lebih bersih (contoh `/index.php?r=page/index` daripada
`/index.php?r=PageController/index`).

Konfigurasi
-----------

Konfigurasi adalah sebuah array pasangan kunci-nilai(key-value). Setiap kunci mewakili
nama properti objek yang dikonfigurasi, dan setiap nilai
merupakan nilai awal properti tersebut. Sebagai contoh, `array('name'=>'My
application', 'basePath'=>'./protected')` menginisialisasi properti `name` dan
`basePath` ke nilai array terkait.

Setiap properti objek yang bisa ditulis dapat dikonfigurasi. Jika tidak dikonfigurasi,
properti akan mengambil nilai default. Ketika mengkonfigurasi sebuah properti,
tidak ada salahnya untuk membaca dokumentasi terkait agar nilai awal
dapat diberikan dengan benar.

File
----

Konvensi penamaan dan penggunaan file tergantung pada tipenya.

File Kelas harus dinamai sesuai kelas publik di dalam file tersebut. Sebagai contoh,
kelas [CController] pada file `CController.php`.  Kelas publik maksudnya
adalah kelas yang bisa dipakai oleh kelas lain. Setiap file kelas harus
berisi paling banyak hanya satu kelas publik. Kelas Private (kelas yang hanya dipakai
oleh satu kelas publik) bisa berada dalam file yang sama dengan
kelas publik.

File view (tampilan) harus dinamai berdasarkan nama view. Sebagai contoh, tampilan `index`
ada dalam file `index.php`. File view adalah file skrip PHP yang berisi
kode HTML dan PHP terutama untuk keperluan menampilkan.

File konfigurasi bisa dinamai secara sembarang. File konfigurasi adalah
skrip PHP yang bertujuan untuk mengembalikan sebuah array terkait yang
mewakili konfigurasi.

Direktori
---------

Secara default, Yii mengambil asumsi pada satu set direktori yang dipakai untuk berbagai keperluan.
Masing-masing bisa dikustomisasi jika diperlukan.

   - `WebRoot/protected`: ini adalah [basis direktori 
aplikasi](/doc/guide/basics.application#application-base-directory) yang menampung semua
skrip PHP dan file data yang sensitif . Yii memiliki alias standar
bernama `application` yang dikaitkan dengan path ini. Direktori ini dan
semua yang ada di bawahnya dilindungi dari pengaksesan pengguna Web.
Bisa dikustomisasi via [CWebApplication::basePath].

   - `WebRoot/protected/runtime`: direktori ini menampung file privat sementara
yang dibuat selama menjalankan aplikasi. Direktori ini harus bisa ditulis oleh
server Web. Dapat dikustomisasi melalui
[CApplication::runtimePath].

   - `WebRoot/protected/extensions`: direktori ini menampung semua extension dari
pihak ketiga. Dapat dikustomisasi melalui [CApplication::extensionPath]. Yii memiliki alias default
bernama `ext` untuk mewakilinya.

   - `WebRoot/protected/modules`: direktori ini menampung semua 
[module](/doc/guide/basics.module) aplikasi, masing-masing diwakili oleh subdirektori.

   - `WebRoot/protected/controllers`: direktori ini menampung semua file kelas
controller. Dapat dikustomisasi melalui [CWebApplication::controllerPath].

   - `WebRoot/protected/views`: direktori ini menampung semua file tampilan,
termasuk tampilan controller, tampilan tata letak dan tampilan sistem. Dapat
dikustomisasi melalui [CWebApplication::viewPath].

   - `WebRoot/protected/views/ControllerID`: direktori ini menampung file tampilan
untuk satu kelas controller. Di sini, `ControllerID` merupakan ID
controller. Dapat dikustomisasi melalui [CController::getViewPath].

   - `WebRoot/protected/views/layouts`: direktori ini menampung semua file
tampilan tata letak. Dapat dikustomisasi melalui [CWebApplication::layoutPath].

   - `WebRoot/protected/views/system`: direktori ini menampung semua file tampilan
sistem. Tampilan sistem adalah template yang dipakai dalam menampilkan exception (exception) dan
kesalahan. Dapat dikustomisasi melalui [CWebApplication::systemViewPath].

   - `WebRoot/assets`: direktori ini menampung file aset yang dipublikasikan. File
asset adalah file privat yang dapat diterbitkan agar bisa diakses oleh pengguna
Web. Direktori ini harus bisa ditulis oleh proses server Web. Dapat dikustomisasi
melalui [CAssetManager::basePath].

   - `WebRoot/themes`: direktori ini menampung berbagai tema yang dapat diterapkan
pada aplikasi. Setiap subdirektori mewakili satu tema yang namanya adalah nama
subdirektori. Dapat dikustomisasi melalui
[CThemeManager::basePath].

Basis Data
----------

Kebanyakan aplikasi Web didukung oleh basis data. Kami mengusulkan beberapa cara terbaik berupa konvensi
penamaan tabel dan kolom database. Harap diingat bahwa
konvensi ini tidak wajib dalam Yii.

   - Kolom maupun tabel pada database dinamakan dalam huruf kecil.

   - Kata-kata dalam nama harus dipisahkan dengan garis bawah (misalnya `product_order`).

   - Khusus jika menggunakan nama dalam bahasa Inggris, untuk nama tabel, Anda dapat menggunakan nama
tunggal (singular) atau jamak (plural) tetapi jangan dua-duanya. Supaya sederhana kami menyarankan menggunakan nama dalam bentuk tunggal.

   - Nama tabel bisa diawali dengan prefiks yang umum seperti `tbl_`. Cara ini sangat berguna
terutama jika tabel-tabel suatu aplikasi berbaur dalam satu tabel dengan tabel-tabel aplikasi lain.
Dua kumpulan tabel itu dapat dipisahkan dengan menggunakan nama tabel dengan prefiks yang berbeda.




<div class="revision">$Id: basics.convention.txt 3225 2011-05-17 23:23:05Z alexander.makarow $</div>