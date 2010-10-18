<!DOCTYPE html PUBLIC
	"-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>
<?php echo CHtml::encode($data['type']); ?>
</title>

<style type="text/css">
/*<![CDATA[*/
body {font-family:"Verdana";font-weight:normal;color:black;background-color:white;}
h1 { font-family:"Verdana";font-weight:normal;font-size:18pt;color:red }
h2 { font-family:"Verdana";font-weight:normal;font-size:14pt;color:maroon }
h3 {font-family:"Verdana";font-weight:bold;font-size:11pt}
p {font-family:"Verdana";font-size:9pt;}
pre {font-family:"Lucida Console";font-size:10pt;}
.version {color: gray;font-size:8pt;border-top:1px solid #aaaaaa;}
.message {color: maroon;}
.source {font-family:"Lucida Console";font-weight:normal;background-color:#ffffee;}
.error {background-color: #ffeeee;}
/*]]>*/
</style>
</head>

<body>
<h1><?php echo $data['type']; ?></h1>

<h3>Opis</h3>
<p class="message">
<?php echo nl2br(CHtml::encode($data['message'])); ?>
</p>

<h3>Izvor datoteke</h3>
<p>
<?php echo CHtml::encode($data['file'])."({$data['line']})"; ?>
</p>

<div class="source">
<pre>
<?php
if(empty($data['source']))
	echo 'Izvorni kod nije raspoloživ.';
else
{
	foreach($data['source'] as $line=>$code)
	{
		if($line!==$data['line'])
			echo CHtml::encode(sprintf("%05d: %s",$line,str_replace("\t",'    ',$code)));
		else
		{
			echo "<div class=\"error\">";
			echo CHtml::encode(sprintf("%05d: %s",$line,str_replace("\t",'    ',$code)));
			echo "</div>";
		}
	}
}
?>
</pre>
</div><!-- end of source -->

<h3>Stog trag</h3>
<div class="callstack">
<pre>
<?php echo CHtml::encode($data['trace']); ?>
</pre>
</div><!-- end of callstack -->

<div class="version">
<?php echo date('Y-m-d H:i:s',$data['time']) .' '. $data['version']; ?>
</div>
</body>
</html>