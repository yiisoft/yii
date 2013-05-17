O Script de Entrada
============

O Script de Entrada, `index.php`, é um script bootstrap em PHP que processa solicitações de
usuários inicialmente. É o único script PHP que os usuários finais podem executar
diretamente.

Na maioria dos casos, o script de entrada de uma aplicação Yii contém um código simples,
mostrado abaixo:

~~~
[php]
// remove the following line when in production mode
defined('YII_DEBUG') or define('YII_DEBUG',true);
// include Yii bootstrap file
require_once('path/to/yii/framework/yii.php');
// create application instance and run
$configFile='path/to/config/file.php';
Yii::createWebApplication($configFile)->run();
~~~

O script inclui primeiramente o arquivo de bootstrap `yii.php` do framework
Yii. Ele cria em seguida uma instância do aplicativo Web que especifica as
configurações e o executa.

Modo de Debug
-------------

Uma aplicação Yii pode ser executado tanto em modo de debug (depuração) quanto
em modo de produção de acordo com o valor da constante `YII_DEBUG`. Por padrão,
esse valo constante é definido como `false`, o que significa modo de produção.
Para executar em modo de debug, defina essa constante como `true` antes de
incluir o arquivo `yii.php`. Executando a aplicação em modo de debug é importante
durante a fase de desenvolvimento pois fornece ricas informações de depuração 
quando um erro ocorre.

<div class="revision">$Id: basics.entry.txt 1622 2009-12-26 20:56:05Z qiang.xue $</div>