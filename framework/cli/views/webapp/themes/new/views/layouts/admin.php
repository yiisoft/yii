<?php
$baseUrl = Yii::app()->request->baseUrl;
$currentAction = Yii::app()->controller->action->id;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo CHtml::encode($this->pageTitle); ?> — Админ-панель</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="<?php echo $baseUrl; ?>/css/main.css?v=dark-background-2025-12-01" />
	<link rel="stylesheet" type="text/css" href="<?php echo $baseUrl; ?>/css/admin.css?v=dark-background-2025-12-01" />
</head>
<body class="admin-body" style="background: url('<?php echo $baseUrl; ?>/uploads/background-main.png') center center / cover no-repeat fixed; background-attachment: fixed;">
	<div class="admin-container <?php echo Yii::app()->user->isGuest ? 'admin-container--auth' : 'admin-container--with-nav'; ?>">
		<?php if(!Yii::app()->user->isGuest): ?>
			<nav class="admin-nav">
				<div class="admin-brand">
					<img src="<?php echo $baseUrl; ?>/uploads/logo.svg" alt="AI Sana">
					<div class="admin-brand-text">
						<span>AI Sana</span>
						<small>Админ-панель</small>
					</div>
				</div>
				<ul class="admin-nav-menu">
					<li class="<?php echo $currentAction === 'dashboard' ? 'is-active' : ''; ?>">
						<a href="<?php echo Yii::app()->createUrl('aisana/dashboard'); ?>">Главная</a>
					</li>
				<li class="<?php echo in_array($currentAction, array('newsAdmin', 'newsUpdate', 'newsView')) ? 'is-active' : ''; ?>">
					<a href="<?php echo Yii::app()->createUrl('aisana/newsAdmin'); ?>">Новости</a>
				</li>
				<li class="<?php echo $currentAction === 'newsCreate' ? 'is-active' : ''; ?>">
					<a href="<?php echo Yii::app()->createUrl('aisana/newsCreate'); ?>">Создать новость</a>
				</li>
				<li class="<?php echo in_array($currentAction, array('coursesAdmin', 'courseUpdate')) ? 'is-active' : ''; ?>">
					<a href="<?php echo Yii::app()->createUrl('aisana/coursesAdmin'); ?>">Курсы</a>
				</li>
				<li class="<?php echo $currentAction === 'courseCreate' ? 'is-active' : ''; ?>">
					<a href="<?php echo Yii::app()->createUrl('aisana/courseCreate'); ?>">Создать курс</a>
				</li>
					<li>
						<a href="<?php echo Yii::app()->createUrl('aisana/index'); ?>" target="_blank">На сайт</a>
					</li>
					<li>
						<a class="logout-link" href="<?php echo Yii::app()->createUrl('aisana/logout'); ?>">Выйти</a>
					</li>
				</ul>
			</nav>
		<?php endif; ?>
		
		<main class="admin-content <?php echo Yii::app()->user->isGuest ? 'admin-content--auth' : ''; ?>">
			<?php if(Yii::app()->user->hasFlash('success')): ?>
				<div class="flash flash-success"><?php echo Yii::app()->user->getFlash('success'); ?></div>
			<?php endif; ?>
			
			<?php if(Yii::app()->user->hasFlash('error')): ?>
				<div class="flash flash-error"><?php echo Yii::app()->user->getFlash('error'); ?></div>
			<?php endif; ?>
			
			<?php echo $content; ?>
		</main>
	</div>
</body>
</html>

