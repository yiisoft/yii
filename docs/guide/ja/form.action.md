アクションの作成
===============

モデルを持った時点で、モデルを操作する為に必要なロジックを書き始める事が
できます。このロジックはコントローラアクションの中に記述します。
例えばログインフォームの為に必要なコードは下記の様になります:

~~~
[php]
public function actionLogin()
{
	$model=new LoginForm;
	if(isset($_POST['LoginForm']))
	{
		// ユーザの入力データの収集
		$model->attributes=$_POST['LoginForm'];
		// ユーザの入力の検証、検証が通った際は前のページへリダイレクト
		if($model->validate())
			$this->redirect(Yii::app()->user->returnUrl);
	}
	// ログインフォームの表示
	$this->render('login',array('model'=>$model));
}
~~~

上記の様に、はじめに `LoginForm` モデルのインスタンスを作成します; 
もしリクエストがPOSTリクエストであった場合(ログインフォームが送信された事
を意味します)、`$model` の中へ送信されたデータ `$_POST['LoginForm']` 
を入れます。そして入力を検証し、成功した場合はユーザのブラウザを
認証によって必要とされたページの前のページへリダイレクトします。
もし検証が失敗した場合、またはアクションが直接アクセスされた場合は、
次のサブセクションで内容を述べる `login` ビューを発行します。

> Tip|ヒント: `login` アクションの中で、認証が必要となる前のページを
 `$Yii::app()->user->returnUrl` によって取得しています。
 `$Yii::app()->user` コンポーネントは、[CWebUser] 
(或いはその派生)クラスで、ユーザーのセッション情報
(例えばユーザー名、ステータス)を表します。詳細は 
[Authentication and Authorization](/doc/guide/topics.auth) 
を参照して下さい。

`login` アクションに現れる下記のPHPステートメントに特に注目しましょう:

~~~
[php]
$model->attributes=$_POST['LoginForm'];
~~~

ここまでで述べたように、このコードは単純にユーザーの送信データを
モデルに入れています。`attribute` プロパティは [CModel] で定義されていて、
名前-値 が対になった配列で、モデルの属性に結びついた値が割り当てられる事
が想定されています。その為、もし、`$_POST['LoginForm']` 
がこの様な配列として与えられた場合、上記のコードは下記の長いコードと
同等となります(全ての必要な属性が配列に含まれていると仮定して):

~~~
[php]
$model->username=$_POST['LoginForm']['username'];
$model->password=$_POST['LoginForm']['password'];
$model->rememberMe=$_POST['LoginForm']['rememberMe'];
~~~

> Note|注意: `$_POST['LoginForm']` から文字列ではなく配列を受け取る為に、
私達はビューの中のインプットフィールドに名前を付ける際の決まりを守ります。
さらに細かく説明するならば、`C` というモデルクラスの `a` 
という属性に結びついたインプットフィールドには `C[a]` 
という名前をつけます。例えば、`username` 属性と結びついた
インプットフィールド に私達は、`LoginForm[username]` 
という名前を使用するでしょう。

さあ、残りのタスクは、必要なインプットフィールドとHTMLフォームを含んだ
 `login` ビューの作成です。

<div class="revision">$Id: form.action.txt 1837 2010-02-24 22:49:51Z qiang.xue $</div>