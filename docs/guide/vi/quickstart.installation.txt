Cài đặt
=======

Cài đặt Yii đơn giản chỉ gồm 2 bước:

   1. Download Yii Framework từ [yiiframework.com](http://www.yiiframework.com/).
   2. Giải nén Yii vào một Web folder (folder trên web server có thể truy cập được bởi web server).

> Tip: Yii không nhất thiết phải được giải nén vào Web folder mà Web user có thể
truy cập được. Một ứng dụng Yii có một entry script (thông thường là index.php)
duy nhất là cần thiết phải được cấp quyền truy cập cho Web user. Các PHP file khác,
bao gồm cả mã nguồn của Yii (/framework) có thể được bảo vệ (bằng cách phân quyền)
để ngăn chặn bị harker tấn công.

Yêu cầu hệ thống
----------------

Sau khi cài đặt Yii, bạn có thể muốn kiểm tra xem Web server của bạn có đáp ứng tất
cả các yêu cầu về mặt cấu hình của Yii. Bạn có thể làm vậy bằng cách truy cập vào URL:

~~~
http://hostname/path/to/yii/requirements/index.php
~~~

Yêu cầu tối thiểu của Yii là Web server hỗ trợ PHP 5.1.0 hoặc version mới hơn. Yii đã
được test với  [Apache HTTP
server](http://httpd.apache.org/) trên Windows và Linux.

Yii cũng có thể chạy trên các Web server khác nếu có  hỗ trợ PHP 5.

<div class="revision">$Id: quickstart.installation.txt 359 2008-12-14 19:50:41Z qiang.xue $</div>