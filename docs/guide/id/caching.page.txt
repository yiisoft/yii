Penembolokan Halaman (Page Caching)
===================================

Penembolokan halaman merujuk pada caching isi seluruh halaman. Penembolokan halaman bisa
terjadi di berbagai tempat. Misalnya, dengan memilih tajuk halaman(page header) yang
sesuai, penjelajah klien(client browser) mungkin menembolok halaman yang sedang dilihat untuk jangka waktu
tertentu. Aplikasi Web sendiri juga dapat menyimpan isi halaman dalam
cache. Dalam subbab ini, fokus kita pada pendekatan ini.

Penembolokan halaman bisa dipertimbangkan sebagai kasus khusus [caching
fragmen](/doc/guide/caching.fragment). Karena isi halaman sering dihasilkan dari
dari penerapan tata letak(layout) pada sebuah tampilan, caching halaman tidak akan berfungsi jika
kita hanya memanggil [beginCache()|CBaseController::beginCache] dan
[endCache()|CBaseController::endCache] dalam tata letak. Alasannya dikarenakan
tata letak diterapkan dalam metode [CController::render()] SETELAH
tampilan konten dievaluasi.

Untuk menembolok seluruh halaman, kita harus melewatkan eksekusi aksi penghasil
isi halaman. Kita bisa menggunakan [COutputCache] sebagai aksi
[filter](/doc/guide/basics.controller#filter) untuk menyelesaikan tugas ini.
Kode berikut akan memperlihatkan bagaimana kita mengkonfigurasi filter cache:

~~~
[php]
public function filters()
{
	return array(
		array(
			'COutputCache',
			'duration'=>100,
			'varyByParam'=>array('id'),
		),
	);
}
~~~

Konfigurasi filter di atas akan menjadikan filter diterapkan ke semua aksi
dalam controller. Kita dapat membatasinya ke satu atau beberapa aksi hanya
dengan menggunakan operator plus. Lebih jelasnya bisa ditemukan dalam
[filter](/doc/guide/basics.controller#filter).

> Tip: Kita dapat menggunakan [COutputCache] sebagai filter karena kelas tersebut diturunkan
dari [CFilterWidget], yang artinya [COutputCache] dapat berupa widget dan juga filter.
Sebenarnya, cara kerja widget mirip dengan filter: widget (filter) dimulai
sebelum isi yang dilampirkan (aksi) dievaluasi, dan widget (filter)
berakhir setelah isi yang dilampirkan (aksi) dievaluasi.

<div class="revision">$Id: caching.page.txt 1014 2009-05-10 12:25:55Z qiang.xue $</div>