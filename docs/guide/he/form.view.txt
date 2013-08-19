יצירת טופס
=============

כתיבת קובץ התצוגה של ההתחברות הוא מאוד ברור וישיר. אנו מתחילים עם תג של פתיחת טופס `form` שהמאפיין `action` שלו צריך להיות הקישור של הפעולה של ההתחברות `login` שתוארה קודם לכן. לאחר מכן אנו מוסיפים תויות ושדות טקסט למאפיינים המוגדרים במחלקת המודל `LoginForm`. לבסוף אנו מוסיפים כפתור שליחה שניתן ללחיצה על ידי משתמשים בכדי לשלוח את הטופס. כל אלו ניתנים לביצוע על ידי קוד HTML פשוט.

Yii מספקת כמה מחלקות המסייעות לפשט את הרכבת התצוגה. לדוגמא, בכדי ליצור שדה טקסט, אנו יכולים לקרוא ל [()CHtml::textField]; בכדי ליצור שדה תיבת בחירה, אנו יכולים לקרוא ל [()CHtml::dropDownList].

» Info|מידע: יש התוהים מה היתרון בשימוש במתודות אלו המסייעות לכתיבת התצוגה אם הם דורשות את אותו כמות קוד בהשוואה לכתיבת HTML טהור. התשובה לכך היא שהמתודות הללו מספקות יותר מסתם קוד HTML.
לדוגמא, הקוד הבא יוצר שדה טקסט אשר יכול לגרום לשליחת הטופס אם הערך שלו השתנה על ידי המשתמש.
» ~~~
» [php]
» CHtml::textField($name,$value,array('submit'=»''));
» ~~~
» במקרה אחר זה היה דורש לכתוב קוד JS בכל מקום בדף.

בחלק הבא, אנו משתמשים ב בכדי ליצור טופס התחברות. אנו מניחים שהמשתנה `model$` מייצג אובייקט של המחלקה `LoginForm`.

~~~
[php]
«div class="form"»
«?php echo CHtml::beginForm(); ?»

    «?php echo CHtml::errorSummary($model); ?»

    «div class="row"»
        «?php echo CHtml::activeLabel($model,'username'); ?»
        «?php echo CHtml::activeTextField($model,'username') ?»
    «/div»

    «div class="row"»
        «?php echo CHtml::activeLabel($model,'password'); ?»
        «?php echo CHtml::activePasswordField($model,'password') ?»
    «/div»

    «div class="row rememberMe"»
        «?php echo CHtml::activeCheckBox($model,'rememberMe'); ?»
        «?php echo CHtml::activeLabel($model,'rememberMe'); ?»
    «/div»

    «div class="row submit"»
        «?php echo CHtml::submitButton('Login'); ?»
    «/div»

«?php echo CHtml::endForm(); ?»
«/div»«!-- form --»
~~~

הקוד למעלה יוצר טופס דינאמי יותר. לדוגמא, [()CHtml::activeLabel] יוצר תוית המקושרת עם המאפיין בתוך המודל. במידה והמאפיין מכיל שגיאת קלט, מחלקת ה CSS של התוית תשתנה ל `error`, המשנה את מראה התוית עם סגנון עיצוב מתאים. בדומה, [()CHtml::activeTextField] יוצר שדה טקסט למאפיין במודל וגם כן משנה את מחלקת ה CSS של התוית במידה וישנה שגיאת קלט.

במידה ואנחנו משתמשים בקובץ הסגנונות `form.css` המגיע ביחד עם הסקריפט `yiic`, הטופס שנוצר יראה בדומה לדוגמא הבאה:

![עמוד ההתחברות](login1.png)

![עמוד ההתחברות עם שגיאות](login2.png)

החל מגרסא 1.1.1, ישנו וידג'ט חדש בשם [CActiveForm] המפשט את יצירת הטופס. הוידג'ט מסוגל לתמוך באימות נתונים רציף גם בצד הלקוח ובצד השרת. על ידי שימוש ב [CActiveForm], ניתן לכתוב את קוד התצוגה המוצג למעלה בצורה הבאה:

~~~
[php]
«div class="form"»
«?php $form=$this-»beginWidget('CActiveForm'); ?»

    «?php echo $form-»errorSummary($model); ?»

    «div class="row"»
        «?php echo $form-»label($model,'username'); ?»
        «?php echo $form-»textField($model,'username') ?»
    «/div»

    «div class="row"»
        «?php echo $form-»label($model,'password'); ?»
        «?php echo $form-»passwordField($model,'password') ?»
    «/div»

    «div class="row rememberMe"»
        «?php echo $form-»checkBox($model,'rememberMe'); ?»
        «?php echo $form-»label($model,'rememberMe'); ?»
    «/div»

    «div class="row submit"»
        «?php echo CHtml::submitButton('Login'); ?»
    «/div»

«?php $this-»endWidget(); ?»
«/div»«!-- form --»
~~~

«div class="revision"»$Id: form.view.txt 1751 2010-01-25 17:21:31Z qiang.xue $«/div»