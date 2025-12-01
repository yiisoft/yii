<?php
// Получаем последние 3 опубликованные новости
$criteria = new CDbCriteria;
$criteria->condition = 'published = 1';
$criteria->order = 'created_at DESC';
$criteria->limit = 3;
$news = News::model()->findAll($criteria);
?>

<?php if(!empty($news)): ?>
<section class="container news-section" id="news-section">
	<h2 class="section-title">Новости</h2>
	<div class="news-grid">
		<?php foreach ($news as $item): ?>
		<div class="news-card">
			<?php if(!empty($item->image)): ?>
			<div class="news-image">
				<img src="<?php echo CHtml::encode($item->image); ?>" alt="<?php echo CHtml::encode($item->title); ?>" />
			</div>
			<?php endif; ?>
			
			<div class="news-content">
				<h3 class="news-title">
					<?php echo CHtml::link(CHtml::encode($item->title), array('aisana/newsView', 'slug'=>$item->slug)); ?>
				</h3>
				
				<?php if(!empty($item->excerpt)): ?>
				<p class="news-excerpt"><?php echo CHtml::encode($item->excerpt); ?></p>
				<?php endif; ?>
				
				<div class="news-meta">
					<span class="news-date">
						<?php echo date('d F Y', strtotime($item->created_at)); ?>
					</span>
				</div>
				
				<?php echo CHtml::link('Подробнее', array('aisana/newsView', 'slug'=>$item->slug), array('class'=>'news-btn')); ?>
			</div>
		</div>
		<?php endforeach; ?>
	</div>
</section>
<?php endif; ?>

