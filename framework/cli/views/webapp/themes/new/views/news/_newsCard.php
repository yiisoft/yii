<?php 
$newsUrl = Yii::app()->createUrl('aisana/newsView', array('slug'=>$data->slug));
?>
<div class="news-card">
	<?php if(!empty($data->image)): ?>
	<div class="news-image">
		<?php 
		$imageUrl = $data->image;
		if(strpos($imageUrl, 'http') !== 0) {
			$imageUrl = Yii::app()->request->baseUrl . $imageUrl;
		}
		?>
		<a href="<?php echo CHtml::encode($newsUrl); ?>">
			<img src="<?php echo CHtml::encode($imageUrl); ?>" alt="<?php echo CHtml::encode($data->title); ?>" />
		</a>
	</div>
	<?php endif; ?>
	
	<div class="news-content">
		<h3 class="news-title">
			<?php echo CHtml::link(CHtml::encode($data->title), array('aisana/newsView', 'slug'=>$data->slug)); ?>
		</h3>
		
		<?php if(!empty($data->excerpt)): ?>
		<p class="news-excerpt"><?php echo CHtml::encode($data->excerpt); ?></p>
		<?php endif; ?>
		
		<div class="news-footer">
			<span class="news-date">
				<?php 
				$months = array(
					1 => 'января', 2 => 'февраля', 3 => 'марта', 4 => 'апреля',
					5 => 'мая', 6 => 'июня', 7 => 'июля', 8 => 'августа',
					9 => 'сентября', 10 => 'октября', 11 => 'ноября', 12 => 'декабря'
				);
				$timestamp = strtotime($data->created_at);
				echo date('d', $timestamp) . ' ' . $months[(int)date('n', $timestamp)] . ' ' . date('Y', $timestamp);
				?>
			</span>
			<?php echo CHtml::link('Подробнее', array('aisana/newsView', 'slug'=>$data->slug), array('class'=>'news-btn')); ?>
		</div>
	</div>
</div>

