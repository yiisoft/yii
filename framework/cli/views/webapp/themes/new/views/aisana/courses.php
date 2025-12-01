<?php
$this->pageTitle = 'Курсы';
?>

<?php $this->renderPartial('../site/_navbar'); ?>

<section class="courses-section">
	<div class="container">
		<h1 class="courses-page-title">Курсы для вас</h1>
		
		<?php if(!empty($courses)): ?>
		<div class="courses-list">
			<?php foreach ($courses as $course): ?>
			<div class="course-item">
				<div class="course-content">
					<h3 class="course-item-title">
						<?php echo CHtml::encode($course->title); ?>
					</h3>
					<p class="course-item-description">
						<?php echo CHtml::encode($course->description); ?>
					</p>
				</div>
				<?php echo CHtml::link('Подробнее', $course->link, array('class'=>'course-item-btn', 'target'=>'_blank')); ?>
			</div>
			<?php endforeach; ?>
		</div>
		<?php else: ?>
		<div class="no-courses">
			<p>Курсов пока нет</p>
		</div>
		<?php endif; ?>
	</div>
</section>



