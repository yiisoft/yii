ユーザ認証
===================

このブログアプリケーションでは、システムオーナとゲストユーザの区別が必要です。 したがって、[ユーザ認証](http://www.yiiframework.com/doc/guide/topics.auth)機能を実装する必要があります。

スケルトンアプリケーションにはすでにユーザ認証機能が備わっています。 ユーザ名とパスワードのどちらもが `demo` 、もしくは `admin` で確認することができます。このセクションでは、認証を `tbl_user` テーブルに基づいて行うよう、対応するコードを修正します。

ユーザ認証は [IUserIdentity] インターフェイスを実装するクラスで行われます。スケルトンアプリケーションでは、この目的のために `UserIdentity` クラスを使っています。このクラスファイルは、`/wwwroot/blog/protected/components/UserIdentity.php` に保存されています。

> Tip|ヒント: 規約により、クラスファイル名は対応するクラス名に接尾辞に拡張子 `.php` をつけたものになります。この規約に従って、[パスエイリアス](http://www.yiiframework.com/doc/guide/basics.namespace)を使ったクラス参照が可能です。 例えば、`UserIdentity` クラスを、`application.components.UserIdentity` というエイリアスで参照することができます。Yii の多くの API において、パスエイリアスを利用可能です。(例：[Yii::createComponent()|YiiBase::createComponent]）そして、パスエイリアスを使うことは、コードに絶対パスを埋め込む必要をなくします。絶対パスを記述することはしばしばアプリケーション配備の際のトラブルの元になります。

`UserIdentity` クラスを以下のように修正します。

~~~
[php]
<?php
class UserIdentity extends CUserIdentity
{
	private $_id;

        public function authenticate()
        {
                $username=strtolower($this->username);
                $user=User::model()->find('LOWER(username)=?',array($username));
                if($user===null)
                        $this->errorCode=self::ERROR_USERNAME_INVALID;
                else if(!$user->validatePassword($this->password))
                        $this->errorCode=self::ERROR_PASSWORD_INVALID;
                else
                {
                        $this->_id=$user->id;
                        $this->username=$user->username;
                        $this->errorCode=self::ERROR_NONE;
                }
                return $this->errorCode==self::ERROR_NONE;
        }

	public function getId()
	{
		return $this->_id;
	}
}
~~~

`authenticate()` メソッドにおいて、 `User` クラスを用いて `tbl_user` テーブルの行を参照しています。`tbl_user` テーブルの `username` 列は特定のユーザ名（大文字小文字の区別なし）と同一です。`User` クラスは前のセクションで `gii` ツールによって作られたものであることを思い出してください。`User` クラスは [CActiveRecord] を継承しているため、 [ActiveRecord 機能](http://www.yiiframework.com/doc/guide/database.ar)を、オブジェクト指向(OOP)にのっとったやり方で `tbl_user` テーブルにアクセスすることができます。

ユーザが正当なパスワードを入力したかどうかをチェックするため、`User`クラスの`validatePassword`メソッドが起動されます。
`/wwwroot/blog/protected/models/User.php`を以下の様に修正します。
プレーンなパスワードをデータベースに保存するのではなく、ランダムに発生したソルトキーとともにパスワードのハッシュを保存することに注意してください。
ユーザが入力したパスワードを検証する際は、そのかわりハッシュされた結果を比較することになります。

~~~
[php]
class User extends CActiveRecord
{
        ......
        public function validatePassword($password)
        {
                return $this->hashPassword($password,$this->salt)===$this->password;
        }

        public function hashPassword($password,$salt)
        {
                return md5($salt.$password);
        }
}
~~~

`UserIdentity` クラスでは、`getId()` メソッドをオーバーライドして、`tbl_user` テーブルから見つかったユーザの `id` を返すようにしています。元の実装では、代わりにユーザ名を返すようになっていました。`username` と `id` プロパティはともにユーザセッションに保存され、コードのどこからでも `Yii::app()->user` でアクセスすることが可能です。

> Tip|ヒント: `UserIdentity` クラスにおいて、対応するクラスファイルを読み込むことなく [CUserIdentity] を参照しています。 これは [CUserIdentity] が Yii framework のコアクラスであるためです。Yii は任意のコアクラスが最初に参照されたときに、自動的にそのクラスファイルを読み込みます。
>
>`User` クラスでも同じことが行われています。 なぜなら、 `User` クラスファイルが、`/wwwroot/blog/protected/models` ディレクトリ以下にあり、アプリケーション初期構成の下記コードで PHP の `include_path` に追加されているからです。
>
> ~~~
> [php]
> return array(
>     ......
>     'import'=>array(
>         'application.models.*',
>         'application.components.*',
>     ),
>     ......
> );
> ~~~
>
> 上記の初期構成は `/wwwroot/blog/protected/models` か `/wwwroot/blog/protected/components` の下にある、いかなるクラスファイルも、最初に参照された時点で自動的に読み込まれることを示します。

`UserIdentity` クラスは主に `LoginForm` クラスで、ログインページにで入力されたユーザ名とパスワードを元にユーザを認証するために使われます。以下コードではどのように `UserIdentity` が使われるのかを示します。 

~~~
[php]
$identity=new UserIdentity($username,$password);
$identity->authenticate();
switch($identity->errorCode)
{
	case UserIdentity::ERROR_NONE:
		Yii::app()->user->login($identity);
		break;
	......
}
~~~

> Info|情報: identity クラスと `user` アプリケーションコンポーネントはしばしば混同されます。前者は認証を行う方法のことであり、後者は現在のユーザに関する情報をあらわします。アプリケーションがもてる`user`コンポーネントはひとつだけですが、identityクラスは複数持つことができます。identityクラスはどのような認証方法がサポートされるかによります。いったん認証されると、identityインスタンスから`user`コンポーネントへ情報が渡され、アプリケーション全体から`user`を用いてアクセス可能になります。

修正後の`UserIdentity`クラスを確認するため、ブラウザでURL`http://www.example.com/blog/index.php`にアクセスし、`tbl_user`テーブルのユーザ名とパスワードでログインしてみてください。[ブログデモ](http://www.yiiframework.com/demos/blog/)で提供されるデータベースを利用した場合、ユーザー名`demo`、パスワード`demo`でアクセスできるはずです。このブログシステムにはユーザ管理機能はありません。そのため、ユーザーはウェブインターフェースで、自身のアカウントを変更したり、新しいアカウントを作成出来ません。ユーザ管理機能はブログアプリケーションの将来の機能拡張として検討されるでしょう。

<div class="revision">$Id: prototype.auth.txt 2333 2009-02-18 19:29:48Z qiang.xue $</div>
