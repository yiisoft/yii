Skrip Entri
============

Skrip entri adalah skrip bootstrap PHP yang bagian awal menangani permintaan pengguna.
 Skrip ini adalah satu-satunya skrip PHP yang secara langsung dapat diminta pengguna
untuk dijalankan.

Dalam banyak kasus, skrip entri aplikasi Yii berisi kode yang sesederhana
seperti ini,

~~~
[php]
// hapus baris berikut saat dalam mode produksi
defined('YII_DEBUG') or define('YII_DEBUG',true);
// sertakan file bootstrap Yii
require_once('path/to/yii/framework/yii.php');
// buat turunan aplikasi dan jalankan
$configFile='path/to/config/file.php';
Yii::createWebApplication($configFile)->run();
~~~

Pertama-tama skrip menyertakan file boostrap Yii framework, `yii.php`. Selanjutnya
membuat instance aplikasi Web dengan konfigurasi yang sudah ditetapkan
dan menjalankannya.

Mode Debug
----------

Aplikasi Yii dapat berjalan baik dalam mode debug(awakutu) ataupun dalam mode produksi berdasarkan
pada nilai konstan `YII_DEBUG`. Secara default, nilai konstan ini didefinisikan
sebagai `false`, yang artinya sebagai mode produksi. Untuk dijalankan dalam mode debug, definisikan
konstan ini menjadi `true` sebelum menyertakan file `yii.php`. Menjalankan aplikasi
dalam mode debug kurang efisien karena aplikasi akan menyimpan banyak log internal. Namun di sisi
lain, mode debug lebih membantu selama tahap pengembangan karena menyediakan
banyak informasi debug saat terjadi kesalahan.

<div class="revision">$Id: basics.entry.txt 3251 2011-06-01 00:24:06Z qiang.xue $</div>
