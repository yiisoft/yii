データキャッシュ
============

データキャッシュは、PHP 変数をキャッシュし、
後でそのキャッシュから読み込めるようにします。
この目的のために、キャッシュコンポーネントのベースクラス [CCache] は、
多くの場合に利用される [set()|CCache::set] と [get()|CCache::get] の
2 つのメソッドを提供します。

キャッシュに変数 `$value` を保存するには、ユニーク ID を選び、
[set()|CCache::set] を呼びます:

~~~
[php]
Yii::app()->cache->set($id, $value);
~~~

キャッシュされたデータは、キャッシングポリシー（たとえば、
キャッシュ容量いっぱいになれば、一番古いデータが削除されます）のために、
キャッシュが消されない限りずっと残ります。
この挙動を変えるために、[set()|CCache::set] を呼ぶときに、
有効期限パラメータを指定し、一定の期間の後、キャッシュが削除されるように
する事もできます。

~~~
[php]
// 最大 30 秒間、キャッシュに値が保持される
Yii::app()->cache->set($id, $value, 30);
~~~

その後、この変数（同じ、あるいは別のウェブリクエスト中に）にアクセスする必要がある場合、
その ID を指定して [get()|CCache::get] を呼べば、キャッシュから読み込めます。
もし、返り値が false なら、値がキャッシュされていないため、
キャッシュを再生成する必要があります。

~~~
[php]
$value=Yii::app()->cache->get($id);
if($value===false)
{
	// キャッシュが見つからなかったため、
	// 後で利用できるように、$value のキャッシュを再生成し保存します:
	// Yii::app()->cache->set($id,$value);
}
~~~

変数をキャッシュするために ID を選ぶとき、
ID がアプリケーション中でキャッシュされるかもしれない、
他の全ての変数内でユニークである事を確かめてください。
キャッシュコンポーネントでは、他のアプリケーションで同じ ID が使われていたとしても
それらを区別出来るため、ID がアプリケーションを横切ってユニークである必要はありません。

MemCacheやAPCのようなある種のキャッシュストレージにおいては、バッチモードにより複数のキャッシュデータを
獲得する手段をサポートしている。これはキャッシュ獲得のオーバヘッドを低減する。
[mget()|CCache::mget]という名のメソッドはこの機能を利用することを提供する。
裏打ちするキャッシュストレージがこれをサポートしていない場合でも、[mget()|CCache::mget]はこれをシミュレートする。

キャッシュから、キャッシュされた値を削除するには、[delete()|CCache::delete]
を呼びます。また、全てのキャッシュを削除するには、[flush()|CCache::flush]
を呼びます。[flush()|CCache::flush] は、他のアプリケーションのデータを含む、
全てのキャッシュデータを削除するため、このメソッドを呼ぶ際は、
細心の注意を払ってください。

> Tip|ヒント: [CCache] は `ArrayAccess` により実装されているため、
> キャッシュコンポーネントは配列のように扱えます。下記に例を示します:
> ~~~
> [php]
> $cache=Yii::app()->cache;
> $cache['var1']=$value1;  // $cache->set('var1',$value1); と同等
> $value2=$cache['var2'];  // $value2=$cache->get('var2'); と同等
> ~~~

キャッシュ依存関係
----------------

有効期限設定に加えて、キャッシュデータも依存関係のあるデータの変更により、
無効にされるかもしれません。
たとえば、あるファイルの内容がキャッシュされており、ファイルが変更された場合、
キャッシュされたコピーを無効にし、キャッシュの代わりにファイルから
最新のデータを読み込む必要があります。

[CCacheDependency] か、その子クラスのインスタンスとして依存関係を表します。
[set()|CCache::set] を呼ぶ際に、キャッシュされるデータと共に、
依存関係のインスタンスを指定します。

~~~
[php]
// 値は 30 秒間有効です
// さらに、依存関係にあるファイルが変更された場合、有効期限内でも無効になります
Yii::app()->cache->set($id, $value, 30, new CFileCacheDependency('FileName'));
~~~

今、[get()|CCache::get] を呼び、キャッシュから `$value` を取り出そうとすると、
依存関係が調査され、それが変更されていてば、データを再生成する必要が
あることを知らせるために、false 値が返ります。

以下に、利用可能なキャッシュ依存関係の概要です:

   - [CFileCacheDependency]: ファイルの最終更新日時が変更された場合、
依存関係が変更されます。

   - [CDirectoryCacheDependency]: ディレクトリや、サブディレクトリ下の
ファイルのいずれかが変更された場合、依存関係が変更されます。

   - [CDbCacheDependency]: 指定された SQL 文のクエリ結果が変わった場合に、
依存関係が変更されます。

   - [CGlobalStateCacheDependency]: 指定されたグローバルステートが変更された場合に、
依存関係が変更されます。グローバルステートは、アプリケーション内での多数のリクエスト、
および、多数のセッションを横断して持続する変数です。これは、
[CApplication::setGlobalState()] により定義されます。

   - [CChainedCacheDependency]: チェーン上の依存関係のいずれかが変更された場合、
依存関係が変更されます。

   - [CExpressionDependency]: 指定されたPHP表現の結果が変更された場合、依存関係が変更されます。


クエリキャッシング
------------------

1.1.7版から、Yiiはクエリキャッシングサポートを追加しました。
データキャッシュの上に構築されており、クエリキャッシングはDBクエリの結果をキャッシュに格納し、
将来同じクエリが要求された場合に、結果が直接キャッシュから得られるためDBクエリの実行時間が短縮される。

> Info|注: ある種のDBMS、例えば[MySQL](http://dev.mysql.com/doc/refman/5.1/en/query-cache.htmlの
ある種のDBMS (e.g. [MySQL](http://dev.mysql.com/doc/refman/5.1/en/query-cache.html))
> はまた、DBサーバサイドでクエリキャッシュをサポートしています。
>サーバサイドでのクエリキャッシングと比較すると、同じ機能を提供するものの、よりフレキシブルかつ
>潜在的に効率的でしょう。 

### クエリキャッシングの許可

クエリキャッシングの許可を行うには [CDbConnection::queryCacheID]
が正当なキャッシュアプリケーションコンポーネント(デフォルトでは`cache`)を参照していることを確認してください。

### DAOと共にクエリキャッシングを使用する

クエリキャッシングを使うには、DBクエリを行う際に[CDbConnection::cache()]メソッドをコールします。
例を示します。

~~~
[php]
$sql = 'SELECT * FROM tbl_post LIMIT 20';
$dependency = new CDbCacheDependency('SELECT MAX(update_time) FROM tbl_post');
$rows = Yii::app()->db->cache(1000, $dependency)->createCommand($sql)->queryAll();
~~~

上記文を実行する際に、Yiiは最初にキャッシュが、実行された正当なSQL文の結果を持っているかどうかをチェックします。
これには以下の3つの条件をチェックします。

- キャッシュがSQL文によりインデクスされたエントリをもっているかどうか
- キャッシュエントリが無効になっていないかどうか(最初にキャッシュに格納されてから1000秒以内)
- 依存性が変更されていないか(`update_time`の最大値がクエリ結果が格納されてから同じかどうか)

もし上記すべての条件が満足されていれば、キャッシュされた結果が直接キャッシュから返されます。
そうでなければ、SQL文はDBサーバへ送信され、対応する結果がキャッシュに格納され、同時に返されます。


### アクティブレコードと共にクエリキャッシングを使う

クエリキャッシングは[アクティブレコード](/doc/guide/database.ar)と共に使用可能です。
そうするには、似たような[CActiveRecord::cache()]メソッドを以下のように呼び出します。

~~~
[php]
$dependency = new CDbCacheDependency('SELECT MAX(update_time) FROM tbl_post');
$posts = Post::model()->cache(1000, $dependency)->findAll();
// リレーショナルARクエリ
$posts = Post::model()->cache(1000, $dependency)->with('author')->findAll();
~~~

ここでの`cache()`メソッドは本質的には[CDbConnection::cache()]へのショートカットです。
内部的にはアクティブレコードによって生成されたSQL文を実行する際にYiiは前節でしめしたような
クエリキャッシングを行います。


### 複数クエリのキャッシング

デフォルトでは、[CDbConnection]または[CActiveRecord]のどちらの`cache()`メソッドにしても、
次のSQLクエリをキャッシュするようにマークします。
他のSQLクエリは、`cache()`を再び呼ばない限りキャッシュされません。例えば、

~~~
[php]
$sql = 'SELECT * FROM tbl_post LIMIT 20';
$dependency = new CDbCacheDependency('SELECT MAX(update_time) FROM tbl_post');

$rows = Yii::app()->db->cache(1000, $dependency)->createCommand($sql)->queryAll();
// クエリキャッシュは使われません。
$rows = Yii::app()->db->createCommand($sql)->queryAll();
~~~

`cache()`メソッドに余分な`$queryCount`パラメータを供給することにより、複数のクエリに対して
クエリキャッシングを行わせることが可能です。この後の例では、`cache()`をコールすることにより、
次の2つのクエリについて、クエリキャッシングを行わせることができます。

~~~
[php]
// ...
$rows = Yii::app()->db->cache(1000, $dependency, 2)->createCommand($sql)->queryAll();
// クエリキャッシュが使われます。
$rows = Yii::app()->db->createCommand($sql)->queryAll();
~~~

ご存知のとおり、リレーショナルなアクティブレコードクエリを実行するときは、複数のSQLクエリが実行されます
( [log messages](/doc/guide/topics.logging))。
例えば、もし`Post`と`Comment`の関係が`HAS_MANY`の場合、以下のコードが実際には2つのDBクエリを実行します。

- もし最初にpostsを20件を最大として選択します。
- 次に選択されたpostsについてcommentsを選択します。

~~~
[php]
$posts = Post::model()->with('comments')->findAll(array(
       'limit'=>20,
));
~~~

以下のようにクエリキャッシングを行う場合、最初のDBクエリだけがキャッシュされます。

~~~
[php]
$posts = Post::model()->cache(1000, $dependency)->with('comments')->findAll(array(
       'limit'=>20,
));
~~~

両方のDBクエリをキャッシュする場合は、次にいくつのDBクエリをキャッシュしたいかのパラメータを設定します。

~~~
[php]
$posts = Post::model()->cache(1000, $dependency, 2)->with('comments')->findAll(array(
       'limit'=>20,
));
~~~


### 制約

リソースハンドルを返すようなクエリにはクエリキャッシュは働きません。
例えばある種のDBMSにおいて`BLOB`カラムタイプを用いる場合クエリリザルトはカラムデータについて
リソースハンドルを返します。

ある種のキャッシュストレージはサイズに制約があります。memcacheはエントリサイズの最大値は1MBが制約です。
そのため、クエリリザルトがこの制約を越える場合、キャッシュはされません。


<div class="revision">$Id: caching.data.txt 3125 2008-11-06 19:43:44Z qiang.xue $</div>
