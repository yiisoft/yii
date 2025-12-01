<?php
$this->pageTitle = 'Новости';
?>

<?php $this->renderPartial('../site/_navbar'); ?>

<section class="news-section">
	<div class="container">
		<h1 class="news-page-title">Новости</h1>
		
		<?php if($dataProvider->totalItemCount > 0): ?>
		<div class="news-grid">
			<?php $this->widget('zii.widgets.CListView', array(
				'dataProvider'=>$dataProvider,
				'itemView'=>'../news/_newsCard',
				'template'=>'{items}',
				'itemsCssClass'=>'news-list',
			)); ?>
		</div>
		<?php else: ?>
		<div class="no-news">
			<p>Новостей пока нет</p>
		</div>
		<?php endif; ?>
	</div>
</section>

