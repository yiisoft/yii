בדיקת יחידות
============

מאחר ומערכת הבדיקות של Yii בנויה על גבי [PHPUnit](http://www.phpunit.de/), מומלץ לעבור על [דוקומנטציה](http://www.phpunit.de/wiki/Documentation) של PHPUnit קודם בכדי לקבל מושג והבנה ראשונית בנוגע לכתיבת בדיקות. אנו מסכמים את העקרונות הבסיסיים אודות כתיבת בדיקות ב Yii.

* בדיקת יחידה נכתבת במונחים של מחלקה `XyzTest` היורשת ממחלקת הבסיס [CTestCase] או [CDbTestCase]. כש `Xyz` הינה המחלקה שאותה בודקים. לדוגמא, בכדי לבדוק את המחלקה `Post`, אנו נקרא ליחידת הבדיקה כ `PostTest` לפי המוסכמות. המחלקה הבסיסית [CTestCase] נועדה לבדיקות יחידה כלליות, והמחלקה הבסיסית [CDbTestCase] תואמת לבדיקת מחלקות [AR](/doc/guide/database.ar). בגלל ש `PHPUnit_Framework_TestCase` הינה מחלקת אב לשני המחלקות, אנו יכולים להשתמש בכל המתודות של מחלקה זו.

* מחלקת בדיקת היחידה נשמרת בקובץ PHP בשם `XyzTest.php`. לפי המוסכמות, ניתן לשמור את קובץ הבדיקה תחת התיקיה `protected/tests/unit`.

* מחלקת הבדיקה מכילה בעיקר סט של מתודות בשם `testAbc`, כש `Abc` בדרך כלל הינו שם מתודת המחלקה לבדיקה.

* מתודת בדיקה בדרך כלל מכילה רצף של ביטויי בדיקות (לדוגמא `assertTrue`, `assertEquals`) המשרתות כנקודות ביקורת לבדיקת התנהגות של המחלקה.

בחלק הבא, אנו בעיקר נסביר כיצד לכתוב בדיקות יחידה עבור מחלקות [AR](/doc/guide/database.ar). אנו נרחיב את המחלקות שלנו ממחלקת הבסיס [CDbTestCase] מאחר והיא מספקת תמיכה בקיבוע מסדי הנתונים אשר הצגנו בחלק הקודם.

נניח שאנו רוצים לבדוק את מחלקת המודל `Comment` [בדמו של הבלוג](http://www.yiiframework.com/demos/blog/). אנו מתחילים על ידי יצירת מחלקה בשם `CommentTest` ושמירתו תחת `protected/tests/unit/CommentTest.php`:

~~~
[php]
class CommentTest extends CDbTestCase
{
    public $fixtures=array(
        'posts'=»'Post',
        'comments'=»'Comment',
    );

    ......
}
~~~

במחלקה, אנו מגדירים את המאפיין `fixtures` למערך הקובע אילו טבלאות קבועות יהיו בשימוש בבדיקה זו. המערך מייצג מיפוי של טבלאות קבועות בבדיקה לבין שמות מחלקות מודלים או שמות טבלאות (לדוגמא מטבלה קבוע `posts` למחלקת מודל `Posts` ). זכור, בעת מיפוי לשמות טבלאות קבועות למטרת הבדיקה, אנו צריכים להגדיר קידומת של נקודותיים (:) לשם הטבלה (לדוגמא `Post:` ) בכדי להבדיל אותו משם מודל המחלקה. ובעת שימוש בשמות של מחלקות מודלים, הטבלאות המדוברות יחשבו כטבלאות בדיקה קבועות. כפי שהסברנו קודם, שמות הטבלאות הקבועות יאופסו למצב ידוע כלשהו בכל פעם שמתודת בדיקה תרוץ.

שמות טבלאות קבועות מאפשרות לנו לגשת למידע הקבוע במתודות בדיקה בדרך מאוד נוחה. הקוד הבא מציג את השימוש האופיני:

~~~
[php]
// החזרת כל השורות בטבלה הקבועה `Comments`
$comments = $this-»comments;
// החזרת השורה ששמה המקוצר הוא `sample1` בטבלה `Post`
$post = $this-»posts['sample1'];
// החזרת אובייקט ה AR המייצג את השורה `sample1`
$post = $this-»posts('sample1');
~~~

» Note|הערה: במידה וטבלה קבועה הוצהרה על ידי שם הטבלה שלה (לדוגמא `'posts'=»':Post'`), אז השימוש השלישי בקוד למעלה הוא אינו תקין מאחר ואין לנו מידע לגבי מחלקת המודל אשר משוייכת לטבלה.

לאחר מכן, אנו כותבים את מתודת ה `testApprove` בכדי לבדוק את המתודה `approve` במחלקת המודל `Comment`. הקוד הוא מאוד ישיר: אנו קודם מוסיפים תגובה שהיא במצב המתנה; לאחר מכן אנו מוודאים שתגובה זו היא במצב המתנה על ידי קבלתה ממסד הנתונים; ולבסוף אנו קוראים למתודת `approve` ומאמתים שהססטוס השתנה בצורה נכונה.

~~~
[php]
public function testApprove()
{
    // הוספת תגובה במצב המתנה
    $comment=new Comment;
    $comment-»setAttributes(array(
        'content'=»'comment 1',
        'status'=»Comment::STATUS_PENDING,
        'createTime'=»time(),
        'author'=»'me',
        'email'=»'me@example.com',
        'postId'=»$this-»posts['sample1']['id'],
    ),false);
    $this-»assertTrue($comment-»save(false));

    // אימות במידה והתגובה באמת במצב המתנה
    $comment=Comment::model()-»findByPk($comment-»id);
    $this-»assertTrue($comment instanceof Comment);
    $this-»assertEquals(Comment::STATUS_PENDING,$comment-»status);

    // קריאה למתודה approve  ובדיקת התגובה שהיא נמצאת במצב פעיל
    $comment-»approve();
    $this-»assertEquals(Comment::STATUS_APPROVED,$comment-»status);
    $comment=Comment::model()-»findByPk($comment-»id);
    $this-»assertEquals(Comment::STATUS_APPROVED,$comment-»status);
}
~~~


«div class="revision"»$Id: test.unit.txt 1745 2010-01-24 14:23:36Z qiang.xue $«/div»