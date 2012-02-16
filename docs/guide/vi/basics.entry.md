Mã khởi động
============

Đây là mã PHP xử lý yêu cầu của người dùng từ ban đầu. Đây cũng là mã duy nhất mà 
người dùng có thể truy cập.

Trong hầu hết các trường hợp, mã khởi động của một ứng dụng Yii chỉ đơn giản như sau,

~~~
[php]
// bỏ dòng này nếu ứng dụng được sử dụng trong thực tế
defined('YII_DEBUG') or define('YII_DEBUG',true);
// đọc file khởi động của Yii
require_once('path/to/yii/framework/yii.php');
// tạo ứng dụng và thực thi
$configFile='path/to/config/file.php';
Yii::createWebApplication($configFile)->run();
~~~

Mã này đầu tiên sẽ đọc file khởi động hệ thống Yii, là file `yii.php`. Tiếp theo
nó tạo ra một thực thể ứng dụng web với các thông số ban đầu như chỉ định và thực thi nó.

Chế độ gỡ lỗi (Debug)
----------

Một ứng dụng Yii có thể chạy ở chế độ debug hoặc chế độ sản phẩm hoàn thành tùy
vào giá trị của biến `YII_DEBUG`. By default, this constant value is defined
as `false`, meaning production mode. To run in debug mode, define this
constant as `true` before including the `yii.php` file. Running application
in debug mode is less efficient because it keeps many internal logs. On the
other hand, debug mode is also more helpful during development stage
because it provides richer debugging information when error occurs.

<div class="revision">$Id: basics.entry.txt 1622 2009-12-26 20:56:05Z weizhuo $</div>