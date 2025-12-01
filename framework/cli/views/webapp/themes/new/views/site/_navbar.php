<nav class="nav">
	<div class="brand">
		<img src="<?php echo Yii::app()->request->baseUrl; ?>/uploads/logo.png" alt="AI Sana logo" class="logo" />
	</div>
	<div class="links" id="navLinks">
		<a href="<?php echo Yii::app()->createUrl('aisana/index'); ?>">
			<img src="<?php echo Yii::app()->request->baseUrl; ?>/components/icons/home.svg" alt="Иконка Главная страница" class="icon" />
			Главная
		</a>
		<a href="<?php echo Yii::app()->createUrl('aisana/index'); ?>#about-program-section">
			<img src="<?php echo Yii::app()->request->baseUrl; ?>/components/icons/question.svg" alt="Иконка О программе" class="icon" />
			О программе
		</a>
		<a href="<?php echo Yii::app()->createUrl('aisana/index'); ?>#ai-agents-section">
			<img src="<?php echo Yii::app()->request->baseUrl; ?>/components/icons/ai-agents.svg" alt="Иконка AI-агенты" class="icon" />
			AI-агенты
		</a>
		<a href="<?php echo Yii::app()->createUrl('aisana/news'); ?>">
			<img src="<?php echo Yii::app()->request->baseUrl; ?>/components/icons/news.svg" alt="Иконка Новости" class="icon" />
			Новости
		</a>
        <a href="<?php echo Yii::app()->createUrl('aisana/courses'); ?>#contacts-section">
            <img src="<?php echo Yii::app()->request->baseUrl; ?>/components/icons/news.svg" alt="Иконка Контакты" class="icon" />
            Курсы
        </a>
		<a href="<?php echo Yii::app()->createUrl('aisana/index'); ?>#contacts-section">
			<img src="<?php echo Yii::app()->request->baseUrl; ?>/components/icons/contacts.svg" alt="Иконка Контакты" class="icon" />
			Контакты
		</a>

	</div>
	<div class="burger" id="burgerMenu">
		<span></span>
		<span></span>
		<span></span>
	</div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
	const burger = document.getElementById('burgerMenu');
	const links = document.getElementById('navLinks');
	
	if (burger && links) {
		burger.addEventListener('click', function() {
			links.classList.toggle('open');
			burger.querySelectorAll('span').forEach(span => {
				span.classList.toggle('active');
			});
		});
	}
});
</script>

