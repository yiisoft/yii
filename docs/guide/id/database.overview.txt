Bekerja dengan Database
=======================

Yii menyediakan dukungan berkemampuan untuk pemrograman database.

Dibangun di atas extension PHP Data Objects (PDO), Yii Data Access Objects (DAO) memungkinkan
pengaksesan ke sistem manajemen database (DBMS) yang berbeda dalam satu antar muka
tunggal yang seragam. Aplikasi yang dikembangkan menggunakan Yii DAO dapat dialihkan
dengan mudah ke DBMS berbeda tanpa perlu memodifikasi data
pengaksesan code.

Yii Query Builder menyediakan sebuah method berorientasi objek untuk membuat
query SQL, yang bisa mengurangi resiko terserang SQL injection.

Dan Active Record Yii, diimplementasikan sebagai pendekatan
Pemetaan Relasional-Obyek / Object-Relational Mapping (ORM) yang diadopsi secara luas, mempermudah
pemrograman database. Tabel direpresentasikan dalam bentuk kelas dan
baris dalam bentuk instance, Yii AR mengeliminasi tugas berulang pada penulisan SQL statement terutama
yang berkaitan dengan operasi CRUD (create, read, update dan delete).

Meskipun Yii menyertakan fitur-fitur database yang dapat menangani
hampir semua tugas-tugas terkait-database, Anda masih bisa menggunakan pustaka database Anda sendiri
dalam aplikasi Yii Anda. Bahkan, Yii framework didesain secara hati-hati agar
bisa dipakai bersamaan dengan pustaka pihak ketiga lainnya.

<div class="revision">$Id: database.overview.txt 2666 2010-11-17 19:56:48Z qiang.xue $</div>