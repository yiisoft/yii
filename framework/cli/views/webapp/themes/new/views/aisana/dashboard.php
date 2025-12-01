<?php
$this->pageTitle = 'Админ-панель';
?>

<div class="admin-dashboard">
	<h1>Добро пожаловать в админ-панель</h1>
	
	<div class="dashboard-stats">
		<div class="stat-card">
			<h3>Новости</h3>
			<p class="stat-number"><?php echo News::model()->count(); ?></p>
			<a href="<?php echo Yii::app()->createUrl('aisana/newsAdmin'); ?>" class="stat-link">Управление →</a>
		</div>
		
		<div class="stat-card">
			<h3>Опубликовано</h3>
			<p class="stat-number"><?php echo News::model()->count('published=1'); ?></p>
		</div>
		
		<div class="stat-card">
			<h3>Черновики</h3>
			<p class="stat-number"><?php echo News::model()->count('published=0'); ?></p>
		</div>
	</div>
	
	<div class="quick-actions">
		<h2>Быстрые действия</h2>
		<a href="<?php echo Yii::app()->createUrl('aisana/newsCreate'); ?>" class="btn btn-primary">Создать новость</a>
		<a href="<?php echo Yii::app()->createUrl('aisana/newsAdmin'); ?>" class="btn btn-secondary">Все новости</a>
	</div>
</div>

