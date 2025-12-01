<?php
$this->pageTitle = $model->title;
?>

<?php $this->renderPartial('../site/_navbar'); ?>

<section class="news-detail-section">
	<div class="container">
		<div class="news-detail">
			<div class="news-detail-header">
				<h1 class="news-detail-title"><?php echo CHtml::encode($model->title); ?></h1>
			</div>
			
			<div class="news-detail-body-wrapper">
				<div class="news-detail-text">
					<?php echo nl2br(CHtml::encode($model->content)); ?>
				</div>
				
				<?php if(!empty($model->image)): ?>
				<div class="news-detail-image-wrapper">
					<?php 
					$imageUrl = $model->image;
					if(strpos($imageUrl, 'http') !== 0) {
						$imageUrl = Yii::app()->request->baseUrl . $imageUrl;
					}
					?>
					<img src="<?php echo CHtml::encode($imageUrl); ?>" alt="<?php echo CHtml::encode($model->title); ?>" class="news-detail-image" />
					<?php if(!empty($model->excerpt)): ?>
					<p class="news-detail-image-caption"><?php echo CHtml::encode($model->excerpt); ?></p>
					<?php endif; ?>
				</div>
				<?php endif; ?>
			</div>
			
			<div class="news-detail-actions">
				<div class="news-detail-date">
					<?php 
					$months = array(
						1 => 'января', 2 => 'февраля', 3 => 'марта', 4 => 'апреля',
						5 => 'мая', 6 => 'июня', 7 => 'июля', 8 => 'августа',
						9 => 'сентября', 10 => 'октября', 11 => 'ноября', 12 => 'декабря'
					);
					$timestamp = strtotime($model->created_at);
					echo date('d', $timestamp) . ' ' . $months[(int)date('n', $timestamp)] . ' ' . date('Y', $timestamp);
					?>
				</div>
				<?php echo CHtml::link('Назад', array('aisana/news'), array('class'=>'btn btn-back')); ?>
			</div>
		</div>
	</div>
</section>

