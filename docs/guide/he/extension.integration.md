שימוש בספריות צד שלישי
=========================

Yii בנויה בצורה כזו שניתן להשתמש בספריות צד שלישי בקלות בכדי להרחיב את הפונקציונליות של Yii אף יותר. כשמשתמשים בספריות צד שלישי בפרוייקט, מפתחים בדרך כלל נתקלים בבעיות של שמות מחלקות והוספת קבצים.
מאחר וכל מחלקות הבסיס של Yii מתחילות באות 'C' , הסיכוי שתווצר בעיה עקב שמות מחלקות זהות הוא קטן; ומאחר ו Yii מסתמך על [טעינה אוטומטית בעזרת SPL](http://us3.php.net/manual/en/function.spl-autoload.php) בכדי לבצע את ההוספות של קבצי המחלקות, היא יכולה לשתף פעולה בצורה טובה עם ספריות נוספת אם הם משתמשים באפשרות PHP של טעינה אוטומטית או משתמשים בנתיב הוספת קבצים של PHP בכדי להוסיף קבצי מחלקות.

למטה אנו מציגים דוגמא כיצד להשתמש ברכיב [Zend_Search_Lucene](http://www.zendframework.com/manual/en/zend.search.lucene.html) מהפריימוורק [Zend](http://www.zendframework.com) בתוך אפליקצית Yii.

קודם, אנו מחלצים את כל המחלקות של Zend לתיקיה תחת התיקיה `Protected/vendors`, בהנחה ש `protected` הינה [התיקיה הראשית של האפליקציה](/doc/guide/basics.application#application-base-directory).
יש לוודא שהקובץ `protected/vendors/Zend/Search/Lucene.php` קיים.

שנית, בתחילת קובץ מחלקת קונטרולר, יש להכניס את השורות הבאות:

~~~
[php]
Yii::import('application.vendors.*');
require_once('Zend/Search/Lucene.php');
~~~

הקוד למעלה מצרף את קובץ המחלקה `Lucene.php`. מאחר ואנו משתמשים בנתיב רלטיבי, אנו צריכים לשנות את תיקית הקבצים הראשית של PHP בכדי שניתן יהיה לטעון את הקובץ בצורה נכונה. זה נעשה על ידי קריאה ל `Yii::import` לפני הקריאה ל `require_once`.

לאחר שההתקנה למעלה מוכנה, אנו יכולים להשתמש במחלקה `Lucene` בתוך פעולה בקונטרולר, כמו בדוגמא הבאה:

~~~
[php]
$lucene=new Zend_Search_Lucene($pathOfIndex);
$hits=$lucene-»find(strtolower($keyword));
~~~


«div class="revision"»$Id: extension.integration.txt 1622 2009-12-26 20:56:05Z qiang.xue $«/div»