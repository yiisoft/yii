Komponen
========

Aplikasi Yii dibangun dari komponen-komponen yang berupa objek yang ditulis ke sebuah
spesifikasi. Sebuah komponen adalah instance dari
[CComponent] atau kelas induknya. Pemakaian komponen meliputi
pengaksesan propertinya dan memunculkan/menangani event-nya. Kelas dasar
[CComponent] menetapkan bagaimana untuk mendefinisikan properti dan event.

Properti Komponen
-----------------

Properti komponen seperti variabel anggota public sebuah objek. Kita dapat
membaca nilainya atau menempatkan sebuah nilai ke dalamnya. Sebagai contoh,

~~~
[php]
$width=$component->textWidth;     // ambil properti textWidth
$component->enableCaching=true;   // setel properti enableCaching
~~~

Untuk mendefinisikan properti komponen, kita cukup mendeklarasikan variabel
anggota public dalam kelas komponen. Cara yang lebih fleksibel adalah dengan
mendefinisikan metode getter (pengambil) dan setter (penyetel) seperti berikut:

~~~
[php]
public function getTextWidth()
{
    return $this->_textWidth;
}

public function setTextWidth($value)
{
    $this->_textWidth=$value;
}
~~~

Kode di atas mendefinisikan properti yang bisa ditulis bernama `textWidth` (nama
bersifat case-sensitive). Ketika membaca properti, `getTextWidth()` dipanggil
dan nilai yang dihasilkannya menjadi nilai properti; Demikian juga pada saat menulis
properti, `setTextWidth()` yang dipanggil. Jika metode penyetel tidak didefinisikan,
properti akan menjadi hanya-baca(read-only) dan menulisinya akan memunculkan sebuah
exception. Menggunakan metode pengambil dan penyetel untuk mendefinisikan sebuah
properti memiliki manfaat bahwa logika tambahan (seperti melakukan validasi, memunculkan event)
dapat dijalankan saat membaca dan menulis properti.

>Note|Catatan: Ada sedikit perbedaan antara properti yang didefinisikan via metode
pengambil/penyetel dan variabel anggota kelas. Nama pengambil/penyetel
tidak bersifat case-sensitive sementara variabel anggota kelas bersifat case-sensitive..

Event Komponen
--------------

Event komponen adalah properti khusus yang mengambil metode (disebut `pengendali event`)
sebagai nilainya. Melampirkan (menempatkan) sebuah metode ke sebuah event akan
menyebabkan metode dipanggil secara otomatis di tempat di mana event
dimunculkan. Oleh karena itu, perilaku komponen bisa diubah dengan cara yang
tidak terduga selama tahap pengembangan komponen.

Event komponen didefinisikan dengan mendefinisikan sebuah metode yang namanya dimulai
dengan `on`. Seperti nama properti yang didefinisikan via metode pengambil/penyetel, nama event tidak
case-sensitive. Kode berikut mendefinisikan sebuah event `onClicked`:

~~~
[php]
public function onClicked($event)
{
	$this->raiseEvent('onClicked', $event);
}
~~~

di mana `$event` adalah instance dari [CEvent] atau anak kelasnya yang menyediakan
parameter event.

Kita dapat melampirkan sebuah metode ke event ini seperti berikut:

~~~
[php]
$component->onClicked=$callback;
~~~

dengan `$callback` merujuk ke PHP callback yang benar. `$callback` bisa berupa fungsi
global atau metode kelas. Jika berupa metode kelas, callback harus dibentuk sebagai
array: `array($object,'methodName')`.

Tanda pengenal event harus seperti berikut:

~~~
[php]
function methodName($event)
{
    ......
}
~~~

dengan `$event` merupakan parameter yang menjelaskan event (berasal dari
panggilan `raiseEvent()`). Parameter `$event` adalah instance dari [CEvent] atau
kelas induk. Pada kondisi minimum, parameter ini berisi informasi mengenai siapa
yang memunculkan event.

Sebuah event handler dapat berupa fungsi anonim yang didukung PHP 5.3 atau ke atas. Misalnya,

~~~
[php]
$component->onClicked=function($event) {
	......
}
~~~

Jika kita memanggil `onClicked()` sekarang, event `onClicked` akan dijalankan (di dalam
`onClicked()`), dan pengendali event terlampir akan dipanggil secara
otomatis.

Sebuah event dapat dilampirkan ke beberapa pengendali. Saat event dijalankan,
pengendali akan dipanggil dengan urutan yang dilampirkan ke event.
Jika sebuah pengendali memutuskan untuk menghindari pemanggilan pengendali berikutnya,
bisa dilakukan dengan menyetel [$event->handled|CEvent::handled] menjadi true.


Behavior Komponen
-----------------

Komponen mendukung pattern [mixin](http://en.wikipedia.org/wiki/Mixin)
dan dapat dilampirkan dengan satu atau beberapa behavior. Sebuah *behavior(perilaku)* adalah objek
yang metodenya bisa 'inherited' (diturunkan) dengan komponen lampirannya dalam arti pengumpulan
fungsionalitas alih-alih spesialisasi (misal, penurunan kelas normal).
Komponen dapat dilampirkan ke beberapa behavior dan selanjutnya melakukan 'multi penurunan(multiple inheritance)'.

Kelas behavior harus mengimplementasikan antar muka(interface) [IBehavior]. Kebanyakan behavior dapat
diturunkan dari kelas basis [CBehavior]. Jika perlu dilampirkan ke sebuah
[model](/doc/guide/basics.model), maka behavior bisa diturunkan dari [CModelBehavior] atau
[CActiveRecordBehavior] yang mengimplementasikan fitur tambahan tertentu untuk model.

Untuk menggunakannya, behavior harus dilampirkan ke sebuah komponen terlebih dahulu dengan memanggil metode
 [attach()|IBehavior::attach]. Kemudian kita memanggil metode behavior melalui komponen:

~~~
[php]
// $name secara unik mengidentifikasi behavior dalam komponen
$behavior->attach($name,$component);
// test() adalah metode $behavior
$component->test();
~~~

Behavior yang dilampirkan dapat diakses seperti layaknya properti komponen.
Sebagai contoh, jika behavior bernama `tree` dilampirkan ke komponen, kita
bisa memperoleh referensi ke obyek perilaku ini menggunakan:

~~~
[php]
$behavior=$component->tree;
// sama dengan kode berikut:
// $behavior=$component->asa('tree');
~~~

Sebuah behavior dapat dinonaktifkan sementara agar metodenya tidak tersedia pada komponen.
Sebagai contoh,

~~~
[php]
$component->disableBehavior($name);
// pernyataan berikut akan memunculkan exception
$component->test();
$component->enableBehavior($name);
// ia bekerja sekarang
$component->test();
~~~

Dimungkinkan bahwa dua behavior dilampirkan ke komponen yang sama yang memiliki nama metode yang sama.
Dalam hal ini, metode behavior pertama yang akan diprioritaskan.

Ketika dipakai bersama dengan [event](#component-event), behavior akan lebih powerful.
Sebuah behavior, bila dilampirkan ke sebuah komponen dapat melampirkan beberapa metodenya ke beberapa event
komponen. Dengan melakukan itu, perilaku mendapat kesempatan untuk mengawasi atau mengubah alur
eksekusi normal komponen.

Properti behavior juga dapat diakses melalui komponen yang dilampirkannya. Properti
-propertinya termasuk variabel member publik dan properti yang didefinisikan melalui getter dan/atau setter behavior.
Misalnya, sebuah behavior dengan nama `xyz` dan behavior dilampirkan ke sebuah komponen `$a`. Kita dapat
menggunakan ekspresi `$a->xyz` untuk mengakses properti behavior.

<div class="revision">$Id: basics.component.txt 2890 2011-01-18 15:58:34Z qiang.xue $</div>