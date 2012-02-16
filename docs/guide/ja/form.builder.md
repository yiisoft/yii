フォームビルダを使う
==================

HTMLフォームを作成するときに、しばしばビューについて大量のコードを繰り返し書くはめに陥ります。
そしてそれは他のプロジェクトでは再利用が困難な場合が多いです。
例えば、全ての入力フィールドについて、テキストラベルと可能性のある入力検証エラーの表示を関連づける必要があります。
これらのコードの再利用性を高めるためにフォームビルダ機能を使うことが出来ます。


基本概念
--------------

YiiフォームビルダはHTMLフォームを記述するのに必要な仕様を表現した[CForm]オブジェクトを使っています。
それはフォームに関係するデータモデルを含み、フォームにどのような種類の入力フィールドがあるのか、
どのようにフォームを表示するのかを示すものです。開発者は主に[CForm]オブジェクトを生成し、構成し、
最終的にフォームを表示するためにオブジェクトの表示メソッドをコールすることが必要です。

フォームの入力仕様はフォーム要素階層に関して系統づけられています。
階層の最下位は[CForm]オブジェクトです。
最下位フォームオブジェクトは2種類の子オブジェクトを持ちます。それは[CForm::buttons]と[CForm::elements]です。前者はボタン要素、例えばサブミットボタンやリセットボタンを含み、後者は
入力要素、静的テキストやサブフォームを含みます。サブフォームは他のフォームの[CForm::elements]に含まれる[CForm]オブジェクトです。それは自分自身のデータモデルと[CForm::buttons]と[CForm::elements]を持ちます。

ユーザがフォームをサブミットするとき、全ての階層的なフォームの入力フィールドに入れられたデータはサブミットされ、サブフォーム階層に属する入力フィールドも含まれます。
[CForm]は自動的に入力データを対応するモデルアトリビュートに割り当てられ、データ検証をされる便利な方法を提供します。


単純なフォーム生成
----------------------

以下に、ログインフォームを作成するためにフォームビルダーを利用する方法を示します。

最初に、ログインアクションコードを書きます。

~~~
[php]
public function actionLogin()
{
	$model = new LoginForm;
	$form = new CForm('application.views.site.loginForm', $model);
	if($form->submitted('login') && $form->validate())
		$this->redirect(array('site/index'));
	else
		$this->render('login', array('form'=>$form));
}
~~~

上記のコードでは、`application.views.site.loginForm` (後で説明されます)というパスエイリアスで示される仕様を用いて[CForm]オブジェクトを作成します。
この[CForm]オブジェクトは[モデルを作成する](/doc/guide/form.model)で示されるように、
`LoginForm`モデルと関係しています。

コードに示すように、もしフォームがサブミットされて全ての入力がエラーなく検証されれば、
ユーザのブラウザは`site/index`ページにリダイレクトされます。さもなければ、
`login`フォームが再度表示されます。

パスエイリアス`application.views.site.loginForm`は実際には`protected/views/site/loginForm.php`
というPHPファイルを参照します。このファイルは次に示すように、[CForm]で必要とされる構成を示すPHP配列を返す必要があります。

~~~
[php]
return array(
	'title'=>'ログインの証明となる情報を入力してください',

    'elements'=>array(
        'username'=>array(
            'type'=>'text',
            'maxlength'=>32,
        ),
        'password'=>array(
            'type'=>'password',
            'maxlength'=>32,
        ),
        'rememberMe'=>array(
            'type'=>'checkbox',
        )
    ),

    'buttons'=>array(
        'login'=>array(
            'type'=>'submit',
            'label'=>'Login',
        ),
    ),
);
~~~

構成は、[CForm]の対応するプロパティを初期化するために用いられる、"名前-値"のペアから構成される連想配列です。
構成するために最も重要なプロパティは、上に示したように、[CForm::elements]と[CForm::buttons]です。
それぞれはフォームの要素のリストを指定する配列をとります。次のサブセクションにおいて、フォーム要素をどのように構成するかを詳しく説明します。

最後に、`login`ビュースクリプトを記述しますが、以下のようにたいへん簡単になります。

~~~
[php]
<h1>Login</h1>

<div class="form">
<?php echo $form; ?>
</div>
~~~

> Tip|ヒント: 上記の`echo $form;`コードは`echo $form->render();`と等価です。
> これは[CForm]が`__toString`マジックメソッドを実装しているからであり、それは
> `render()`を呼び出して、フォームオブジェクトのストリング表現をその結果として返すためです。


フォーム要素の指定
------------------------

フォームビルダを使うことにより、ほとんどの作業はビュースクリプトコードを書くことからフォーム要素を指定することに移ります。
このサブセクションでは[CForm::elements]をどのように指定するかを示します。
[CForm::buttons]は[CForm::elements]とほとんど同じであるため、説明しません。


[CForm::elements]プロパティは配列をその値として受け付けます。それぞれの配列要素は単一のフォーム要素となり、それは、入力要素であっても良いし、
テキストストリングやサブフォームであっても構いません。

### 入力要素の指定

入力要素は主にラベルと入力フィールドと入力ヒントテキストとエラー表示から構成されます。
これはモデルアトリビュートに関連づけられる必要があります。入力要素の仕様は[CFormInputElement]インスタンスとして表現されます。
[CForm::elements]配列中の以下のコードは単一の入力要素を指定します。

~~~
[php]
'username'=>array(
    'type'=>'text',
    'maxlength'=>32,
),
~~~

モデルアトリビュートは`username`であり、入力フィールド型は`text`であり、その最長長さを示す`maxlength`アトリビュートは32であることを示します。

[CFormInputElement] の設定可能なプロパティはすべて上記のようにして構成することが出来ます。
例えば、ヒントテキストを表示するために[hint|CFormInputElement::hint]オプションを指定できます。
また、入力フィールドがリストボックスやドロップダウンリストやチェックボックスやラジオボタンである場合は[items|CFormInputElement::items]を指定できます。
オプションの名前が[CFormInputElement]のプロパティでない場合は、対応するHTML入力要素の属性として扱われます。例えば、上記の`maxlength`は[CFormInputElement]のプロパティではありませんので、HTMLのテキスト入力フィールドの`maxlength`属性として扱われます。

[type|CFormInputElement::type]オプションは注意が必要です。これは入力フィールドのタイプを指定します。
例えば、`text`は通常のテキスト入力フィールドが表示されることを意味します。一方、`password`タイプはパスワード入力フィールドが表示されることを意味します。[CFormInputElement]は以下の内蔵型のタイプを認識します。

 - text
 - hidden
 - password
 - textarea
 - file
 - radio
 - checkbox
 - listbox
 - dropdownlist
 - checkboxlist
 - radiolist

上記の内蔵型のタイプ中で、"リスト"タイプのものの使い方について、もう少し詳しく説明したいと思います。
リストタイプには、`dropdownlist`、`checkboxlist`、そして、`radiolist`が含まれます。
これらのタイプでは、対応する入力要素の[items|CFormInputElement::items]プロパティを設定することが要求されます。
これは次のようにして設定することが出来ます。

~~~
[php]
'gender'=>array(
    'type'=>'dropdownlist',
    'items'=>User::model()->getGenderOptions(),
    'prompt'=>'選択して下さい:',
),

...

class User extends CActiveRecord
{
	public function getGenderOptions()
	{
		return array(
			0 => '男性',
			1 => '女性',
		);
	}
}
~~~

上記のコードによって、"選択して下さい:"というプロンプトテキストを持ったドロップダウンリストセレクタが生成されます。
セレクタのオプションは"男性"と"女性"を含みますが、これらは`User`モデルクラスの`getGenderOptions`メソッドから返されたものです。

これら内蔵型のタイプの他に、[type|CFormInputElement::type]には、ウィジェットクラス名かそれのパスエイリアスを指定する事が出来ます。
ウィジェットクラスは[CInputWidget]または[CJuiInputWidget]を継承する必要があります。
入力フィールドを表示する場合、指定されたウィジェットクラスインスタンスが生成され、表示されます。ウィジェットは入力要素のために指定した仕様によって構成されます。


### スタティックテキストの指定

多くの場合フォームは、入力フィールド以外になんらかの飾りのHTMLコードを含みます。例えば、フォームの異なる部分を分離するために水平線が描かれたり、フォームの見栄えを良くするためにある部分にイメージが配置されたりします。
これらのHTMLコードはスタティックテキスト(変化しないテキスト)として[CForm::elements]の中に指定されます。
これを実現するため、スタティックテキストは[CForm::elements]の適当な場所に指定します。例えば、

~~~
[php]
return array(
    'elements'=>array(
		......
        'password'=>array(
            'type'=>'password',
            'maxlength'=>32,
        ),

        '<hr />',

        'rememberMe'=>array(
            'type'=>'checkbox',
        )
    ),
	......
);
~~~

上記では、`password`入力と`rememberMe`入力の間に水平線が挿入されます。

スタティックテキストはテキストの内容と配置が通常でない場合に良く用いられます。もしそれぞれの入力要素が同様に飾られる必要がある場合には
フォーム表示をカスタマイズするアプローチをとるべきで、それはこの章の後で説明します。


### サブフォームの指定

サブフォームは長いフォームを、論理的に関係する部分に分割するために使用されます。例えば、
ユーザ登録フォームは、ログイン情報とプロファイル情報の2つの部分に分割されます。
それぞれのサブフォームはデータモデルに関係してもしなくても構いません。この例のユーザ登録フォームにおいて、
ログイン情報とプロファイル情報を2つの別のデータベーステーブルに(従って2つのデータモデルに)格納する場合は、
それぞれのサブフォームは対応するデータモデルに対応づけられるでしょう。そうでなく、もし全ての情報を単一のテーブルに格納する場合は、
サブフォームはデータモデルを持ちません。なぜならサブフォームは親フォームに関係づけられたデータモデルを共有するからです。

サブフォームも[CForm]オブジェクトの表現です。サブフォームを指定するために、[CForm::elements]プロパティの型を`form`として構成する必要があります。

~~~
[php]
return array(
    'elements'=>array(
		......
        'user'=>array(
            'type'=>'form',
            'title'=>'Login Credential',
            'elements'=>array(
            	'username'=>array(
            		'type'=>'text',
            	),
            	'password'=>array(
            		'type'=>'password',
            	),
            	'email'=>array(
            		'type'=>'text',
            	),
            ),
        ),

        'profile'=>array(
        	'type'=>'form',
        	......
        ),
        ......
    ),
	......
);
~~~

親フォームを構成するように、サブフォームに対しても[CForm::elements]を指定する必要があります。
もしサブフォームがデータモデルに関連づけられるなら、[CForm::model]も構成します。

しばしば、フォームをデフォルトである[CForm]以外のクラスを使って表現したい場合があります。例えば、
この章のすぐ後で示すように、フォーム表示ロジックをカスタマイズするために[CForm]を継承することになります。
入力要素の型を`form`と指定することにより、サブフォームは自動的にその親のフォームのクラスと同じに表されます。
もし入力要素の型を例えば`XyzForm`(`Form`という語で終ること)と指定するならば、サブフォームも`XyzForm`オブジェクトで表されます。


フォーム要素へのアクセス
-----------------------

フォーム要素へのアクセスは配列要素へのアクセスと同様に単純です。[CForm::elements]プロパティは、[CMap]から継承され、通常の配列と同様に要素にアクセス可能な[CFormElementCollection]オブジェクト
を返します。例えば、ログインフォームの例において`username`要素へアクセスするには、以下のコードを用います。

~~~
[php]
$username = $form->elements['username'];
~~~

そしてユーザ登録フォームの例において、`email`要素にアクセスするためには、

~~~
[php]
$email = $form->elements['user']->elements['email'];
~~~

[CForm]は、その[CForm::elements]プロパティへの配列アクセスを実装しているため、上記コードはさらに以下のように単純化されます。

~~~
[php]
$username = $form['username'];
$email = $form['user']['email'];
~~~


入れ子フォームの作成
----------------------

既にサブフォームを見てきました。サブフォームを持つフォームのことを入れ子フォームと呼ぶことにしましょう。
この章では複数のデータモデルに関連された入れ子フォームを、ユーザ登録フォームの例としてとりあげます。
ユーザ認証情報は`User`モデルに格納されており、ユーザプロファイル情報は`Profile`モデルに格納されているものとします。

最初に`register`アクションを次のように作成します。

~~~
[php]
public function actionRegister()
{
	$form = new CForm('application.views.user.registerForm');
	$form['user']->model = new User;
	$form['profile']->model = new Profile;
	if($form->submitted('register') && $form->validate())
	{
		$user = $form['user']->model;
		$profile = $form['profile']->model;
		if($user->save(false))
		{
			$profile->userID = $user->id;
			$profile->save(false);
			$this->redirect(array('site/index'));
		}
	}

	$this->render('register', array('form'=>$form));
}
~~~

上記において、`application.views.user.registerForm`によりフォームの構成を指定します。
フォームがサブミットされ正常に検証された後、ユーザモデルとプロファイルモデルにセーブを試みます。
対応するサブフォームオブジェクトの`model`プロパティにアクセスすることで、ユーザとプロファイルモデルを取得します。
入力値の検証は既に済んでいるため、検証をスキップするために`$user->save(false)`をコールします。これはプロファイルモデルも同様です。

次にフォーム構成ファイルである`protected/views/user/registerForm.php`を記述します。

~~~
[php]
return array(
    'elements'=>array(
	'user'=>array(
		'type'=>'form',
		'title'=>'Login information',
		'elements'=>array(
	        'username'=>array(
	            'type'=>'text',
	        ),
	        'password'=>array(
	            'type'=>'password',
	        ),
	        'email'=>array(
	            'type'=>'text',
	        )
		),
	),

	'profile'=>array(
		'type'=>'form',
		'title'=>'Profile information',
		'elements'=>array(
	        'firstName'=>array(
	            'type'=>'text',
	        ),
	        'lastName'=>array(
	            'type'=>'text',
	        ),
		),
	),
    ),

    'buttons'=>array(
        'register'=>array(
            'type'=>'submit',
            'label'=>'Register',
        ),
    ),
);
~~~

上記において、それぞれのサブフォームを指定する場合、それぞれの[CForm::title]プロパティも指定します。
デフォルトのフォーム表示ロジックではそれぞれのサブフォームをタイトルとしてこのプロパティを用いたフィールドセットで囲みます。

最後に、単純な`register`ビュースクリプトを記述します。

~~~
[php]
<h1>Register</h1>

<div class="form">
<?php echo $form; ?>
</div>
~~~


フォーム表示のカスタマイズ
------------------------

フォームビルダを使用する主なメリットはロジック(別のファイルに格納されたフォーム構成)と表現([CForm::render]メソッド)の分離です。
結果としてフォーム表示を、[CForm::render]を上書きしたり、部分ビューを提供したりすることでカスタマイズが可能となります。
両方のアプローチ共、フォーム構成を触ることが無いため、容易に再利用が可能です。

[CForm::render]を上書きする場合には、[CForm::elements]や[CForm::buttons]をたどり、それぞれのフォームエレメントに対して[CFormElement::render]メソッドをコールする必要があります。例えば、

~~~
[php]
class MyForm extends CForm
{
	public function render()
	{
		$output = $this->renderBegin();

		foreach($this->getElements() as $element)
			$output .= $element->render();

		$output .= $this->renderEnd();

		return $output;
	}
}
~~~

フォームを表示するためのビュースクリプト`_form`を以下のように記述します。

~~~
[php]
<?php
echo $form->renderBegin();

foreach($form->getElements() as $element)
	echo $element->render();

echo $form->renderEnd();
~~~

このビュースクリプトを使用するには、単に以下のようにコールするだけです。

~~~
[php]
<div class="form">
$this->renderPartial('_form', array('form'=>$form));
</div>
~~~

もし標準的なフォーム表示では特殊なフォーム(例えばフォームがある要素に特別な飾りを必要とする場合)について対応できない場合には、
ビュースクリプト中で以下のようなやり方を行うことができます。

~~~
[php]
なんらかの複雑なUI要素

<?php echo $form['username']; ?>

なんらかの複雑なUI要素

<?php echo $form['password']; ?>

なんらかの複雑なUI要素
~~~

最後のアプローチにおいては同様の量のフォームコードを書く必要があるため、フォームビルダはあまり利点があるとは言えません。
しかしながら、フォームは別の構成ファイルにより指定されるため、開発者はロジックに集中できるという利点があります。


<div class="revision">$Id: form.builder.txt 2890 2011-01-18 15:58:34Z qiang.xue $</div>