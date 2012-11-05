<!doctype html>
<html>
	<head>
		<meta charset="utf-8" />
		<title>Translation report</title>

		<style>
			.diff ins {
				background: #cfc;
				text-decoration: none;
			}

			.diff del {
				background: #ffe6cc;
				text-decoration: none;
			}

			.ok {
				color: #99cc32;
			}

			.errors {
				color: #cc5129;
			}
		</style>
	</head>
	<body>
		<h1><?php echo CHtml::encode($title)?></h1>

		<ul>
			<li><strong>Source:</strong> <?php echo CHtml::encode($sourcePath)?></li>
			<li><strong>Translation:</strong> <?php echo CHtml::encode($translationPath)?></li>
		</ul>

		<?php foreach($results as $name => $result):?>
			<h2 class="<?php echo empty($result['errors']) ? 'ok' : 'errors'?>"><?php echo $name?></h2>
			<?php foreach($result['errors'] as $error):?>
				<p><?php echo CHtml::encode($error)?></p>
			<?php endforeach ?>
			<?php if(!empty($result['diff'])):?>
				<code class="diff"><pre><?php echo $this->highlightDiff($result['diff'])?></pre></code>
			<?php endif?>
		<?php endforeach ?>
		</dl>
	</body>
</html>