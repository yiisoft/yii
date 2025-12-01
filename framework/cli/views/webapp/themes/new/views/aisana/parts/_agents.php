<section class="agents-section" id="ai-agents-section">
    <div class="container agents-container">
        <h2 class="section-title">Наши AI-агенты</h2>

        <?php
        $agents = array(
            array(
                'title' => 'AI AntiFraud',
                'status' => 'testing',
                'statusText' => 'На тестировании',
                'description' => 'Поведенческий скоринг для antifraud.',
                'link' => 'https://aisanasku.streamlit.app/',
                'features' => array(
                    'Точность ~99%',
                    'SHAP, Gini, KS',
                    'Ручная проверка ↓70%',
                    'Обработка <30 сек'
                )
            ),
            array(
                'title' => 'CheckDoc',
                'status' => 'testing',
                'statusText' => 'Тестирование',
                'description' => 'AI‑доктор для диагностики и рекомендаций.',
                'link' => 'https://checkdoc.streamlit.app/',
                'features' => array(
                    'Анализ симптомов за 5 минут',
                    '1000+ пациентов в день',
                    'Снижение затрат в 2 раза',
                    'Точность до 90%'
                )
            ),
            array(
                'title' => 'УниЭксперт',
                'status' => 'development',
                'statusText' => 'В разработке',
                'description' => 'AI для поиска нормативных документов.',
                'link' => 'https://uniexpert.streamlit.app/',
                'features' => array(
                    'RAG‑архитектура',
                    'Экономия времени на 70%',
                    'Интеграция с Telegram',
                    'Охват 80% сотрудников'
                )
            ),
            array(
                'title' => 'AI для транспорта',
                'status' => 'testing',
                'statusText' => 'Тестирование',
                'description' => 'Оптимизация городских маршрутов.',
                'link' => 'https://t.me/ai_transport_bot',
                'features' => array(
                    'Анализ GPS‑данных и камер',
                    'Топливо ↓10%',
                    'Пассажиропоток ↑15%',
                    'Маршруты в реальном времени'
                )
            ),
            array(
                'title' => 'Антикоррупционный бот',
                'status' => 'deployed',
                'statusText' => 'Внедрён',
                'description' => 'Чат‑бот по вопросам коррупции.',
                'link' => 'https://truechat.ku.edu.kz/',
                'features' => array(
                    'Уведомления и сценарии',
                    'Обратная связь −50%',
                    'Вовлечённость ×3',
                    'Снижение рисков'
                )
            )
        );
        ?>

        <div class="carousel-wrapper">
            <div class="swiper agents-carousel">
                <div class="swiper-wrapper">
                    <?php foreach ($agents as $index => $agent): ?>
                        <div class="swiper-slide">
                            <div class="agent-card <?php echo 'status-' . $agent['status']; ?>">
                                <div class="agent-status <?php echo 'status-' . $agent['status']; ?>">
                                    <?php echo $agent['statusText']; ?>
                                </div>
                                <h3 class="agent-title"><?php echo CHtml::encode($agent['title']); ?></h3>
                                <p class="agent-description"><?php echo CHtml::encode($agent['description']); ?></p>

                                <ul class="agent-features">
                                    <?php foreach ($agent['features'] as $feature): ?>
                                        <li><?php echo CHtml::encode($feature); ?></li>
                                    <?php endforeach; ?>
                                </ul>

                                <a href="<?php echo CHtml::encode($agent['link']); ?>"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="agent-btn">
                                    Перейти к проекту
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <button class="custom-arrow prev agents-nav-prev" type="button" aria-label="Предыдущий">
                <span class="arrow-icon">«</span>
            </button>
            <button class="custom-arrow next agents-nav-next" type="button" aria-label="Следующий">
                <span class="arrow-icon">»</span>
            </button>
        </div>
    </div>
</section>
<script>
(function () {
    function initSwiper() {
        if (typeof Swiper === 'undefined') {
            console.error('Swiper не найден');
            return;
        }

        var slidesCount = <?php echo count($agents); ?>;

        if (window.agentsSwiper && typeof window.agentsSwiper.destroy === 'function') {
            window.agentsSwiper.destroy(true, true);
        }

        window.agentsSwiper = new Swiper('.agents-carousel', {
            loop: slidesCount > 1,
            centeredSlides: true,
            grabCursor: true,
            speed: 700,

            slidesPerView: 1,
            spaceBetween: 24,

            breakpoints: {
                768: {
                    slidesPerView: 2,
                    spaceBetween: 30
                },
                1200: {
                    slidesPerView: 3,
                    spaceBetween: 40
                },
                1600: {
                    slidesPerView: 3,
                    spaceBetween: 40
                }
            },

            navigation: {
                nextEl: '.agents-nav-next',
                prevEl: '.agents-nav-prev'
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSwiper);
    } else {
        initSwiper();
    }
})();
</script>
