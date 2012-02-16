בדיקות פונקציונליות
==================

לפני קריאה של חלק זה, מומלץ לקרוא קודם את הדוקומנטציה של [Selenium](http://seleniumhq.org/docs/) ושל [PHPUnit](http://www.phpunit.de/wiki/Documentation). אנו מסכמים בחלק זה את העקרונות הבסיסים לכתיבת בדיקות פונקציונליות ב Yii:

* בדומה לבדיקות יחידה, בדיקה פונקציונלית נכתבת במונחים של מחלקה `XyzTest` היורשת מהמחלקה [CWebTestCase], כש `Xyz` מתייחס למחלקה הנבדקת. בגלל שהמחלקה `PHPUnit_Extensions_SeleniumTestCase` הינה מחלקת אב למחלקה [CWebTestCase], אנו יכולים להשתמש בכל המתודות הנמצאות בה.

* מחלקת הבדיקה הפונקציונלית נשמרת בקובץ PHP בשם `XyzTest.php`. על פי המוסכמות, הקובץ נשמר תחת `protected/tests/functional`.

* מחלקת הבדיקות בעיקר מכילה סט של מתודות לבדיקה בשם `testAbc`, כש `Abc` הוא בדרך כלל שם האפשרות שנבדקת. לדוגמא, בכדי לבדוק את אפשרות התחברות המשתמשים אנו יכולים לקרוא למתודה בשם `testLogin`.

* מתודת בדיקה בדרך כלל מכילה רצף של ביטויים אשר ישלחו פקודות עבור Selenium בכדי לתקשר מול אפליקצית הווב הנבדקת. בנוסף היא מכילה בדיקות לוודא שהאפליקציה מגיבה כמו שצריך.

לפני שנסביר כיצד לכתוב בדיקות פונקציונליות, הבא נבדוק את הקובץ `WebTestCase.php` שנוצר על ידי פקודת הכלי `yiic webapp`. קובץ זה מגדיר את המחלקה `WebTestCase` שיכול לשרת כמחלקת בסיס לכל מחלקות הבדיקה הפונקציונליות.

~~~
[php]
define('TEST_BASE_URL','http://localhost/yii/demos/blog/index-test.php/');

class WebTestCase extends CWebTestCase
{
    /**
     * Sets up before each test method runs.
     * This mainly sets the base URL for the test application.
     */
    protected function setUp()
    {
        parent::setUp();
        $this-»setBrowserUrl(TEST_BASE_URL);
    }

    ......
}
~~~

המחלקה `WebTestCase` בעיקר מגדירה את הקישורים של העמודים אותם צריך לבדוק. לאחר מכן במתודות בדיקה, אנחנו יכולים להשתמש בקישורים רלטיבים בכדי להגדיר אילו עמודים לבדוק.

כמו כן אנחנו צריכים לשים לב שבקישור הבדיקה הבסיסי, אנחנו משתמשים ב `index-test.php` כקובץ הכניסה הראשי במקום `index.php`. ההבדל היחידי בין `index-test.php` ו `index.php` הוא שהקודם משתמש בקובץ הגדרות `test.php` והשני משתמש בקובץ הגדרות `main.php`.

כעת אנו נסביר כיצד לבדוק את האפשרות של תצוגה הודעה [בבלוג](http://www.yiiframework.com/demos/blog). קודם אנו כותבים את מחלקת הבדיקה בצורה הבאה, בידיעה שמחלקת הבדיקה שאנו כותבים יורשת ממחלקת הבסיס שכרגע הסברנו עליה:

~~~
[php]
class PostTest extends WebTestCase
{
    public $fixtures=array(
        'posts'=»'Post',
    );

    public function testShow()
    {
        $this-»open('post/1');
        // וודא שכותרת קיימת להודעה
        $this-»assertTextPresent($this-»posts['sample1']['title']);
        // וודא שקיים טופס לתגובה
        $this-»assertTextPresent('Leave a Comment');
    }

    ......
}
~~~

בדומה לכתיבת מחלקה לבדיקת יחידה, אנו מגדירים את הטבלאות הקבועות שאנו משתמשים בבדיקה זו. כאן אנו מצביעים שהטבלה הקבועה `Post` צריכה להיות בשימוש. במתודת הבדיקה `testShow`, אנו קודם אומרים ל Selenium-RC לפתוח את הקישור `post/1`. זהו קישור רלטיבי, והקישור המלא נוצר על ידי איחוד הקישור הזה לקישור הבסיס שהגדרנו במחלקת הבסיס (זאת אומרת `http://localhost/yii/demos/blog/index-test.php/post/1`). לאחר מכן אנו מאמתים שאנו יכולים למצוא את הכותרת של ההודעה `sample1` בעמוד הנוכחי. ואנו מוודאים שהעמוד מכיל את הטקסט `Leave a Comment`.

» Tip|טיפ: לפני הרצת בדיקות פונקציונליות, יש להפעיל את שרת Selenium-RC. ניתן לבצע זאת על ידי הרצת הפקודה `java -jar selenium-server.jar` תחת התיקיה בה מותקן שרת ה Selenium-RC.

«div class="revision"»$Id: test.functional.txt 1662 2010-01-04 19:15:10Z qiang.xue $«/div»