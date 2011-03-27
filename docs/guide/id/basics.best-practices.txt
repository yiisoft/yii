Praktek MVC Terbaik (Best Practices)
==================

Walaupun Model-View-Controller (MVC) dikenali hampir seluruh Web developer, bagaimana menggunakan MVC secara benar di dalam pengembangan aplikasi nyata masih membingungkan banyak orang. Pemikiran utama dari MVC adalah **code yang dapat dipakai ulang dan pemisahan tanggung jawab**. Pada bagian ini, kita akan melihat bagaimana petunjuk umum mengikuti MVC ketika mengembangkan aplikasi Yii.

Supaya lebih gampang mengikuti petunjuk ini, kita berasumsi sebuah aplikasi Web yang terdiri dari beberapa sub-aplikasi, seperti

* front end: sebuah website yang ditujukan untuk end-user normal;
* back end: sebuah website yang berisi fungsi-fungsi administrasi. Biasanya terbatas untuk staf administrasi.
* console: sebuah aplikasi yang terdiri dari perintah console yang akan dijalankan di window terminal atau sebagai job yang terjadwal guna mendukung keseluruhan aplikasi.
* Web API: menyediakan interface untuk pihak ketiga demi bergabung dengan aplikasi.

Sub-aplikasi bisa diimplementasi dengan [modules](/doc/guide/basics.module), atau sebagai aplikasi Yii yang berbagai beberapa code dengan sub-aplikasi lainnya.


Model
-----

[Models](/doc/guide/basics.model) mewakili struktur data dari aplikasi Web. Model sering dibagikan kepada sub-aplikasi berbeda dari aplikasi Web. Misalnya, sebuah model `LoginForm` mungkin bisa digunakan di bagian front end dan juga back end dari aplikasi; sebuah model `News` juga dapat digunakan oleh perintah console, Web APIs, dan juga front/back end untuk suatu aplikasi. Oleh karena itu, models

* harus mengandung properti untuk mewakili data tertentu;

* harus berisi business logic (misalnya aturan validasi) untuk memastikan data yang diwakilinya memenuhi persyaratan desain;

* boleh berisi kode untuk manipulasi data. Misalnya, sebuah model `SearchForm`, selain merepresentasikan pencarian data input, juga bisa berisi sebuah method `search` untuk melakukan pencarian sesungguhnya.

Kadang-kadang, aturan terakhir ini bisa menyebabkan sebuah model menjadi sangat besar, berisi terlalu banyak code untuk sebuah class. Juga bisa membuat model menjadi sulit untuk diurus jika code mengandung tujuan yang berbeda. Misalnya sebuah model `News` mungkin berisi sebuah method bernama `getLatestNews` yang hanya digunakan di front-end; juga mungkin berisi method bernama `getDeletedNews` yang mungkin hanya digunakan di back end. Sebetulnya ini tidak bermasalah untuk aplikasi kecil atau ukuran medium. Tetapi untuk aplikasi berskala besar, strategi berikut dapat diterapkan untuk membuat model lebih gampang diurus:

* Buat sebuah kelas model `NewsBase` yang hanya berisi code yang dipakai bersama-sama oleh sub-aplikasi yang berbeda (misalnya front end, back end);

* Di setiap sub-aplikasi, menentukan sebuah model `News` dengan menurunkan `NewsBase`. Tempatkan semua code yang spesifik ke dalam model `News` di sub-aplikasi.

Jadi, jika kita ingin menggunakan strategi ini pada contoh di atas, kita harus membuat sebuha model `News` di aplikasi front end yang berisi hanya method `getLatestNews`, dan kita harus menambah model `News` yang lain di aplikasi back end, yang hanya berisi method `getDeletedNews`.

Secara umum, model tidak seharusnya berisi logic yang berhubungan langsung terhadap end user. Lebih jelasnya, models

* tidak boleh menggunakan `$_GET`, `$_POST`, atau variabel sejenis yang berhubungan erat dengan request end-user. Tetapi ingatlah bahwa model boleh digunakan oleh sub-aplikasi yang betul-betul berbeda (seperti unit test, Web API) yang tidak menggunakan variabel untuk mewakili user request. Variabel-variabel yang berhubungan dengan user request seharusnya diurus oleh Controller.

* harus menghindari meng-embed HTML atau code presentasional lainnya. Karena code presentasional beragam tergantung request end user (seperti front end dan back end bisa menampilkan detail berita dalam format yang sangat berbeda), lebih baik diurus oleh view.


View
----

[Views](/doc/guide/basics.view) bertanggung jawab untuk menampilkan model dalam format yang diinginkan oleh end user. Secara umum, view

* harus mengandung code presentasional, seperti HTML, dan code PHP sederhana untuk melintasi, memformat dan me-render data;

* harus menghindari code yang melakukan query DB secara eksplisit. Code seperti ini lebih baik diletakkan di model.

* harus menghindari akses langsung `$_GET`, `$_POST`, atau variabel sejenisnya yang mewakili request end user karena sebetulnya ini adalah tugas controller. View harusnya fokus pada penampilan dan layout dari data yang disediakan oleh controller dan/atau model, tetapi tidak mencoba untuk mengakses variabel request atau database secara langsung.

* boleh mengakses langsung properti dan method dari controller dan model. Namun, harus dilakukan hanya untuk tujuan presentasi.


View dapat didaya ulang dengan beberapa cara:

* Layout: tempat presentasional yang umum (seperti page header, footer) dapat diletakkan di view layout.

* Partial views: gunakan partial view (view yang tidak didekorasikan oleh layout) untuk menggunakan fragmen dari code presentasional. Misalnya kita menggunakan partial view `_form.php` untuk merender form input model yang digunakan oleh halaman pembuatan maupun update.

* Widgets: jika banyak sekali logic yang diperlukan untuk menampilkan suatu partial view, maka partial view ini dapat diubah menjadi sebuah widget yang file kelas paling cocok digunakan untuk menampung logic ini. Untuk widget yang men-generate banyak markup HTML, lebih baik menggunakan file view khusus untuk widget untuk menampung markup.

* Helper classes: di view kita sering memerlukan code snippet untuk melakukan tugas-tugas kecil seperti memformat data atau menghasilkan tag HTML. Daripada meletakkan code ini langsung ke file view, pendekatan lain yang lebih baik adalah meletakkan seluruh code snippet ini ke kelas helper view. Kemudian gunakan kelas helper ini di file view Anda. Yii menyediakan contoh seperti ini. Yii memiliki sebuah kelas helper [CHtml] yang bisa menghasilkan code HTML umum. Kelas helper dapat diletakkan di sebuah [direktori autoloadable](/doc/guide/basics.namespace) sehingga kita bisa menggunakannya tanpa perlu menyertakan kelas secara eksplisit.


Controller
----------

[Controllers](/doc/guide/basics.controller) adalah lem perekat model, view dan komponen-komponen lainnya sehingga menjadi aplikasi yang dapat dijalankan. Controller bertanggung jawab untuk menghadapi langsung dengan request end user. Oleh karenanya, controller

* boleh mengakses `$_GET`, `$_POST` dan variabel PHP lain yang merepresentasikan user request;

* membuat berbagai instance model dan mengatur siklus hidupnya. Misalnya, di sebuah action update pada suatu model, controller bisa membuat instance model terlebih dahulu; kemudian mengisi model dengan input user dari `$_POST`; setelah menyimpan model dengan sukses, controller boleh diarahkan ke browser user ke halaman detail model. Harap ingat bahwa implementasi sesungguhnya penyimpanan model harus ditaruh di model alih-alih di controller.

* harus menghindari menampung statement SQL, yang seharusnya lebih baik disimpan di model.

* harus menghindari HTML apapun atau markup presentasional. Lebih baik simpan ke view.


Di dalam aplikasi MVC yang didesain dengan baik, controller sangatlah kecil, mengandung hanya beberapa lusin baris code; sedangkan model lebih besar, mengandung hampir semua code yang bertanggung jawab atas representasi dan manipulasi data. Ini dikarenakan struktur data dan business logic yang diwakili oleh model merupakan sesuatu yang sangat spesifik pada aplikasi tertentu, dan perlu dikustomisasi secara besar-besaran untuk memenuhi keperluan aplikasi; sedangkan logika controller sering mengikuti pola yang mirip pada berbagai aplikasi sehingga dapat disederhanakan dengan framework atau kelas basis.


<div class="revision">$Id: basics.best-practices.txt 2795 2010-12-31 00:22:33Z alexander.makarow $</div>