מודול
======

» Note|הערה: תמיכה במודולים קיימת מגרסא 1.0.3 ומעלה.

מודול היא יחידה בפני עצמה המורכבת מ - [מודלים](/doc/guide/basics.model), [קבצי תצוגה](/doc/guide/basics.view), [קונטרולרים](/doc/guide/basics.controller) ושאר רכיבים נתמכים. במובנים רבים, מודול דומה ל[אפליקציה](/doc/guide/basics.application). ההבדל היחידי הוא שלא ניתן לפרוס מודול כפי שהוא והוא חייב להיות ממוקם בתוך אפליקציה. ניתן לגשת לקונטרולרים בתוך מודול כפי שניגשים לקונטרולר רגיל באפליקציה.

מודולים יעילים בכמה תסריטים. בעבור אפליקציה גדולה במיוחד, ניתן לחלק אותה למספר מודולים, כל אחד מפותח ומתוחזק בצורה נפרדת. כמה חלקים אשר משתמשים בהם לעיתים קרובות, כמו ניהול משתמשים, ניהול תגובות, יכולים להיות מפותחים בפורמט של מודול כדי שיהיה ניתן להשתמש בהם בקלות בפרוייקטים עתידיים.

יצירת מודול
---------------

מודול מאורגן על פי תיקיה ששמה הוא [המזהה היחודי|CWebModule::id] של המודול. המבנה של התיקיות בתוך מודול הוא בדומה למבנה של [התיקיות באפליקציה](/doc/guide/basics.application#application-base-directory). הדוגמא הבאה מציגה מבנה של תיקיות פשוט של מודול בשם `forum`:

~~~
forum/
   ForumModule.php            קובץ המחלקה של המודול
   components/                מכיל רכיבים אשר ניתן להשתמש בכל מקום במודול
      views/                  מכיל קבצי וידג'טים
   controllers/               מכיל קבצי מחלקות הקונטרולרים
      DefaultController.php   קונטרולר ברירת המחדל
   extensions/                מכיל תוספות צד שלישי
   models/                    מכיל את קבצי המודלים
   views/                     מכיל את קבצי התצוגה
      layouts/                מכיל את תבניות התצוגה
      default/                DefaultController מכיל קבצי תצוגה השייכים ל
         index.php            קובץ תצוגה
~~~

מודול חייב להכיל מחלקה אשר יורשת מהמחלקה [CWebModule]. שם המחלקה נקבע על פי הביטוי `ucfirst($id).'Module'`, איפה ש `id$` מתייחס למזהה היחודי של המודול (או שמה של התיקיה של המודול). מחלקת המודול משרתת כמקום מרכזי לאחסון ושיתוף המידע לאורך כל קוד המודול. לדוגמא, ניתן להשתמש ב [CWebModule::params] כדי לשמור פרמטרים של המודול, ולהשתמש ב [CWebModule::components] כדי לשתף [רכיבי אפליקציה](/doc/guide/basics.application#application-component) ברמת המודול.

» Tip|טיפ: אנו יכולים להשתמש בכלי בשם Gii כדי ליצור מודול עם המבנה הבסיסי של מודול חדש.

שימוש במודול
------------

כדי להשתמש במודול, ראשית יש ליצור תיקיה בשם `modules` תחת [התיקיה הראשית](/doc/guide/basics.application#application-base-directory) של האפליקציה. לאחר מכן יש להגדיר את המזהה היחודי של המודול במאפיין [modules|CWebApplication::modules] של האפליקציה. לדוגמא, כדי להשתמש במודול המוצג למעלה `forum`, ניתן להשתמש [הגדרות אפליקציה](/doc/guide/basics.application#application-configuration) הבאות:

~~~
[php]
return array(
    ......
    'modules'=»array('forum',...),
    ......
);
~~~

ניתן להגדיר מודול עם המאפיינים הראשוניים שלו מוגדרים בצורה שונה. השימוש הוא דומה מאוד [להגדרות רכיב](/doc/guide/basics.application#application-component). לדוגמא, מודול ה `forum` יכול להכיל מאפיין בשם `postPerPage` בתוך מחלקת המודול שלו שניתן להגדירו [בהגדרות האפליקציה](/doc/guide/basics.application#application-configuration) בצורה הבאה:

~~~
[php]
return array(
    ......
    'modules'=»array(
        'forum'=»array(
            'postPerPage'=»20,
        ),
    ),
    ......
);
~~~

ניתן לגשת לאובייקט המודול בעזרת המאפיין [module|CController::module] של הקונטרולר הפעיל כרגע. דרך האובייקט של המודול, ניתן לגשת למידע אשר משותף ברמת המודול. לדוגמא, בכדי לגשת למידע של `postPerPage`, ניתן להשתמש בביטוי:

~~~
[php]
$postPerPage=Yii::app()-»controller-»module-»postPerPage;
// or the following if $this refers to the controller instance
// $postPerPage=$this-»module-»postPerPage;
~~~

ניתן לגשת לפעולה בקונטרולר בתוך מודול על ידי [הניתוב](/doc/guide/basics.controller#route) `moduleID/controllerID/actionID`. לדוגמא, נניח שלמודול `forum` המוצג למעלה ישנו קונטרולר בשם `PostController`, ניתן להשתמש [בניתוב](/doc/guide/basics.controller#route) `forum/post/create`  כדי לנתב את הבקשה אל הפעולה `create` אשר נמצאת בקונטרולר. הקישור המתאים לניתוב זה יהיה `http://www.example.com/index.php?r=forum/post/create`.

» Tip|טיפ: במידה והקונטרולר הוא בתת תיקיה תחת `controllers`, אנו עדיין יכולים להשתמש בפורמט של [הניתוב](/doc/guide/basics.controller#route) המוצג למעלה. לדוגמא, נניח ש `PostController` נמצא תחת `forum/controllers/admin`, ניתן לגשת לפעולה `create` על ידי שימוש ב `forum/admin/post/create`.

שרשור מודולים
-------------

ניתן לשרשר מודולים. זאת אומרת, שמודול יכול להכיל מודול נוסף. אנו קוראים לקודם *מודל אב* (*parent module*) ואת המודול תחתיו *תת מודול* (*child module*). תתי מודולים צריכים להיות ממוקמים בתוך תיקית `modules` של מודול האב. כדי לגשת לפעולה של קונטרולר בתת מודול, יש צורך להשתמש בניתוב `parentModuleID/childModuleID/controllerID/actionID`.


«div class="revision"»$Id: basics.module.txt 2042 2009-02-25 21:45:42Z qiang.xue $«/div»