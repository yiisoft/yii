<?php
// Здесь можно добавить логику получения курсов из БД
// Пока используем статические данные
$courses = array(
	array(
		'title' => 'Введение в Machine Learning',
		'description' => 'Основы машинного обучения и практические примеры',
		'link' => '#'
	),
	array(
		'title' => 'Deep Learning для начинающих',
		'description' => 'Изучение нейронных сетей и глубокого обучения',
		'link' => '#'
	),
	array(
		'title' => 'AI в бизнесе',
		'description' => 'Применение искусственного интеллекта в бизнес-процессах',
		'link' => '#'
	)
);
?>

<section class="container courses-section" id="courses-section">
	<h2 class="section-title">Курсы для вас</h2>
	<div class="courses-grid">
		<?php foreach ($courses as $course): ?>
		<div class="course-card">
			<h3 class="course-title"><?php echo CHtml::encode($course['title']); ?></h3>
			<p class="course-description"><?php echo CHtml::encode($course['description']); ?></p>
			<?php echo CHtml::link('Подробнее', $course['link'], array('class'=>'course-btn')); ?>
		</div>
		<?php endforeach; ?>
	</div>
</section>

