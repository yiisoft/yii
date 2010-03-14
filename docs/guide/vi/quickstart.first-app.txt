Tạo Ứng Dụng Yii Đầu Tiên
=========================

Để có những kinh nghiệm đầu tiên về Yii,  trong phần này, chúng tôi hướng dẫn
bạn cách tạo ứng dụng Yii đầu tiên của bạn. Chúng tôi sẽ sử dụng một công cụ đắc
lực `yiic` để tự động tạo ra những đoạn mã cho yêu cầu này. Để cho tiện, chúng
tôi giả xử rằng `YiiRoot` là thư mục mà Yii được cài đặt, và `WebRoot` là thư
mục chứa tài liệu chính của Web server.

Chạy lệnh `yiic` trong command line như sau:

~~~
% YiiRoot/framework/yiic webapp WebRoot/testdrive
~~~

> Note|Chú ý: Khi chạy `yiic` trên hệ điều hành Mac, Linux hoặc Unix, bạn có thể
> phải thay đổi quyền hạn cho file `yiic` để nó có tính năng thực thi.
> Cách khác, bạn có thể chạy công cụ như sau,
>
> ~~~
> % cd WebRoot/testdrive
> % php YiiRoot/framework/yiic.php webapp WebRoot/testdrive
> ~~~

Nó sẽ tạo ra "bộ xương" của ứng dụng Yii trong thư mục
`WebRoot/testdrive`. Cấu trúc thư mục của ứng dụng này là cần thiết cho hầu hất
các ứng dụng Yii.

Không cần phải viết đoạn code nào, bạn vẫn có thể chạy test thử ứng dụng này
bằng cách truy cập vào đường dẫn này trên trình duyệt:

~~~
http://hostname/testdrive/index.php
~~~

Như chúng ta thấy, ứng dụng có tất cả 4 trang: trang home, trang about,
trang contact va trang login. Trang contact hiển thị form liên hệ để người dùng
điền thông tin để liên hệ với người quản trị web, và trang login page cho phép
người dùng xác thực (đăng nhập) trước khi truy cập đến các nội dung mà khách
không được xem. Xem các hình dưới đây để nắm nhiều thông tin hơn.

![Trang Home](first-app1.png)

![Trang Contact](first-app2.png)

![Trang Contact với các thông báo lỗi](first-app3.png)

![Trang Contact khi thành công](first-app4.png)

![Trang Login](first-app5.png)


Sơ đổ bên dước thể hiện cấu trúc thư mục của ứng dụng.
Hãy xem các [quy định](/doc/guide/basics.convention#directory) để biết được củ
thể cấu trúc thư mục này.

~~~
testdrive/
   index.php                 file script truy cập đến ứng dụng web
   index-test.php            file script truy cập các chức năng kiểm định
   assets/                   chứa các file nguồn được publish
   css/                      chứa các file CSS
   images/                   chứa các file hình ảnh
   themes/                   chứa các theme của ứng dụng
   protected/                chứa các file bảo vệ của ứng dụng
      yiic                   yiic command line script cho Unix/Linux
      yiic.bat               yiic command line script cho Windows
      yiic.php               yiic command line PHP script
      commands/              chứa các lệnh 'yiic' được thiết lập
         shell/              chứa các lệnh 'yiic shell' được thiết lập
      components/            chứa các component có thể sử dụng lại
         Controller.php      lớp cơ bản cho các controller
         Identity.php        lớp 'Identity' dùng cho việc xác thực
      config/                chứa các file cấu hình
         console.php         the console application configuration
         main.php            the Web application configuration
         test.php            the configuration for the functional tests
      controllers/           containing controller class files
         SiteController.php  the default controller class
      data/                  containing the sample database
         schema.mysql.sql    the DB schema for the sample MySQL database
         schema.sqlite.sql   the DB schema for the sample SQLite database
         testdrive.db        the sample SQLite database file
      extensions/            containing third-party extensions
      messages/              containing translated messages
      models/                containing model class files
         LoginForm.php       the form model for 'login' action
         ContactForm.php     the form model for 'contact' action
      runtime/               containing temporarily generated files
      tests/                 containing test scripts
      views/                 containing controller view and layout files
         layouts/            containing layout view files
            main.php         the base layout shared by all pages
            column1.php      the layout for pages using a single column
            column2.php      the layout for pages using two columns
         site/               containing view files for the 'site' controller
         	pages/           containing "static" pages
         	   about.php    the view for the "about" page
            contact.php      the view for 'contact' action
            error.php        the view for 'error' action (displaying external errors)
            index.php        the view for 'index' action
            login.php        the view for 'login' action
~~~

Kết nối cơ sở dữ liệu
---------------------

Hầu hết các ứng dụng web đều có cơ sở dữ liệu đằng sau nó. Và ứng dụng của chúng
tôi cũng không ngoại lệ. Để sử dụng cơ sở dữ liệu, chúng ta cần phải khai báo cho
ứng dụng biết làm sao để kết nối. Việc này có thể được thực hiện trong file cấu hình
của ứng dụng `WebRoot/testdrive/protected/config/main.php`, cụ thể như sau,

~~~
[php]
return array(
	......
	'components'=>array(
		......
		'db'=>array(
			'connectionString'=>'sqlite:protected/data/testdrive.db',
		),
	),
	......
);
~~~

The above code instructs Yii that the application should connect to the SQLite database
`WebRoot/testdrive/protected/data/testdrive.db` when needed. Note that the SQLite database
is already included in the skeleton application that we just generated. The database
contains only a single table named `tbl_user`:

~~~
[sql]
CREATE TABLE tbl_user (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	username VARCHAR(128) NOT NULL,
	password VARCHAR(128) NOT NULL,
	email VARCHAR(128) NOT NULL
);
~~~

If you want to try a MySQL database instead, you may use the included MySQL
schema file `WebRoot/testdrive/protected/data/schema.mysql.sql` to create the database.

> Note: To use Yii's database feature, we need to enable PHP PDO extension
and the driver-specific PDO extension. For the test-drive application, we
need to turn on both the `php_pdo` and `php_pdo_sqlite` extensions.


Implementing CRUD Operations
----------------------------

Now is the fun part. We would like to implement the CRUD (create, read,
update and delete) operations for the `User` table we just created. This is
also commonly needed in practical applications. Instead of taking trouble
to write actual code, we would use the powerful `yiic` tool again
to automatically generate the code for us. This process is also known as *scaffolding*.

Open a command line window, and execute the commands listed as follows,

~~~
% cd WebRoot/testdrive
% protected/yiic shell
Yii Interactive Tool v1.1
Please type 'help' for help. Type 'exit' to quit.
>> model User tbl_user
   generate models/User.php
   generate fixtures/tbl_user.php
   generate unit/UserTest.php

The following model classes are successfully generated:
    User

If you have a 'db' database connection, you can test these models now with:
    $model=User::model()->find();
    print_r($model);

>> crud User
   generate UserController.php
   generate UserTest.php
   mkdir D:/testdrive/protected/views/user
   generate create.php
   generate update.php
   generate index.php
   generate view.php
   generate admin.php
   generate _form.php
   generate _view.php

Crud 'user' has been successfully created. You may access it via:
http://hostname/path/to/index.php?r=user
~~~

In the above, we use the `yiic shell` command to interact with our
skeleton application. At the prompt, we execute two sub-commands: `model User tbl_user`
and `crud User`. The former generates a model class named `User` for the `tbl_user` table,
while the latter analyzes the `User` model and generates the code implementing
the corresponding CRUD operations.

> Note: You may encounter errors like "...could not find driver", even
> though the requirement checker shows you have already enabled PDO
> and the corresponding PDO driver. If this happens, you may try
> to run the `yiic` tool as follows,
>
> ~~~
> % php -c path/to/php.ini protected/yiic.php shell
> ~~~
>
> where `path/to/php.ini` represents the correct PHP ini file.

Let's enjoy our work by browsing the following URL:

~~~
http://hostname/testdrive/index.php?r=user
~~~

This will display a list of user entries in the `tbl_user` table.

Click the `Create User` button on the page. We will be brought to the login
page if we have not logged in before. After logged in, we see
an input form that allows us to add a new user entry. Complete the form and
click on the `Create` button. If there is any input error, a nice error
prompt will show up which prevents us from saving the input. Back to the
user list page, we should see the newly added user appearing in the list.

Repeat the above steps to add more users. Notice that user list page
will automatically paginate the user entries if there are too many to be
displayed in one page.

If we login as an administrator using `admin/admin`, we can view the user
admin page with the following URL:

~~~
http://hostname/testdrive/index.php?r=user/admin
~~~

This will show us the user entries in a nice tabular format. We can click on the table
header cells to sort the corresponding columns. We can click on the buttons
on each row of data to view, update or delete the corresponding row of data.
We can browse different pages. We can also filter and search to look for
the data we are interested in.

All these nice features come without requiring us to write a single line
of code!

![User admin page](first-app6.png)

![Create new user page](first-app7.png)



<div class="revision">$Id: quickstart.first-app.txt 1733 2010-01-21 16:54:29Z qiang.xue $</div>