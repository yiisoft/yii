<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta http-equiv="content-language" content="en"/>
<link rel="stylesheet" type="text/css" href="css/main.css" />
<title>Verificador de requisitos do Yii</title>
</head>

<body>
<div id="page">

<div id="header">
<h1>Verificador de requisitos do Yii</h1>
</div><!-- header-->

<div id="content">
<h2>Descrição</h2>
<p>
Este script verifica se as configurações do servidor satisfazem os requisitos
para executar aplicações Web que utilizem o <a href="http://www.yiiframework.com/"> Yii </a>.
É verificado se o servidor está executando a versão correta do PHP,
se as extensões apropriadas do PHP foram carregadas,
e se as definições do arquivo php.ini estão corretas.
</p>

<h2>Resultados</h2>
<p>
<?php if($result>0): ?>
Parabéns! As configurações do seu servidor satisfazem todos os requisitos do Yii.
<?php elseif($result<0): ?>
As configurações do seu servidor satisfazem os requisitos mínimos do Yii. Por favor, preste atenção às advertências listadas abaixo caso sua aplicação for utilizar os recursos correspondentes.
<?php else: ?>
Infelizmente as configurações do seu servidor não satisfazem os requisitos do Yii.
<?php endif; ?>
</p>

<h2>Detalhes</h2>

<table class="result">
<tr><th>Nome</th><th>Resultado</th><th>Exigido por</th><th>Detalhe</th></tr>
<?php foreach($requirements as $requirement): ?>
<tr>
	<td>
	<?php echo $requirement[0]; ?>
	</td>
	<td class="<?php echo $requirement[2] ? 'passed' : ($requirement[1] ? 'failed' : 'warning'); ?>">
	<?php echo $requirement[2] ? 'OK' : ($requirement[1] ? 'Falhou' : 'Advertência'); ?>
	</td>
	<td>
	<?php echo $requirement[3]; ?>
	</td>
	<td>
	<?php echo $requirement[4]; ?>
	</td>
</tr>
<?php endforeach; ?>
</table>

<table>
<tr>
<td class="passed">&nbsp;</td><td>OK</td>
<td class="failed">&nbsp;</td><td>Falhou</td>
<td class="warning">&nbsp;</td><td>Advertência</td>
</tr>
</table>

</div><!-- content -->

<div id="footer">
<?php echo $serverInfo; ?>
</div><!-- footer -->

</div><!-- page -->
</body>
</html>