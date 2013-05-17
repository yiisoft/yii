Konten Dinamis(Dynamic Content)
======================================

Saat menggunakan [caching fragmen](/doc/guide/caching.fragment) atau [caching
halaman](/doc/guide/caching.page), kita sering mengalami situasi di mana
seluruh bagian output relatif statis kecuali pada satu atau beberapa 
tempat. Sebagai contoh, halaman bantuan mungkin menampilkan halaman statis
informasi bantuan dengan nama pengguna yang saat ini masuk ditampilkan di 
atas.

Untuk memecahkan masalah ini, kita dapat memvariasikan konten cache berdasarkan
pada nama pengguna, tetapi cara ini membuang ruang cache yang berharga karena
kebanyakan isinya sama kecuali nama pengguna. Kita juga bisa membagi halaman
ke dalam beberapa fragmen dan menembolokannya secara individual, tapi ini mempersulit tampilan kita dan menjadikan kode kita sangat kompleks. Pendekatan
yang lebih baik adalah penggunaan fitur *konten dinamis* yang disediakan oleh [CController].

Konten dinamis berarti sebuah fragmen output yang tidak harus di-cache
meskipun disertakan di dalam fragmen cache. Untuk membuat konten selalu dinamis,
harus dibuat setiap kali meskipun penyertaan konten
sedang dilayani dari cache. Dikarenakan alasan ini, kita memerlukan konten dinamis yang
dibuat oleh beberapa metode atau fungsi.

Kita memanggil [CController::renderDynamic()] untuk menyisipkan konten dinamis 
di tempat yang diinginkan.

~~~
[php]
...konten HTML lain...
<?php if($this->beginCache($id)) { ?>
...konten fragmen yang ditembolok...
	<?php $this->renderDynamic($callback); ?>
...konten fragmen yang ditembolok...
<?php $this->endCache(); } ?>
...konten HTML lain...
~~~

Dalam contoh di atas, `$callback` merujuk pada PHP callback yang benar. `$callback` bisa berupa string yang merujuk ke nama metode dalam kelas controller saat ini atau
fungsi global. `$callback` juga bisa berupa array yang merujuk ke metode kelas. Setiap
parameter tambahan pada [renderDynamic()|CController::renderDynamic()]
akan dioper ke callback. Callback harus mengembalikan konten dinamis daripada
menampilkannya.

<div class="revision">$Id: caching.dynamic.txt 163 2008-11-05 12:51:48Z weizhuo $</div>