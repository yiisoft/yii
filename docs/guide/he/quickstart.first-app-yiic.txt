יישום פעולות CRUD בעזרת `yiic shell` (לא נתמך)
==============================================

» Note|הערה: יצירת הקוד בעזרת `yiic shell` יצא מכלל שימוש החל מגרסא 1.1.2. נא להשתמש ביוצר הקוד הנרחב והמבוסס ווב בשם [Gii](/doc/guide/topics.gii), במקום.

יש לפתוח חלון פקודות, להריץ את הפקודות הרשומות מטה:

~~~
% cd WebRoot/testdrive
% protected/yiic shell
Yii Interactive Tool v1.1
Please type 'help' for help. Type 'exit' to quit.
»» model User tbl_user
   generate models/User.php
   generate fixtures/tbl_user.php
   generate unit/UserTest.php

The following model classes are successfully generated:
    User

If you have a 'db' database connection, you can test these models now with:
    $model=User::model()-»find();
    print_r($model);

»» crud User
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

בקוד המוצג למעלה, אנו משתמשים בפקודות `yiic shell` בכדי לתקשר עם האפליקציה שלנו. בחלון, אנו מריצים שני פקודות נוספות: `model User tbl_user` ו `crud User`. הראשון יוצר מחלקת מודל בשם `User` עבור הטבלה `tbl_user`, בזמן שהפקודה השנייה מנתחת את המודל `User` ויוצרת את הקוד המיישם את פעולות ה CRUD השונות.

» Note|הערה: יתכן ותתקל בשגיאות כמו `could not find driver...`, למרות שבעת ביצוע בדיקות הדרישות של מערכת Yii מצויין שה-PDO פעיל. במידה וזה קורה, תוכל לנסות להריץ את הכלי `yiic` בצורה הבאה,
» ~~~
» % php -c path/to/php.ini protected/yiic.php shell
» ~~~
»
» כש `path/to/php.ini` מייצג את קובץ הגדרות ה-PHP הנכון.

כעת נוכל לראות את העבודה בפעולה שהרגע יצרנו:

~~~
http://hostname/testdrive/index.php?r=user
~~~

זה יציג רשימה של רשומות מטבלת `tbl_user`.

לחץ על כפתור `Create User` בעמוד. אנו נגיע לעמוד ההתחברות אם עדיין לא התחברנו. לאחר ההתחברות, אנו נראה טופס המאפשר לנו להוסיף משתמש חדש. יש להשלים את הטופס וללחוץ על כפתור `Create`. במידה וישנם שגיאות בשדות, תופיע שגיאה שתמנע מאתנו לשלוח את הטופס. בחזרה לעמוד רשימת המשתמשים, כעת אנו נוכל לראות את המשתמש שהרגע הוספנו מופיע ברשימה.

ניתן לחזור על הפעולות למעלה בכדי להוסיף משתמשים נוספים. שים לב שעמוד תצוגת המשתמשים יציג עמודים באופן אוטומטי אם ישנם יותר מדי משתמשים לתצוגה בעמוד אחד.

אם אנו נתחבר כמנהלים ראשיים בעזרת הפרטים `admin/admin`, אנו נוכל לצפות בעמוד ניהול המשתמשים בקישור הבא:

~~~
http://hostname/testdrive/index.php?r=user/admin
~~~

עמוד זה יציג לנו את רשימת המשתמשים בתצוגה טבלאית. אנו יכולים ללחוץ על אחד מהכותרות בטבלה בכדי למיין את הטבלה על פי אותה כותרת שלחצנו הרגע. אנו יכולים ללחוץ על כל אחד מהמידע בשורות בכדי לצפות, לעדכן או למחוק את המידע בשורה הנ"ל. אנו יכולים לדפדף בין עמודים. כמו כן, אנו יכולים לסנן ולחפש אחר המידע אותו אנו רוצים לראות.

כל האפשרויות הללו מגיעות ללא שום צורך בכתיבת שורת קוד אחת.

![עמוד ניהול משתמשים](first-app6.png)

![עמוד הוספת משתמש חדש](first-app7.png)


«div class="revision"»$Id: quickstart.first-app-yiic.txt 2098 2010-04-11 03:54:25Z qiang.xue $«/div»