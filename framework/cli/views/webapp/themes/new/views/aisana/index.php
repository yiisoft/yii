<?php
$this->pageTitle = 'AI Sana Kozybayev University';
?>

<?php $this->renderPartial('../site/_navbar'); ?>

<section class="main-section">
	<section class="container main-content-container">
		<div class="center-box">
			<p class="center-box-subtitle">AI Sana Kozybayev University</p>
			<h1>Интеллектуальные AI-агенты для университета и города</h1>
			<p class="center-box-text">Новые возможности автоматизации — от диагностики пациентов до оптимизации городского транспорта и работы с документами</p>
		</div>
		<button class="more-btn">Подробнее</button>
	</section>
	
	<?php $this->renderPartial('parts/_agents'); ?>
	
	<section class="container about-section" id="about-program-section">
		<h2 class="section-title">О программе AI SANA</h2>
		<div class="about-content">
			<div class="about-text">
				<p>Программа AI SANA — инициатива Министерства науки и высшего образования для внедрения передовых технологий искусственного интеллекта в образование.</p>
				<p>Охватывает 100 000 студентов, стимулирует создание DeepTech-стартапов, развитие ИИ-компетенций и технологического предпринимательства.</p>
				<p>Проходит в 3 этапа: массовая подготовка (650 тыс студентов), изучение ML и AI бизнеса, акселерация 1.5 тыс стартапов с поддержкой экспертов Stanford, Imperial и King's College.</p>
			</div>
			<div class="about-image">
				<img src="<?php echo Yii::app()->request->baseUrl; ?>/uploads/aisana.png" alt="AI SANA" />
			</div>
		</div>
	</section>
	
	<section class="container contacts-section" id="contacts-section">
		<h2 class="section-title">Контакты</h2>
		<p>По вопросам сотрудничества и участия в AI Sana пишите</p>
		<div class="social-links">
			<a href="https://facebook.com" target="_blank" class="social-link">
				<img src="<?php echo Yii::app()->request->baseUrl; ?>/uploads/facebook.png" alt="Facebook" />
			</a>
			<a href="https://instagram.com" target="_blank" class="social-link">
				<img src="<?php echo Yii::app()->request->baseUrl; ?>/uploads/instagram.png" alt="Instagram" />
			</a>
			<a href="https://youtube.com" target="_blank" class="social-link">
				<img src="<?php echo Yii::app()->request->baseUrl; ?>/uploads/youtube.png" alt="YouTube" />
			</a>
			<a href="https://github.com/vnikonv/aisana" target="_blank" class="social-link">
				<img src="<?php echo Yii::app()->request->baseUrl; ?>/uploads/github.png" alt="GitHub" />
			</a>
			<a href="mailto:mail@ku.edu.kz" class="social-link">
				<img src="<?php echo Yii::app()->request->baseUrl; ?>/uploads/mail.png" alt="Email" />
			</a>
		</div>
		<div class="team-grid">
			<?php
			$team = array(
				array('name' => 'Леонтьева Оксана', 'phone' => '+7 705 192 48 05'),
				array('name' => 'Елнұр Аяған', 'phone' => '+7 701 765 4321'),
				array('name' => 'Камараев Тимур', 'phone' => '+7 747 511 01 60'),
			);
			foreach ($team as $member):
			?>
			<div class="team-card">
				<div class="team-content">
					<h3><?php echo CHtml::encode($member['name']); ?></h3>
					<p><?php echo CHtml::encode($member['phone']); ?></p>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
	</section>
	
	<footer class="main-footer">
		<div class="container footer-content">
			<p class="footer-text">© 2025 AISana, Козыбаев Университет. Разработка интеллектуальных решений для образования и города</p>
		</div>
	</footer>
</section>

