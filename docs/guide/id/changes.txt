Fitur Baru
==========

Halaman ini meringkas fitur-fitur utama baru yang diperkenalkan dalam setiap rilis Yii.

Version 1.1.8
-------------
 * [Menambah dukungan kelas aturan URL buatan sendiri](/doc/guide/topics.url#using-custom-url-rule-classes)

Versi 1.1.7
-------------
 * [Menambah dukungan URL RESTful URL ](/doc/guide/topics.url#user-friendly-urls)
 * [Menambah dukungan caching query](/doc/guide/caching.data#query-caching)
 * [Sekarang sudah bisa passing parameter untuk relational named scopes](/doc/guide/database.arr#relational-query-with-named-scopes)
 * [Menambah kemampuan melakukan query Relational tanpa mengambil model bersangkutan](/doc/guide/database.arr#performing-relational-query-without-getting-related-models)
 * [Menambah dukungan HAS_MANY through dan HAS_ONE through pada relasi AR](/doc/guide/database.arr#relational-query-with-through)
 * [Menambah dukungan transaksi pada fitur migrasi DB](/doc/guide/database.migration#transactional-migrations)
 * [Menambah dukungan penggunaan parameter binding dengan action berjenis class](/doc/guide/basics.controller#action-parameter-binding)
 * Menambah dukungan untuk melakukan validasi data pada sisi client secara seamless dengan menggunakan [CActiveForm]

Versi 1.1.6
-------------
 * [Menambah query builder](/doc/guide/database.query-builder)
 * [Menambah migrasi database ](/doc/guide/database.migration)
 * [Praktek MVC Terbaik](/doc/guide/basics.best-practices)
 * [Menambah dukungan penggunaan parameter anonim dan opsi global dalam perintah console](/doc/guide/topics.console)

Versi 1.1.5
-------------

 * [Menambah dukungan untuk command action dan parameter binding](/doc/guide/topics.console)
 * [Menambah dukungan untuk kelas autoloading namespaced](/doc/guide/basics.namespace)
 * [Menambah dukungan untuk penemaan (theming) view pada widget](/doc/guide/topics.theming#theming-widget-views)

Versi 1.1.4
-----------

* [Menambah dukungan pengikatan parameter aksi otomatis (automatic action parameter binding)](/doc/guide/basics.controller#action-parameter-binding).

Versi 1.1.3
-----------

* [Menambah dukungan untuk mengatur nilai default widget di dalam konfigurasi aplikasi](/doc/guide/topics.theming#customizing-widgets-globally).

Versi 1.1.2
-------------

* [Menambah sebuah alat penghasil code yang berbasis Web yang disebut sebagai Gii](/doc/guide/topics.gii)

Versi 1.1.1
-------------

* Menambah CActiveForm untuk mempermudah penulisan kode yang berhubungan dengan form
serta mendukung validasi yang konsisten dan seamless baik pada bagian klien maupun server.

* Refaktor ulang kode yang dihasilkan oleh yiic. Khususnya, aplikasi
kerangka sekarang diciptakan dengan beberapa susunan (multiple layout); menu operasi 
disusun ulang untuk halaman-halaman CRUD; menambah pencarian dan penyaringan pada halaman admin yang
dihasilkan oleh perinta crud; menggunakan CActiveForm untuk menghasilkan sebuah form;

* [Menambah dukungan untuk memungkinkan mendefinisikan perintah yiic global](/doc/guide/topics.console)

Versi 1.1.0
-----------

* [Menambah dukungan untuk penuliasn unit dan tes fungsional](/doc/guide/test.overview).

* [Menambah dukungan penggunaan skin widget](/doc/guide/topics.theming#skin).

* [Menambah pembangun form(form builder) yang dapat diperluas](/doc/guide/form.builder).

* Memperbaiki cara deklarasi atribut model safe. Lihat
 [Mengamankan Pengisian Atribut](/doc/guide/form.model#securing-attribute-assignments).

* Mengubah algoritma eager loading untuk query rekaman aktif relasional (relational active record) sehingga seluruh tabel disatukan(join) dalam sebuah kalimat SQL.

* Mengubah default alias tabel menjadi nama relasi rekaman aktif (active record relation)

* [Menambah dukungan penggunaan prefiks pada nama tabel](/doc/guide/database.dao#using-table-prefix)

* Menambah seluruh set extension baru yang dikenal dengan [pustaka Zii](http://code.google.com/p/zii/).

* Nama alias untuk tabel utama dalam sebuah query AR ditetapkan sebagai 't'

<div class="revision">$Id: changes.txt 3235 2011-05-24 18:54:01Z qiang.xue $</div>
