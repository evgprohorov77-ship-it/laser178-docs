<?php
/**
 * Plugin Name: Laser178 Site Content
 * Description: Создает страницы, меню, калькуляторы и прайс для laser178.ru
 * Version: 2.2
 * Author: Hermes
 */

if (!defined('ABSPATH')) exit;

add_action('wp_head', 'l178_meta_verifications');
function l178_meta_verifications() {
    echo '<meta name="yandex-verification" content="d14b5b5c9bd82699" />' . "\n";
    echo '<meta name="google-site-verification" content="oy7Re28KhzfEXISiY70gWPe2rR6CboRnENtSTN-InAE" />' . "\n";
}

add_action('wp_enqueue_scripts', 'l178_enqueue_styles');
function l178_enqueue_styles() {
    wp_register_style('l178-site-styles', false);
    wp_enqueue_style('l178-site-styles');
    $css = "
        :root {
            color-scheme: dark !important;
            --laser-bg: #0a0a0a !important;
            --laser-card: #111 !important;
            --laser-border: #1f1f1f !important;
            --laser-accent: #0ea5e9 !important;
            --laser-cyan: #0ea5e9 !important;
            --laser-text: #e5e5e5 !important;
            --laser-text-muted: #888 !important;
        }
        body, .site-content, .elementor-section-wrap, .elementor-inner, .elementor-site-main, .content-area, .site-main, .page-content, .entry-content, .hello-elementor, .elementor-default {
            background: #0a0a0a !important;
            color: #e5e5e5 !important;
        }
        .laser-card-centered { text-align: center !important; }
        .laser-card-centered h3, .laser-card-centered p { text-align: center !important; }
        .laser-card .laser-icon { display: inline-flex; }
    ";
    wp_add_inline_style('l178-site-styles', $css);
}

function l178_create_pages() {
    $pages = [
        'home' => ['title' => 'Главная', 'slug' => 'home'],
        'services' => ['title' => 'Услуги', 'slug' => 'services'],
        'price' => ['title' => 'Прайс', 'slug' => 'price'],
        'calculator' => ['title' => 'Калькулятор', 'slug' => 'calculator'],
        'laser-calculator' => ['title' => 'Калькулятор лазерной очистки', 'slug' => 'laser-calculator'],
        'contacts' => ['title' => 'Контакты', 'slug' => 'contacts'],
        'about' => ['title' => 'О нас', 'slug' => 'about'],
        'works' => ['title' => 'Наши работы', 'slug' => 'works'],
        'booking' => ['title' => 'Запись онлайн', 'slug' => 'booking'],
    ];
    $ids = [];
    foreach ($pages as $key => $p) {
        $existing = get_page_by_path($p['slug']);
        if ($existing) {
            $ids[$key] = $existing->ID;
        } else {
            $ids[$key] = wp_insert_post([
                'post_title' => $p['title'],
                'post_name' => $p['slug'],
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_content' => '',
            ]);
        }
    }
    update_option('show_on_front', 'page');
    update_option('page_on_front', $ids['home']);
    
    $menu_name = 'Главное меню';
    $menu = wp_get_nav_menu_object($menu_name);
    if (!$menu) {
        $menu_id = wp_create_nav_menu($menu_name);
    } else {
        $menu_id = $menu->term_id;
        $items = wp_get_nav_menu_items($menu_id);
        if ($items) {
            foreach ($items as $item) {
                wp_delete_post($item->ID, true);
            }
        }
    }
    $menu_items = [
        ['title' => 'Главная', 'page' => 'home'],
        ['title' => 'Услуги', 'page' => 'services'],
        ['title' => 'Прайс', 'page' => 'price'],
        ['title' => 'Калькулятор', 'page' => 'calculator'],
        ['title' => 'Наши работы', 'page' => 'works'],
        ['title' => 'Контакты', 'page' => 'contacts'],
        ['title' => 'О нас', 'page' => 'about'],
    ];
    foreach ($menu_items as $mi) {
        wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-title' => $mi['title'],
            'menu-item-object-id' => $ids[$mi['page']],
            'menu-item-object' => 'page',
            'menu-item-type' => 'post_type',
            'menu-item-status' => 'publish',
        ]);
    }
    $locations = get_theme_mod('nav_menu_locations');
    if (!is_array($locations)) $locations = [];
    $locations['menu-1'] = $menu_id;
    set_theme_mod('nav_menu_locations', $locations);

    update_home_content($ids['home']);
    update_services_content($ids['services']);
    update_price_content($ids['price']);
    update_calculator_content($ids['calculator']);
    update_laser_calculator_content($ids['laser-calculator']);
    update_contacts_content($ids['contacts']);
    update_about_content($ids['about']);
    update_works_content($ids['works']);
    update_booking_content($ids['booking']);
}
register_activation_hook(__FILE__, 'l178_create_pages');

function l178_setup_and_update() {
    l178_create_pages();
}

function update_home_content($id) {
    $content = '<div class="laser-hero">
    <div class="laser-hero-content">
        <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
            <div class="laser-section-label">// ЛАЗЕРНАЯ ОЧИСТКА И АНТИКОР</div>
            <h1>Защита авто<br><span>проще, чем кажется</span></h1>
            <p>Лазерная очистка, антикоррозийная обработка, арматурные работы и комплексная защита автомобиля. От диагностики до гарантированного результата.</p>
            <div style="display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 40px;">
                <a class="btn-primary" href="/calculator">Рассчитать стоимость</a>
                <a class="btn-secondary" href="/services">Посмотреть услуги</a>
            </div>
            <div class="laser-hero-terminal">
                <div class="line">Статус: оборудование готово</div>
                <div class="line">Материалы: Dinitrol, ONB, MasterWAX</div>
                <div class="line">Гарантия: до 5 лет</div>
                <div class="line">Ответ: < 15 мин</div>
            </div>
        </div>
    </div>
</div>

<div style="padding: 80px 20px; max-width: 1000px; margin: 0 auto;">
    <div class="laser-section-label">// ПОЧЕМУ МЫ</div>
    <h2 style="font-size: 36px; margin-bottom: 40px; text-align: center;">ПОЛНЫЙ ЦИКЛ ЗАЩИТЫ</h2>
    <div class="laser-cycle-grid">
        <div class="laser-cycle-card">
            <div class="laser-tag">01</div>
            <h3>Диагностика</h3>
            <p style="flex-grow: 1;">Осматриваем кузов, скрытые полости, днище и арки. Определяем степень коррозии и подбираем технологию.</p>
        </div>
        <div class="laser-cycle-card">
            <div class="laser-tag">02</div>
            <h3>Разборка</h3>
            <p style="flex-grow: 1;">Снимаем элементы, необходимые для доступа к обрабатываемым зонам: бампера, подкрылки, защиты картера.</p>
        </div>
        <div class="laser-cycle-card">
            <div class="laser-tag">03</div>
            <h3>Очистка</h3>
            <p style="flex-grow: 1;">Лазерная или механическая очистка от ржавчины, старого покрытия и загрязнений.</p>
        </div>
        <div class="laser-cycle-card">
            <div class="laser-tag">04</div>
            <h3>Консервация</h3>
            <p style="flex-grow: 1;">Наносим антикоррозийные материалы, обрабатываем днище, арки и скрытые полости.</p>
        </div>
    </div>
</div>

<div style="padding: 80px 20px; max-width: 1000px; margin: 0 auto;">
    <div class="laser-section-label">// ПРЕИМУЩЕСТВА</div>
    <h2 style="font-size: 36px; margin-bottom: 40px; text-align: center;">ПОЧЕМУ ВЫБИРАЮТ НАС</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; text-align: center;">
        <div class="laser-card">
            <div style="width:56px;height:56px;margin:0 auto 16px auto;display:flex;align-items:center;justify-content:center;border-radius:12px;background:transparent;color:var(--laser-cyan);border:1px solid var(--laser-cyan);box-shadow:0 4px 20px rgba(14,165,233,0.15);"><img src="/wp-content/uploads/icon-shield.png?v=2" alt="" width="28" height="28" style="display:block;"/></div>
            <h3>Гарантия до 5 лет</h3>
            <p>Фиксируем результат документально и поддерживаем после обслуживания.</p>
        </div>
        <div class="laser-card">
            <div style="width:56px;height:56px;margin:0 auto 16px auto;display:flex;align-items:center;justify-content:center;border-radius:12px;background:transparent;color:var(--laser-cyan);border:1px solid var(--laser-cyan);box-shadow:0 4px 20px rgba(14,165,233,0.15);"><img src="/wp-content/uploads/icon-lightning.png?v=2" alt="" width="28" height="28" style="display:block;"/></div>
            <h3>Лазер без абразива</h3>
            <p>Очистка без пыли, песка и истончения металла.</p>
        </div>
        <div class="laser-card" style="text-align:center;">
            <div style="width:56px;height:56px;margin:0 auto 16px auto;display:flex;align-items:center;justify-content:center;border-radius:12px;background:transparent;color:var(--laser-cyan);border:1px solid var(--laser-cyan);box-shadow:0 4px 20px rgba(14,165,233,0.15);"><img src="/wp-content/uploads/icon-flask.png?v=2" alt="" width="28" height="28" style="display:block;"/></div>
            <h3>Проверенные<br>материалы</h3>
            <p>Работаем с Dinitrol,<br>ONB Master и MasterWAX.</p>
        </div>
        <div class="laser-card">
            <div style="width:56px;height:56px;margin:0 auto 16px auto;display:flex;align-items:center;justify-content:center;border-radius:12px;background:transparent;color:var(--laser-cyan);border:1px solid var(--laser-cyan);box-shadow:0 4px 20px rgba(14,165,233,0.15);"><img src="/wp-content/uploads/icon-wrench.png?v=2" alt="" width="28" height="28" style="display:block;"/></div>
            <h3>Любая сложность</h3>
            <p>Опыт работы с кузовами разных марок и степеней повреждения.</p>
        </div>
    </div>
</div>

<div style="padding: 80px 20px; max-width: 1200px; margin: 0 auto;">
    <div class="laser-section-label">// РАБОТЫ</div>
    <h2 style="font-size: 36px; margin-bottom: 20px;">НАШИ РАБОТЫ</h2>
    <p style="color: var(--laser-text-muted); margin-bottom: 40px;">Реальные результаты лазерной очистки и антикоррозийной обработки.</p>
    <div class="laser-works-grid" data-lightbox="works">
        <div class="laser-works-item" role="button" tabindex="0" data-full="/wp-content/uploads/work-01.jpg"><img src="/wp-content/uploads/work-01.jpg" alt="Работа 1" loading="lazy"/></div>
        <div class="laser-works-item" role="button" tabindex="0" data-full="/wp-content/uploads/work-02.jpg"><img src="/wp-content/uploads/work-02.jpg" alt="Работа 2" loading="lazy"/></div>
        <div class="laser-works-item" role="button" tabindex="0" data-full="/wp-content/uploads/work-03.jpg"><img src="/wp-content/uploads/work-03.jpg" alt="Работа 3" loading="lazy"/></div>
        <div class="laser-works-item" role="button" tabindex="0" data-full="/wp-content/uploads/work-04.jpg"><img src="/wp-content/uploads/work-04.jpg" alt="Работа 4" loading="lazy"/></div>
        <div class="laser-works-item" role="button" tabindex="0" data-full="/wp-content/uploads/work-05.jpg"><img src="/wp-content/uploads/work-05.jpg" alt="Работа 5" loading="lazy"/></div>
        <div class="laser-works-item" role="button" tabindex="0" data-full="/wp-content/uploads/work-06.jpg"><img src="/wp-content/uploads/work-06.jpg" alt="Работа 6" loading="lazy"/></div>
        <div class="laser-works-item" role="button" tabindex="0" data-full="/wp-content/uploads/work-07.jpg"><img src="/wp-content/uploads/work-07.jpg" alt="Работа 7" loading="lazy"/></div>
        <div class="laser-works-item" role="button" tabindex="0" data-full="/wp-content/uploads/work-08.jpg"><img src="/wp-content/uploads/work-08.jpg" alt="Работа 8" loading="lazy"/></div>
        <div class="laser-works-item" role="button" tabindex="0" data-full="/wp-content/uploads/work-09.jpg"><img src="/wp-content/uploads/work-09.jpg" alt="Работа 9" loading="lazy"/></div>
    </div>
    <div class="laser-lightbox" id="laserLightbox" aria-hidden="true">
        <button class="laser-lightbox-close" aria-label="Закрыть">&times;</button>
        <button class="laser-lightbox-prev" aria-label="Назад">&#10094;</button>
        <button class="laser-lightbox-next" aria-label="Вперёд">&#10095;</button>
        <div class="laser-lightbox-img-wrap"><img src="" alt="" id="laserLightboxImg"/></div>
    </div>
</div>

<div style="padding: 80px 20px; max-width: 1200px; margin: 0 auto;">
    <div class="laser-section-label">// ОТЗЫВЫ</div>
    <h2 style="font-size: 36px; margin-bottom: 40px;">ЧТО ГОВОРЯТ КЛИЕНТЫ</h2>
    <div style="display: grid; gap: 20px;" class="laser-reviews-grid">
        <div class="laser-card" style="text-align: left; display: flex; flex-direction: column;">
            <div style="color: #fbbf24; margin-bottom: 16px; font-size: 18px;">★★★★★</div>
            <p style="color: var(--laser-text-muted); margin: 0 0 20px 0; flex-grow: 1;">Машину сначала осмотрели бесплатно и подробно рассказали о состоянии кузова и как будет происходить процесс. Очень понравилось отношение и то, что не пытались продать лишние услуги. Спасибо большое!</p>
            <div style="display: flex; align-items: center; gap: 10px; font-weight: 700; color: var(--laser-cyan); font-size: 15px;"><img src="/wp-content/uploads/avatar-natalya.png" alt="" width="36" height="36" style="border-radius: 50%; display: block;"/>— Наталья</div>
        </div>
        <div class="laser-card" style="text-align: left; display: flex; flex-direction: column;">
            <div style="color: #fbbf24; margin-bottom: 16px; font-size: 18px;">★★★★★</div>
            <p style="color: var(--laser-text-muted); margin: 0 0 20px 0; flex-grow: 1;">С первого взгляда сразу понял, что Евгений настоящий профессионал своего дела! Очень понравился его подход, всё по делу, всегда был на связи! Всем рекомендую!!!</p>
            <div style="display: flex; align-items: center; gap: 10px; font-weight: 700; color: var(--laser-cyan); font-size: 15px;"><img src="/wp-content/uploads/avatar-alexander.png" alt="" width="36" height="36" style="border-radius: 50%; display: block;"/>— Александр Прокофьев</div>
        </div>
        <div class="laser-card" style="text-align: left; display: flex; flex-direction: column;">
            <div style="color: #fbbf24; margin-bottom: 16px; font-size: 18px;">★★★★★</div>
            <p style="color: var(--laser-text-muted); margin: 0 0 20px 0; flex-grow: 1;">У меня на Паджерике была проблема с коррозией VIN номера, ребята справились на ура, лучше чем абразивами тереть, ещё и антикором закрыли. Цена доступная. Рекомендую.</p>
            <div style="display: flex; align-items: center; gap: 10px; font-weight: 700; color: var(--laser-cyan); font-size: 15px;"><img src="/wp-content/uploads/avatar-al.png" alt="" width="36" height="36" style="border-radius: 50%; display: block;"/>— Аль</div>
        </div>
        <div class="laser-card" style="text-align: left; display: flex; flex-direction: column;">
            <div style="color: #fbbf24; margin-bottom: 16px; font-size: 18px;">★★★★★</div>
            <p style="color: var(--laser-text-muted); margin: 0 0 20px 0; flex-grow: 1;">Долго читала и сравнивала виды очистки от коррозии и всё сводилось к тому, что лазером эффективнее. Спасибо вам, Евгений, за качественную работу! Буду вас рекомендовать!</p>
            <div style="display: flex; align-items: center; gap: 10px; font-weight: 700; color: var(--laser-cyan); font-size: 15px;"><img src="/wp-content/uploads/avatar-nadezhda.png" alt="" width="36" height="36" style="border-radius: 50%; display: block;"/>— Надежда Штейнбах</div>
        </div>
    </div>
</div>

<div style="padding: 80px 20px; max-width: 1200px; margin: 0 auto;">
    <div class="laser-section-label">// УСЛУГИ</div>
    <h2 style="font-size: 36px; margin-bottom: 40px;">ЧТО ДЕЛАЕМ</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
        <div class="laser-card">
            <h3>Арматурные работы</h3>
            <p>Снятие и установка элементов для доступа к обрабатываемым зонам.</p>
            <a href="/services#disassembly" class="btn-secondary" style="margin-top: 15px; display: inline-block;">Подробнее</a>
        </div>
        <div class="laser-card">
            <h3>Лазерная очистка</h3>
            <p>Удаление ржавчины, старой краски, масел и окислов без повреждения металла.</p>
            <a href="/services#laser" class="btn-secondary" style="margin-top: 15px; display: inline-block;">Подробнее</a>
        </div>
        <div class="laser-card">
            <h3>Антикоррозийная обработка</h3>
            <p>Защита кузова, днища, арок и скрытых полостей от коррозии.</p>
            <a href="/services#anticor" class="btn-secondary">Подробнее</a>
        </div>
    </div>
</div>

<div style="padding: 80px 20px; max-width: 1200px; margin: 0 auto;">
    <div class="laser-section-label">// КАЛЬКУЛЯТОР</div>
    <h2 style="font-size: 36px; margin-bottom: 20px;">РАССЧИТАЙТЕ СТОИМОСТЬ ОНЛАЙН</h2>
    <p style="margin-bottom: 30px; color: var(--laser-text-muted);">Предварительный расчёт защиты и арматурных работ.</p>
    <a class="btn-primary" href="/calculator">Рассчитать</a>
</div>';
    wp_update_post(['ID' => $id, 'post_content' => $content]);
}

function update_services_content($id) {
    $content = '<div style="padding: 80px 20px; max-width: 1200px; margin: 0 auto;">
    <div class="laser-section-label">// УСЛУГИ</div>
    <h1 style="font-size: 42px; margin-bottom: 20px;">НАШИ УСЛУГИ</h1>
    <p style="color: var(--laser-text-muted); margin-bottom: 40px;">Комплексная защита автомобиля от коррозии и механических повреждений.</p>

    <div id="disassembly" class="laser-card" style="margin-bottom: 30px;">
        <h2>Арматурные работы</h2>
        <p>Снятие и установка бамперов, подкрылков, защит картера, топливного бака, подрамника и других элементов для доступа к обрабатываемым зонам.</p>
    </div>

    <div id="laser" class="laser-card" style="margin-bottom: 30px;">
        <h2>Лазерная очистка</h2>
        <p>Лазерная очистка удаляет коррозию, старую краску, масла и окислы без повреждения металла. В отличие от пескоструя, не истончает деталь и не оставляет абразивной пыли.</p>
        <p>Подходит для:</p>
        <ul>
            <li>Локальной очистки порогов, арок и отдельных элементов</li>
            <li>Очистки днища и подрамника</li>
            <li>Подготовки поверхности перед антикором</li>
        </ul>
    </div>

    <div id="anticor" class="laser-card" style="margin-bottom: 30px;">
        <h2>Антикоррозийная обработка</h2>
        <p>Обработка днища, арок и кузова антикоррозийными материалами. Используем Dinitrol, ONB Master и MasterWAX.</p>
    </div>

    <div class="laser-card">
        <h2>Консервация скрытых полостей</h2>
        <p>Обработка внутренних полостей кузова, дверей, стоек и лонжеронов восковыми антикоррозийными составами.</p>
    </div>
</div>
[l178_promo]';
    wp_update_post(['ID' => $id, 'post_content' => $content]);
}

function update_price_content($id) {
    $content = '<div style="padding: 80px 20px; max-width: 1200px; margin: 0 auto;">
    <div class="laser-section-label">// ПРАЙС</div>
    <h1 style="font-size: 42px; margin-bottom: 20px;">ЦЕНЫ</h1>
    <p style="color: var(--laser-text-muted); margin-bottom: 40px;">Ориентировочная стоимость услуг. Точная цена определяется после осмотра.</p>
    [l178_price]
</div>';
    wp_update_post(['ID' => $id, 'post_content' => $content]);
}

function update_calculator_content($id) {
    $content = '<div style="padding: 80px 20px; max-width: 1200px; margin: 0 auto;">
    <div class="laser-section-label">// КАЛЬКУЛЯТОР</div>
    <h1 style="font-size: 42px; margin-bottom: 20px;">КАЛЬКУЛЯТОР СТОИМОСТИ</h1>
    <p style="color: var(--laser-text-muted); margin-bottom: 40px;">Выберите параметры автомобиля и услуги — получите предварительную стоимость.</p>
    [l178_calculator]
</div>';
    wp_update_post(['ID' => $id, 'post_content' => $content]);
}

function update_laser_calculator_content($id) {
    $content = '<div style="padding: 80px 20px; max-width: 1200px; margin: 0 auto;">
    <div class="laser-section-label">// КАЛЬКУЛЯТОР ЛАЗЕРНОЙ ОЧИСТКИ</div>
    <h1 style="font-size: 42px; margin-bottom: 20px;">ЛАЗЕРНАЯ ОЧИСТКА</h1>
    <p style="color: var(--laser-text-muted); margin-bottom: 40px;">Расчёт стоимости лазерной очистки по зонам, площади и сложности.</p>
    [l178_laser_calculator]
</div>';
    wp_update_post(['ID' => $id, 'post_content' => $content]);
}

function update_contacts_content($id) {
    $content = '<div style="padding: 80px 20px; max-width: 1200px; margin: 0 auto;">
    <div class="laser-section-label">// КОНТАКТЫ</div>
    <h1 style="font-size: 42px; margin-bottom: 20px;">СВЯЖИТЕСЬ С НАМИ</h1>
    <p style="color: var(--laser-text-muted); margin-bottom: 40px;">Задайте вопрос или запишитесь на осмотр.</p>
    <div class="laser-card" style="max-width: 600px;">
        <p><strong>Адрес:</strong> Санкт-Петербург, Грузинская, 3 корп. 1П<br>(въезд с ул. Салова)</p>
        <p><strong>Телефон:</strong> <a href="tel:+79810971505">+7 981 097-15-05</a></p>
        <p><strong>Email:</strong> <a href="mailto:info@laser178.ru">info@laser178.ru</a></p>
        <p><strong>Режим работы:</strong><br>Пн–Пт: 10:00 – 19:00<br>Сб: 10:00 – 15:00</p>
    </div>
</div>';
    wp_update_post(['ID' => $id, 'post_content' => $content]);
}

function update_about_content($id) {
    $content = '<div style="padding: 80px 20px; max-width: 1200px; margin: 0 auto;">
    <div class="laser-section-label">// О НАС</div>
    <h1 style="font-size: 42px; margin-bottom: 20px;">О КОМПАНИИ</h1>
    <p style="margin-bottom: 20px;">Laser178 — это специализированный центр по защите автомобилей от коррозии. Мы работаем с лазерной очисткой, антикоррозийной обработкой, арматурными работами и консервацией скрытых полостей.</p>
    <p style="margin-bottom: 20px;">Используем профессиональные материалы Dinitrol, ONB Master и MasterWAX. Гарантируем качество выполненных работ и подбор решения под состояние каждого автомобиля.</p>
</div>';
    wp_update_post(['ID' => $id, 'post_content' => $content]);
}

function update_works_content($id) {
    $content = '<div style="padding: 80px 20px; max-width: 1200px; margin: 0 auto;">
    <div class="laser-section-label">// ПОРТФОЛИО</div>
    <h1 style="font-size: 42px; margin-bottom: 20px;">НАШИ РАБОТЫ</h1>
    <p style="color: var(--laser-text-muted); margin-bottom: 40px;">Реальные кейсы по лазерной очистке и антикоррозийной обработке.</p>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
        <div class="laser-card">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px;">
                <div style="background: #1f1f1f; border-radius: 6px; min-height: 120px; display: flex; align-items: center; justify-content: center; color: var(--laser-text-muted); font-size: 12px;">До</div>
                <div style="background: #1f1f1f; border-radius: 6px; min-height: 120px; display: flex; align-items: center; justify-content: center; color: var(--laser-text-muted); font-size: 12px;">После</div>
            </div>
            <h3>Lada Vesta</h3>
            <p style="color: var(--laser-text-muted); font-size: 14px; margin-bottom: 10px;">Лазерная очистка днища и арок</p>
            <p><strong>Срок:</strong> 1 день</p>
            <p><strong>Стоимость:</strong> от 18 000 ₽</p>
        </div>

        <div class="laser-card">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px;">
                <div style="background: #1f1f1f; border-radius: 6px; min-height: 120px; display: flex; align-items: center; justify-content: center; color: var(--laser-text-muted); font-size: 12px;">До</div>
                <div style="background: #1f1f1f; border-radius: 6px; min-height: 120px; display: flex; align-items: center; justify-content: center; color: var(--laser-text-muted); font-size: 12px;">После</div>
            </div>
            <h3>Kia Rio</h3>
            <p style="color: var(--laser-text-muted); font-size: 14px; margin-bottom: 10px;">Комплекс антикор + скрытые полости</p>
            <p><strong>Срок:</strong> 2 дня</p>
            <p><strong>Стоимость:</strong> от 45 000 ₽</p>
        </div>

        <div class="laser-card">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px;">
                <div style="background: #1f1f1f; border-radius: 6px; min-height: 120px; display: flex; align-items: center; justify-content: center; color: var(--laser-text-muted); font-size: 12px;">До</div>
                <div style="background: #1f1f1f; border-radius: 6px; min-height: 120px; display: flex; align-items: center; justify-content: center; color: var(--laser-text-muted); font-size: 12px;">После</div>
            </div>
            <h3>Skoda Octavia</h3>
            <p style="color: var(--laser-text-muted); font-size: 14px; margin-bottom: 10px;">Удаление старого антикора и перекрытие</p>
            <p><strong>Срок:</strong> 2 дня</p>
            <p><strong>Стоимость:</strong> от 52 000 ₽</p>
        </div>
    </div>
</div>';
    wp_update_post(['ID' => $id, 'post_content' => $content]);
}

function update_booking_content($id) {
    $content = '<div style="padding: 80px 20px; max-width: 800px; margin: 0 auto;">
    <div class="laser-section-label">// ЗАПИСЬ</div>
    <h1 style="font-size: 42px; margin-bottom: 20px;">ЗАПИШИСЬ НА ОСМОТР</h1>
    <p style="color: var(--laser-text-muted); margin-bottom: 40px;">Оставьте заявку — мы перезвоним и подберём удобное время.</p>
    [l178_booking_form]
</div>';
    wp_update_post(['ID' => $id, 'post_content' => $content]);
}

function l178_calculator_shortcode() {
    ob_start();
    ?>
    <div id="l178-calc">
    <style>
        #l178-calc { --laser-bg: #0a0a0a; --laser-card: #111; --laser-border: #1f1f1f; --laser-accent: #0ea5e9; --laser-text: #e5e5e5; --laser-text-muted: #888; }
        .laser-dark #l178-calc { --laser-bg: #0a0a0a; --laser-card: #111; --laser-border: #1f1f1f; --laser-text: #e5e5e5; }
        .laser-light #l178-calc { --laser-bg: #f5f5f5; --laser-card: #fff; --laser-border: #ddd; --laser-text: #111; --laser-text-muted: #666; --laser-accent: #0284c7; }
        #l178-calc { background: var(--laser-bg); border: 1px solid var(--laser-border); border-radius: 8px; padding: 30px; color: var(--laser-text); max-width: 900px; margin: 0 auto; }
        #l178-calc .calc-step { display: none; }
        #l178-calc .calc-step.active { display: block; }
        #l178-calc .calc-step-title { font-size: 20px; margin-bottom: 24px; font-weight: 600; }
        #l178-calc .calc-group { margin-bottom: 24px; }
        #l178-calc label { display: block; margin-bottom: 10px; font-weight: 600; }
        #l178-calc select, #l178-calc input[type="text"] { width: 100%; padding: 12px; border-radius: 4px; border: 1px solid var(--laser-border); background: var(--laser-bg); color: var(--laser-text); }
        #l178-calc .checkbox-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; align-items: stretch; }
        #l178-calc .checkbox-grid label { font-weight: normal; display: flex; align-items: flex-start; gap: 8px; cursor: pointer; background: var(--laser-bg); border: 1px solid var(--laser-border); border-radius: 4px; padding: 12px; transition: all 0.2s ease; min-height: 80px; }
        #l178-calc .checkbox-grid label:hover { border-color: var(--laser-accent); }
        #l178-calc .checkbox-grid label:has(input:checked) { border-color: var(--laser-accent); background: rgba(14, 165, 233, 0.1); }
        #l178-calc .checkbox-grid input[type="checkbox"] { width: auto; margin-top: 2px; }
        #l178-calc .laser-services-list { display: flex; flex-direction: column; gap: 12px; }
        #l178-calc .laser-service-item { border: 1px solid var(--laser-border); border-radius: 4px; padding: 12px; background: var(--laser-bg); transition: all 0.2s ease; }
        #l178-calc .laser-service-item:has(input:checked) { border-color: var(--laser-accent); background: rgba(14, 165, 233, 0.1); }
        #l178-calc .laser-service-item label { font-weight: normal; display: flex; align-items: flex-start; gap: 8px; cursor: pointer; margin-bottom: 0; }
        #l178-calc .laser-service-item p { color: var(--laser-text-muted); font-size: 12px; margin: 6px 0 8px 24px; }
        #l178-calc .laser-service-item .calc-group { margin-left: 24px; margin-top: 0; margin-bottom: 0; }
        #l178-calc .laser-service-item .calc-group input { padding: 6px 10px; max-width: 120px; }
        #l178-calc .element-qty-wrap { display: flex; align-items: center; gap: 4px; margin-top: 6px; }
        #l178-calc .element-qty { width: 44px; padding: 6px 4px; border-radius: 4px; border: 1px solid var(--laser-border); background: var(--laser-bg); color: var(--laser-text); text-align: center; font-size: 14px; -moz-appearance: textfield; }
        #l178-calc .element-qty::-webkit-outer-spin-button, #l178-calc .element-qty::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        #l178-calc .element-qty-btn { width: 28px; height: 30px; border: 1px solid var(--laser-border); background: var(--laser-bg); color: var(--laser-text); border-radius: 4px; cursor: pointer; font-size: 16px; line-height: 1; display: flex; align-items: center; justify-content: center; }
        #l178-calc .element-qty-btn:hover { border-color: var(--laser-accent); color: var(--laser-accent); }
        #l178-calc .element-row { display: flex; flex-direction: column; align-items: flex-start; gap: 4px; width: 100%; }
        #l178-calc .element-row span { line-height: 1.4; }
        #l178-calc .calc-buttons { display: flex; justify-content: space-between; margin-top: 30px; }
        #l178-calc button { padding: 14px 28px; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; }
        #l178-calc .btn-primary { background: var(--laser-accent); color: #fff; box-shadow: 0 0 20px rgba(14, 165, 233, 0.3); }
        #l178-calc .btn-primary:hover { background: var(--laser-accent); opacity: 0.9; }
        #l178-calc .btn-secondary { background: transparent; border: 1px solid var(--laser-border); color: var(--laser-text); }
        #l178-calc .btn-secondary:hover { border-color: var(--laser-accent); color: var(--laser-accent); }
        #l178-calc .calc-modal { position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 10000; display: flex; align-items: center; justify-content: center; padding: 16px; }
        #l178-calc .calc-modal-content { background: var(--laser-bg); border: 1px solid var(--laser-border); border-radius: 8px; width: 100%; max-width: 700px; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 20px 60px rgba(0,0,0,0.5); }
        #l178-calc .calc-modal-header { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-bottom: 1px solid var(--laser-border); }
        #l178-calc .calc-modal-header h3 { margin: 0; font-size: 18px; }
        #l178-calc .calc-modal-close { background: transparent; border: none; color: var(--laser-text); font-size: 28px; line-height: 1; padding: 0; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: none; }
        #l178-calc .calc-modal-close:hover { color: var(--laser-accent); }
        #l178-calc .calc-modal-body { padding: 20px; overflow-y: auto; }
        #l178-calc .calc-modal-footer { display: flex; justify-content: flex-end; gap: 12px; padding: 16px 20px; border-top: 1px solid var(--laser-border); }
        #l178-calc .calc-modal-footer button { padding: 10px 20px; }
        @media (max-width: 768px) { #l178-calc .calc-modal-content { max-height: calc(100vh - 80px); } #l178-calc .calc-modal-footer { padding-bottom: 70px; } }
        #l178-calc .material-group h4 { margin: 0 0 12px 0; font-size: 16px; color: var(--laser-accent); border-bottom: 1px solid var(--laser-border); padding-bottom: 8px; }
        #l178-calc .calc-selected-preview { color: var(--laser-text-muted); font-size: 13px; }
        #l178-calc .calc-summary { background: var(--laser-bg); padding: 24px; border-radius: 8px; border: 1px solid var(--laser-border); margin-top: 20px; }
        #l178-calc .calc-summary h3 { margin-top: 0; color: #fff; }
        #l178-calc .calc-summary .total { font-size: 28px; color: var(--laser-accent); font-weight: 700; }
        #l178-calc .calc-summary ul { margin: 0 0 20px 0; padding-left: 20px; }
        #l178-calc .calc-summary li { margin-bottom: 8px; }
        @media (max-width: 640px) { #l178-calc .checkbox-grid { grid-template-columns: 1fr; } }
    </style>

    <h2 class="calc-step-title">Калькулятор стоимости обработки авто</h2>
    <div class="calc-step active" data-step="1">
        <div class="calc-group">
            <label>Выберите марку авто</label>
            <select id="calc-brand"><option value="">— Выберите марку —</option></select>
        </div>
        <div class="calc-group">
            <label>Выберите модель</label>
            <select id="calc-model" disabled><option value="">— Сначала выберите марку —</option></select>
        </div>
        <div class="calc-group">
            <label>Тип кузова</label>
            <select id="calc-body-type">
                <option value="" selected>— Выберите кузов —</option>
                <option value="Хэтчбек">Хэтчбек</option>
                <option value="Седан">Седан</option>
                <option value="Кроссовер">Кроссовер</option>
                <option value="Внедорожник">Внедорожник</option>
            </select>
        </div>
        <div class="calc-buttons"><span></span><button class="btn-primary" onclick="nextStep()">Далее</button></div>
    </div>

    <div class="calc-step" data-step="2">
        <div class="calc-group">
            <label>Выберите услуги</label>
            <div class="checkbox-grid" id="calc-services">
                <label><input type="checkbox" value="disassembly" data-base="0" data-type="disassembly"> Арматурные работы (снятие+установка)</label>
                <p style="color: var(--laser-text-muted); font-size: 12px; margin: -6px 0 8px 0;">Снятие и последующая установка элементов кузова для доступа к обрабатываемым поверхностям.</p>
                <label><input type="checkbox" value="preparation" data-base="4000"> Подготовка поверхности</label>
                <p style="color: var(--laser-text-muted); font-size: 12px; margin: -6px 0 8px 0;">Мойка, обезжиривание и подготовка деталей перед нанесением защитных покрытий.</p>
                <label><input type="checkbox" value="laser" data-base="0" data-type="laser"> Лазерная очистка</label>
                <p style="color: var(--laser-text-muted); font-size: 12px; margin: -6px 0 8px 0;">Удаление ржавчины, краски или старого антикора лазером без повреждения металла.</p>
                <label><input type="checkbox" value="anticor" data-base="0"> Антикоррозийная обработка (комплекс)</label>
            </div>
            <p id="calc-elements-preview" class="calc-selected-preview">Элементы не выбраны</p>
            <p id="calc-laser-preview" class="calc-selected-preview" style="display:none;">Лазерная очистка не выбрана</p>
        </div>

        <div id="calc-elements-modal" class="calc-modal" style="display:none;">
            <div class="calc-modal-content">
                <div class="calc-modal-header"><h3>Выберите элементы для снятия и установки</h3><button type="button" class="calc-modal-close" onclick="closeElementsModal()" aria-label="Закрыть">×</button></div>
                <div class="calc-modal-body"><div class="checkbox-grid" id="calc-elements"></div><p style="color: var(--laser-text-muted); font-size: 13px; margin-top: 12px;">Цены за комплект «снятие+установка», с учётом группы автомобиля и типа кузова.</p></div>
                <div class="calc-modal-footer"><button type="button" class="btn-secondary" onclick="closeElementsModal()">Отмена</button><button type="button" class="btn-primary" onclick="confirmElementsModal()">Готово</button></div>
            </div>
        </div>

        <div id="calc-laser-modal" class="calc-modal" style="display:none;">
            <div class="calc-modal-content">
                <div class="calc-modal-header"><h3>Расчёт лазерной очистки</h3><button type="button" class="calc-modal-close" onclick="closeLaserModal()" aria-label="Закрыть">×</button></div>
                <div class="calc-modal-body">
                    <div class="calc-group">
                        <label>Тип услуги</label>
                        <div class="laser-services-list" id="laser-modal-services">
                            <div class="laser-service-item">
                                <label><input type="checkbox" value="rust" data-rate="15000"> Очистка ржавчины</label>
                                <p>Удаляем коррозию и окислы с металла перед грунтовкой и антикором.</p>
                                <div class="calc-group" id="laser-modal-area-rust-group" style="display:none;">
                                    <label>Площадь обработки, м²</label>
                                    <input type="number" id="laser-modal-area-rust" value="0" min="0" step="0.1">
                                </div>
                            </div>
                            <div class="laser-service-item">
                                <label><input type="checkbox" value="paint" data-rate="11250"> Очистка краски</label>
                                <p>Снимаем старое лакокрасочное покрытие без повреждения металла.</p>
                                <div class="calc-group" id="laser-modal-area-paint-group" style="display:none;">
                                    <label>Площадь обработки, м²</label>
                                    <input type="number" id="laser-modal-area-paint" value="0" min="0" step="0.1">
                                </div>
                            </div>
                            <div class="laser-service-item">
                                <label><input type="checkbox" value="anticor" data-rate="16875"> Очистка старого антикор покрытия</label>
                                <p>Убираем высохший или повреждённый антикор перед нанесением нового слоя.</p>
                                <div class="calc-group" id="laser-modal-area-anticor-group" style="display:none;">
                                    <label style="font-size: 13px; margin-bottom: 4px;">Площадь, м²</label>
                                    <input type="number" id="laser-modal-area-anticor" min="0" step="0.1" value="0" placeholder="0">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="calc-group">
                        <label>Состояние коррозии</label>
                        <select id="laser-modal-condition">
                            <option value="1.0">Лёгкое (начальная стадия)</option>
                            <option value="1.5" selected>Среднее</option>
                            <option value="2.0">Тяжёлое (глубокая коррозия)</option>
                        </select>
                    </div>
                    <div class="calc-group">
                        <label>Защита металла</label>
                        <div class="checkbox-grid" id="laser-modal-addons">
                            <label><input type="checkbox" value="preparation" data-base="2500"> Подготовка поверхности</label>
                            <label><input type="checkbox" value="primer" data-base="3500"> Грунтовка</label>
                            <label><input type="checkbox" value="protective" data-base="4500"> Защитное покрытие после очистки</label>
                        </div>
                    </div>
                    <p style="color: var(--laser-text-muted); font-size: 13px; margin-top: 12px;">Цены для: <strong id="laser-modal-car-info"></strong></p>
                </div>
                <div class="calc-modal-footer"><button type="button" class="btn-secondary" onclick="closeLaserModal()">Отмена</button><button type="button" class="btn-primary" onclick="confirmLaserModal()">Готово</button></div>
            </div>
        </div>

        <div class="calc-group">
            <label>Степень обработки</label>
            <select id="calc-level">
                <option value="1">Базовая (1 зона)</option>
                <option value="1.5" selected>Стандартная (2 зоны)</option>
                <option value="2.2">Полная (все зоны)</option>
            </select>
            <small style="display: block; color: var(--laser-text-muted); font-size: 12px; margin-top: 8px; line-height: 1.4;">
                <strong>Базовая</strong> — 1 зона на выбор: днище, арки или скрытые полости.<br>
                <strong>Стандартная</strong> — комбинация 2-х зон: например, днище + арки.<br>
                <strong>Полная</strong> — комплексная обработка всех зон: днище, арки и скрытые полости.
            </small>
        </div>
        <div class="calc-group material-select">
            <label>Материал для обработки</label>
            <select id="calc-material">
                <option value="" selected>Выбор материала</option>
                <option value="dinitrol">Dinitrol</option>
                <option value="onb">ONB Master</option>
                <option value="masterwax">MasterWAX</option>
            </select>
            <p id="calc-material-preview" class="calc-selected-preview" style="display:none; margin-top: 8px;">Материалы не выбраны</p>
        </div>

        <div id="calc-material-modal" class="calc-modal" style="display:none;">
            <div class="calc-modal-content">
                <div class="calc-modal-header"><h3>Выбор материалов</h3><button type="button" class="calc-modal-close" onclick="closeMaterialModal()" aria-label="Закрыть">×</button></div>
                <div class="calc-modal-body">
                    <div class="material-group" data-brand="dinitrol">
                        <h4>Dinitrol</h4>
                        <div class="checkbox-grid">
                            <label>
                                <input type="checkbox" value="dinitrol-ml" data-base="1750" data-volume="1 л">
                                <div class="element-row">
                                    <span>Антикор для скрытых полостей Dinitrol ML (1 л)</span>
                                    <div class="element-qty-wrap"><button type="button" class="element-qty-btn" data-action="dec" data-for="dinitrol-ml">−</button><input type="number" class="element-qty" min="1" value="1" data-for="dinitrol-ml"><button type="button" class="element-qty-btn" data-action="inc" data-for="dinitrol-ml">+</button></div>
                                </div>
                            </label>
                            <label>
                                <input type="checkbox" value="dinitrol-penetrant" data-base="2090" data-volume="1 л">
                                <div class="element-row">
                                    <span>Антикор для скрытых полостей Dinitrol PENETRANT LT (1 л)</span>
                                    <div class="element-qty-wrap"><button type="button" class="element-qty-btn" data-action="dec" data-for="dinitrol-penetrant">−</button><input type="number" class="element-qty" min="1" value="1" data-for="dinitrol-penetrant"><button type="button" class="element-qty-btn" data-action="inc" data-for="dinitrol-penetrant">+</button></div>
                                </div>
                            </label>
                            <label>
                                <input type="checkbox" value="dinitrol-1000" data-base="2690" data-volume="1 л">
                                <div class="element-row">
                                    <span>Антикор для скрытых полостей Dinitrol 1000 (1 л)</span>
                                    <div class="element-qty-wrap"><button type="button" class="element-qty-btn" data-action="dec" data-for="dinitrol-1000">−</button><input type="number" class="element-qty" min="1" value="1" data-for="dinitrol-1000"><button type="button" class="element-qty-btn" data-action="inc" data-for="dinitrol-1000">+</button></div>
                                </div>
                            </label>
                            <label>
                                <input type="checkbox" value="dinitrol-metallic" data-base="1390" data-volume="1 л">
                                <div class="element-row">
                                    <span>Антикор для днища Dinitrol Metallic (1 л)</span>
                                    <div class="element-qty-wrap"><button type="button" class="element-qty-btn" data-action="dec" data-for="dinitrol-metallic">−</button><input type="number" class="element-qty" min="1" value="1" data-for="dinitrol-metallic"><button type="button" class="element-qty-btn" data-action="inc" data-for="dinitrol-metallic">+</button></div>
                                </div>
                            </label>
                            <label>
                                <input type="checkbox" value="dinitrol-482" data-base="2490" data-volume="1 л">
                                <div class="element-row">
                                    <span>Антикор для днища Dinitrol 482 (1 л)</span>
                                    <div class="element-qty-wrap"><button type="button" class="element-qty-btn" data-action="dec" data-for="dinitrol-482">−</button><input type="number" class="element-qty" min="1" value="1" data-for="dinitrol-482"><button type="button" class="element-qty-btn" data-action="inc" data-for="dinitrol-482">+</button></div>
                                </div>
                            </label>
                            <label>
                                <input type="checkbox" value="dinitrol-447" data-base="1990" data-volume="500 мл">
                                <div class="element-row">
                                    <span>Антигравий Dinitrol 447 черный (500 мл)</span>
                                    <div class="element-qty-wrap"><button type="button" class="element-qty-btn" data-action="dec" data-for="dinitrol-447">−</button><input type="number" class="element-qty" min="1" value="1" data-for="dinitrol-447"><button type="button" class="element-qty-btn" data-action="inc" data-for="dinitrol-447">+</button></div>
                                </div>
                            </label>
                            <label>
                                <input type="checkbox" value="dinitrol-77b" data-base="2450" data-volume="500 мл">
                                <div class="element-row">
                                    <span>Антикор жидкий воск Dinitrol 77B (500 мл)</span>
                                    <div class="element-qty-wrap"><button type="button" class="element-qty-btn" data-action="dec" data-for="dinitrol-77b">−</button><input type="number" class="element-qty" min="1" value="1" data-for="dinitrol-77b"><button type="button" class="element-qty-btn" data-action="inc" data-for="dinitrol-77b">+</button></div>
                                </div>
                            </label>
                            <label>
                                <input type="checkbox" value="dinitrol-4010" data-base="2490" data-volume="500 мл">
                                <div class="element-row">
                                    <span>Консервант для двигателя Dinitrol 4010 (500 мл)</span>
                                    <div class="element-qty-wrap"><button type="button" class="element-qty-btn" data-action="dec" data-for="dinitrol-4010">−</button><input type="number" class="element-qty" min="1" value="1" data-for="dinitrol-4010"><button type="button" class="element-qty-btn" data-action="inc" data-for="dinitrol-4010">+</button></div>
                                </div>
                            </label>
                            <label>
                                <input type="checkbox" value="dinitrol-713iq" data-base="6990" data-volume="400 мл">
                                <div class="element-row">
                                    <span>Антикор для сплавов на основе алюминия Dinitrol 713 IQ (400 мл)</span>
                                    <div class="element-qty-wrap"><button type="button" class="element-qty-btn" data-action="dec" data-for="dinitrol-713iq">−</button><input type="number" class="element-qty" min="1" value="1" data-for="dinitrol-713iq"><button type="button" class="element-qty-btn" data-action="inc" data-for="dinitrol-713iq">+</button></div>
                                </div>
                            </label>
                            <label>
                                <input type="checkbox" value="dinitrol-8050" data-base="1890" data-volume="400 мл">
                                <div class="element-row">
                                    <span>Краска алкидно-полиуретановая высокотемпературная Dinitrol 8050 (400 мл)</span>
                                    <div class="element-qty-wrap"><button type="button" class="element-qty-btn" data-action="dec" data-for="dinitrol-8050">−</button><input type="number" class="element-qty" min="1" value="1" data-for="dinitrol-8050"><button type="button" class="element-qty-btn" data-action="inc" data-for="dinitrol-8050">+</button></div>
                                </div>
                            </label>
                            <label>
                                <input type="checkbox" value="dinitrol-autocleaner" data-base="1490" data-volume="1 л">
                                <div class="element-row">
                                    <span>Очиститель Dinitrol Autocleaner (1 л)</span>
                                    <div class="element-qty-wrap"><button type="button" class="element-qty-btn" data-action="dec" data-for="dinitrol-autocleaner">−</button><input type="number" class="element-qty" min="1" value="1" data-for="dinitrol-autocleaner"><button type="button" class="element-qty-btn" data-action="inc" data-for="dinitrol-autocleaner">+</button></div>
                                </div>
                            </label>
                        </div>
                    </div>
                    <div class="material-group" data-brand="onb" style="display:none;">
                        <h4>ONB Master</h4>
                        <div class="checkbox-grid">
                            <label>
                                <input type="checkbox" value="onb-warrior" data-base="1499" data-volume="1 л">
                                <div class="element-row">
                                    <span>Антигравий для порогов ОНБ Warrior черный (1 л)</span>
                                    <div class="element-qty-wrap"><button type="button" class="element-qty-btn" data-action="dec" data-for="onb-warrior">−</button><input type="number" class="element-qty" min="1" value="1" data-for="onb-warrior"><button type="button" class="element-qty-btn" data-action="inc" data-for="onb-warrior">+</button></div>
                                </div>
                            </label>
                            <label>
                                <input type="checkbox" value="onb-kolchuga" data-base="779" data-volume="1 кг">
                                <div class="element-row">
                                    <span>Антигравий ОНБ КОЛЬЧУГА черный (1 кг)</span>
                                    <div class="element-qty-wrap"><button type="button" class="element-qty-btn" data-action="dec" data-for="onb-kolchuga">−</button><input type="number" class="element-qty" min="1" value="1" data-for="onb-kolchuga"><button type="button" class="element-qty-btn" data-action="inc" data-for="onb-kolchuga">+</button></div>
                                </div>
                            </label>
                            <label>
                                <input type="checkbox" value="onb-degreaser" data-base="1099" data-volume="1 л">
                                <div class="element-row">
                                    <span>Обезжириватель антисиликон ОНБ (1 л)</span>
                                    <div class="element-qty-wrap"><button type="button" class="element-qty-btn" data-action="dec" data-for="onb-degreaser">−</button><input type="number" class="element-qty" min="1" value="1" data-for="onb-degreaser"><button type="button" class="element-qty-btn" data-action="inc" data-for="onb-degreaser">+</button></div>
                                </div>
                            </label>
                            <label>
                                <input type="checkbox" value="onb-bastion" data-base="2399" data-volume="1 л">
                                <div class="element-row">
                                    <span>Полиуретановое покрытие ОНБ Бастион (1 л)</span>
                                    <div class="element-qty-wrap"><button type="button" class="element-qty-btn" data-action="dec" data-for="onb-bastion">−</button><input type="number" class="element-qty" min="1" value="1" data-for="onb-bastion"><button type="button" class="element-qty-btn" data-action="inc" data-for="onb-bastion">+</button></div>
                                </div>
                            </label>
                            <label>
                                <input type="checkbox" value="onb-armada" data-base="999" data-volume="1 л">
                                <div class="element-row">
                                    <span>Мастика антикоррозийная ОНБ АРМАДА (1 л)</span>
                                    <div class="element-qty-wrap"><button type="button" class="element-qty-btn" data-action="dec" data-for="onb-armada">−</button><input type="number" class="element-qty" min="1" value="1" data-for="onb-armada"><button type="button" class="element-qty-btn" data-action="inc" data-for="onb-armada">+</button></div>
                                </div>
                            </label>
                            <label>
                                <input type="checkbox" value="onb-cerberus-zn" data-base="4399" data-volume="1,9 кг">
                                <div class="element-row">
                                    <span>Грунт цинковый антикоррозийный ОНБ Cerberus Zn (1,9 кг)</span>
                                    <div class="element-qty-wrap"><button type="button" class="element-qty-btn" data-action="dec" data-for="onb-cerberus-zn">−</button><input type="number" class="element-qty" min="1" value="1" data-for="onb-cerberus-zn"><button type="button" class="element-qty-btn" data-action="inc" data-for="onb-cerberus-zn">+</button></div>
                                </div>
                            </label>
                            <label>
                                <input type="checkbox" value="onb-excalibur" data-base="2199" data-volume="0,9 кг + 0,2 кг">
                                <div class="element-row">
                                    <span>Эпоксидный грунт ОНБ Экскалибур 2К1 (0,9 кг + 0,2 кг)</span>
                                    <div class="element-qty-wrap"><button type="button" class="element-qty-btn" data-action="dec" data-for="onb-excalibur">−</button><input type="number" class="element-qty" min="1" value="1" data-for="onb-excalibur"><button type="button" class="element-qty-btn" data-action="inc" data-for="onb-excalibur">+</button></div>
                                </div>
                            </label>

                        </div>
                    </div>
                    <div class="material-group" data-brand="masterwax" style="display:none;">
                        <h4>MasterWAX</h4>
                        <div class="checkbox-grid">
                            <label>
                                <input type="checkbox" value="masterwax-ml" data-base="830" data-volume="1 л/0,75 кг">
                                <div class="element-row">
                                    <span>Мовиль для автомобиля ML Masterwax (1 л/0,75 кг)</span>
                                    <div class="element-qty-wrap"><button type="button" class="element-qty-btn" data-action="dec" data-for="masterwax-ml">−</button><input type="number" class="element-qty" min="1" value="1" data-for="masterwax-ml"><button type="button" class="element-qty-btn" data-action="inc" data-for="masterwax-ml">+</button></div>
                                </div>
                            </label>
                            <label>
                                <input type="checkbox" value="masterwax-313" data-base="1000" data-volume="1 л/1 кг">
                                <div class="element-row">
                                    <span>Антигравий 313 Masterwax черный (1 л/1 кг)</span>
                                    <div class="element-qty-wrap"><button type="button" class="element-qty-btn" data-action="dec" data-for="masterwax-313">−</button><input type="number" class="element-qty" min="1" value="1" data-for="masterwax-313"><button type="button" class="element-qty-btn" data-action="inc" data-for="masterwax-313">+</button></div>
                                </div>
                            </label>
                            <label>
                                <input type="checkbox" value="masterwax-314-black" data-base="880" data-volume="1 л/1 кг">
                                <div class="element-row">
                                    <span>Антигравий 314 Masterwax черный (1 л/1 кг)</span>
                                    <div class="element-qty-wrap"><button type="button" class="element-qty-btn" data-action="dec" data-for="masterwax-314-black">−</button><input type="number" class="element-qty" min="1" value="1" data-for="masterwax-314-black"><button type="button" class="element-qty-btn" data-action="inc" data-for="masterwax-314-black">+</button></div>
                                </div>
                            </label>
                            <label>
                                <input type="checkbox" value="masterwax-wax" data-base="770" data-volume="1 л/0,85 кг">
                                <div class="element-row">
                                    <span>Мовиль ВОСК MasterWax MW 109 (1 л/0,85 кг)</span>
                                    <div class="element-qty-wrap"><button type="button" class="element-qty-btn" data-action="dec" data-for="masterwax-wax">−</button><input type="number" class="element-qty" min="1" value="1" data-for="masterwax-wax"><button type="button" class="element-qty-btn" data-action="inc" data-for="masterwax-wax">+</button></div>
                                </div>
                            </label>
                            <label>
                                <input type="checkbox" value="masterwax-bpm4790" data-base="750" data-volume="1 л/1 кг">
                                <div class="element-row">
                                    <span>Антишум Жидкие подкрылки BPM 4790 MasterWax (1 л/1 кг)</span>
                                    <div class="element-qty-wrap"><button type="button" class="element-qty-btn" data-action="dec" data-for="masterwax-bpm4790">−</button><input type="number" class="element-qty" min="1" value="1" data-for="masterwax-bpm4790"><button type="button" class="element-qty-btn" data-action="inc" data-for="masterwax-bpm4790">+</button></div>
                                </div>
                            </label>
                            <label>
                                <input type="checkbox" value="masterwax-metallic" data-base="820" data-volume="1 л/1 кг">
                                <div class="element-row">
                                    <span>Антикор Metallic MasterWax (1 л/1 кг)</span>
                                    <div class="element-qty-wrap"><button type="button" class="element-qty-btn" data-action="dec" data-for="masterwax-metallic">−</button><input type="number" class="element-qty" min="1" value="1" data-for="masterwax-metallic"><button type="button" class="element-qty-btn" data-action="inc" data-for="masterwax-metallic">+</button></div>
                                </div>
                            </label>
                            <label>
                                <input type="checkbox" value="masterwax-classic" data-base="600" data-volume="1 л/0,75 кг">
                                <div class="element-row">
                                    <span>Мовиль классика MasterWax (1 л/0,75 кг)</span>
                                    <div class="element-qty-wrap"><button type="button" class="element-qty-btn" data-action="dec" data-for="masterwax-classic">−</button><input type="number" class="element-qty" min="1" value="1" data-for="masterwax-classic"><button type="button" class="element-qty-btn" data-action="inc" data-for="masterwax-classic">+</button></div>
                                </div>
                            </label>
                            <label>
                                <input type="checkbox" value="masterwax-bpm482" data-base="850" data-volume="1 л/1 кг">
                                <div class="element-row">
                                    <span>Мастика антикоррозийная BPM 482 MasterWax Service (1 л/1 кг)</span>
                                    <div class="element-qty-wrap"><button type="button" class="element-qty-btn" data-action="dec" data-for="masterwax-bpm482">−</button><input type="number" class="element-qty" min="1" value="1" data-for="masterwax-bpm482"><button type="button" class="element-qty-btn" data-action="inc" data-for="masterwax-bpm482">+</button></div>
                                </div>
                            </label>
                            <label>
                                <input type="checkbox" value="masterwax-zinc" data-base="680" data-volume="1 л/0,75 кг">
                                <div class="element-row">
                                    <span>Мовиль с ЦИНКОМ MasterWax (1 л/0,75 кг)</span>
                                    <div class="element-qty-wrap"><button type="button" class="element-qty-btn" data-action="dec" data-for="masterwax-zinc">−</button><input type="number" class="element-qty" min="1" value="1" data-for="masterwax-zinc"><button type="button" class="element-qty-btn" data-action="inc" data-for="masterwax-zinc">+</button></div>
                                </div>
                            </label>
                            <label>
                                <input type="checkbox" value="masterwax-314-white" data-base="1200" data-volume="1 л/1 кг">
                                <div class="element-row">
                                    <span>Антигравий 314 Masterwax белый (1 л/1 кг)</span>
                                    <div class="element-qty-wrap"><button type="button" class="element-qty-btn" data-action="dec" data-for="masterwax-314-white">−</button><input type="number" class="element-qty" min="1" value="1" data-for="masterwax-314-white"><button type="button" class="element-qty-btn" data-action="inc" data-for="masterwax-314-white">+</button></div>
                                </div>
                            </label>
                            <label>
                                <input type="checkbox" value="masterwax-rust-converter" data-base="715" data-volume="1 л/0,75 кг">
                                <div class="element-row">
                                    <span>Мовиль с преобразователем ржавчины MasterWax (1 л/0,75 кг)</span>
                                    <div class="element-qty-wrap"><button type="button" class="element-qty-btn" data-action="dec" data-for="masterwax-rust-converter">−</button><input type="number" class="element-qty" min="1" value="1" data-for="masterwax-rust-converter"><button type="button" class="element-qty-btn" data-action="inc" data-for="masterwax-rust-converter">+</button></div>
                                </div>
                            </label>
                            <label>
                                <input type="checkbox" value="masterwax-fast" data-base="950" data-volume="1 л/1 кг">
                                <div class="element-row">
                                    <span>Антикор FAST Masterwax битумный (1 л/1 кг)</span>
                                    <div class="element-qty-wrap"><button type="button" class="element-qty-btn" data-action="dec" data-for="masterwax-fast">−</button><input type="number" class="element-qty" min="1" value="1" data-for="masterwax-fast"><button type="button" class="element-qty-btn" data-action="inc" data-for="masterwax-fast">+</button></div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="calc-modal-footer"><button type="button" class="btn-secondary" onclick="closeMaterialModal()">Отмена</button><button type="button" class="btn-primary" onclick="confirmMaterialModal()">Готово</button></div>
            </div>
        </div>
        <div class="calc-buttons">
            <button class="btn-secondary" onclick="prevStep()">Назад</button>
            <button class="btn-primary" onclick="nextStep()">Далее</button>
        </div>
    </div>

    <div class="calc-step" data-step="3">
        <div class="calc-group"><label>Ваше имя</label><input type="text" id="calc-name" placeholder="Иван"></div>
        <div class="calc-group"><label>Телефон</label><input type="text" id="calc-phone" placeholder="+7 (999) 999-99-99"></div>
        <div class="calc-group"><label>Город / район</label><input type="text" id="calc-city" placeholder="Санкт-Петербург"></div>
        <div class="calc-buttons"><button class="btn-secondary" onclick="prevStep()">Назад</button><button class="btn-primary" onclick="calculate()">Рассчитать</button></div>
    </div>

    <div class="calc-step" data-step="4">
        <div class="calc-summary">
            <h3>Расчёт стоимости</h3>
            <p><strong>Авто:</strong> <span id="summary-car"></span></p>
            <p><strong>Услуги:</strong></p>
            <ul class="calc-services-list" id="summary-services"></ul>
            <p><strong>Материал:</strong> <span id="summary-material"></span></p>
            <p><strong>Степень обработки:</strong> <span id="summary-level"></span></p>
            <p><strong>Контакты:</strong> <span id="summary-contacts"></span></p>
            <p class="total">Итого: от <span id="summary-total">0</span> ₽</p>
            <p><em>Точная стоимость определяется после осмотра автомобиля.</em></p>
            <button type="button" class="btn-secondary" id="l178-save-pdf" style="margin-top: 20px;">Сохранить заказ-наряд в PDF</button>
        </div>
        <div class="calc-buttons"><button class="btn-secondary" onclick="prevStep()">Назад</button><button class="btn-primary" onclick="resetCalc()">Новый расчёт</button></div>
    </div>

    <script>
    const carData = {
        'Lada (ВАЗ)': ['Granta', 'Vesta', 'Priora', 'Kalina', 'Niva', 'Largus'],
        'Kia': ['Rio', 'Cerato', 'Optima', 'Sorento', 'Sportage', 'K5', 'Soul'],
        'Hyundai': ['Solaris', 'Elantra', 'Sonata', 'Tucson', 'Santa Fe', 'Creta', 'i30'],
        'Toyota': ['Corolla', 'Camry', 'RAV4', 'Land Cruiser', 'Hilux', 'Prado', 'C-HR'],
        'Nissan': ['Almera', 'Sentra', 'Qashqai', 'X-Trail', 'Patrol', 'Murano', 'Juke'],
        'Volkswagen': ['Polo', 'Jetta', 'Passat', 'Tiguan', 'Touareg', 'Golf'],
        'Renault': ['Logan', 'Sandero', 'Duster', 'Kaptur', 'Arkana', 'Megane'],
        'Ford': ['Focus', 'Fiesta', 'Mondeo', 'Kuga', 'Explorer', 'Mustang'],
        'Chevrolet': ['Aveo', 'Cruze', 'Malibu', 'Captiva', 'Tahoe', 'Niva'],
        'Mazda': ['Mazda3', 'Mazda6', 'CX-5', 'CX-9', 'CX-30'],
        'Mitsubishi': ['Lancer', 'Galant', 'Outlander', 'Pajero', 'ASX', 'Eclipse Cross'],
        'Honda': ['Civic', 'Accord', 'CR-V', 'Pilot', 'HR-V'],
        'Skoda': ['Rapid', 'Octavia', 'Superb', 'Kodiaq', 'Karoq'],
        'Opel': ['Astra', 'Insignia', 'Mokka', 'Zafira', 'Crossland'],
        'Peugeot': ['208', '308', '408', '3008', '5008', '2008'],
        'Citroen': ['C4', 'C5', 'C-Elysee', 'C4 Aircross', 'C5 Aircross'],
        'Suzuki': ['Swift', 'Vitara', 'SX4', 'Jimny', 'Grand Vitara'],
        'Subaru': ['Impreza', 'Legacy', 'Forester', 'Outback', 'XV'],
        'Lexus': ['ES', 'GS', 'RX', 'LX', 'NX', 'UX'],
        'Infiniti': ['Q50', 'Q60', 'QX50', 'QX60', 'QX80'],
        'Audi': ['A3', 'A4', 'A6', 'A8', 'Q5', 'Q7', 'Q8', 'TT'],
        'BMW': ['1 Series', '3 Series', '5 Series', '7 Series', 'X3', 'X5', 'X6', 'X7'],
        'Mercedes-Benz': ['A-Class', 'C-Class', 'E-Class', 'S-Class', 'GLC', 'GLE', 'GLS'],
        'Volvo': ['S60', 'S90', 'XC40', 'XC60', 'XC90'],
        'Porsche': ['911', 'Cayenne', 'Panamera', 'Macan', 'Taycan'],
        'Jeep': ['Compass', 'Cherokee', 'Grand Cherokee', 'Wrangler', 'Renegade'],
        'Chrysler': ['300C', 'Pacifica'],
        'Dodge': ['Challenger', 'Charger', 'Durango', 'Journey'],
        'Cadillac': ['CT5', 'CT6', 'XT5', 'XT6', 'Escalade'],
        'Haval': ['Jolion', 'F7', 'H6', 'H9', 'Dargo'],
        'Geely': ['Coolray', 'Atlas', 'Tugella', 'Monjaro', 'Okavango'],
        'Chery': ['Tiggo 4', 'Tiggo 7', 'Tiggo 8', 'Tiggo 9', 'Omoda'],
        'Changan': ['CS35', 'CS55', 'CS75', 'CS95', 'UNI-K', 'UNI-T'],
        'Exeed': ['TXL', 'VX', 'LX', 'Yaoguang'],
        'Omoda': ['C5', 'S5', 'S6'],
        'Jaecoo': ['J7', 'J8'],
        'Tank': ['300', '500', '700'],
        'GAC': ['GS8', 'GS4', 'GN8', 'Aion'],
        'УАЗ': ['Патриот', 'Пикап', 'Хантер', 'Профи'],
        'ГАЗ': ['ГАЗель', 'Соболь', 'Валдай'],
        'Tesla': ['Model 3', 'Model Y', 'Model S', 'Model X', 'Cybertruck']
    };
    const brandGroups = {
        'Lada (ВАЗ)': 'Отечественные', 'УАЗ': 'Отечественные', 'ГАЗ': 'Отечественные',
        'Kia': 'Корейские', 'Hyundai': 'Корейские',
        'Chery': 'Китайские', 'Haval': 'Китайские', 'Geely': 'Китайские', 'Changan': 'Китайские', 'Exeed': 'Китайские', 'Omoda': 'Китайские', 'Jaecoo': 'Китайские', 'Tank': 'Китайские', 'GAC': 'Китайские',
        'Volkswagen': 'Европейские', 'Renault': 'Европейские', 'Peugeot': 'Европейские', 'Citroen': 'Европейские', 'Opel': 'Европейские', 'Skoda': 'Европейские', 'Volvo': 'Европейские',
        'Toyota': 'Японские', 'Nissan': 'Японские', 'Mazda': 'Японские', 'Mitsubishi': 'Японские', 'Honda': 'Японские', 'Suzuki': 'Японские', 'Subaru': 'Японские',
        'Audi': 'Немецкий премиум', 'BMW': 'Немецкий премиум', 'Mercedes-Benz': 'Немецкий премиум', 'Porsche': 'Немецкий премиум',
        'Ford': 'Американские', 'Chevrolet': 'Американские', 'Jeep': 'Американские', 'Chrysler': 'Американские', 'Dodge': 'Американские', 'Cadillac': 'Американские', 'Tesla': 'Американские'
    };
    const groupMultipliers = { 'Отечественные': 1.0, 'Корейские': 1.15, 'Китайские': 1.10, 'Европейские': 1.25, 'Японские': 1.20, 'Немецкий премиум': 1.50, 'Американские': 1.30 };
    const bodyTypeMultipliers = { 'Хэтчбек': 0.95, 'Седан': 1.00, 'Кроссовер': 1.15, 'Внедорожник': 1.25 };
    const disassemblyItems = [
        ['Снятие+установка переднего бампера', 2900],
        ['Снятие+установка заднего бампера', 2100],
        ['Снятие+установка подкрылка', 900],
        ['Снятие+установка термо-экрана', 1250],
        ['Снятие+установка защиты картера двигателя', 650],
        ['Снятие+установка защиты картера КПП', 500],
        ['Снятие+установка глушителя/катализатора', 1650],
        ['Снятие+установка колеса', 300],
        ['Снятие+установка топливного бака', 5400],
        ['Снятие+установка заднего подрамника', 5800],
        ['Снятие+установка карданного вала', 1650]
    ];
    const workRatesByBody = { 'Хэтчбек': 1500, 'Седан': 1700, 'Кроссовер': 1900, 'Внедорожник': 2200 };
    const materialNames = { dinitrol: 'Dinitrol', onb: 'ONB Master', masterwax: 'MasterWAX' };
    const materialNotes = { dinitrol: 'профессиональная антикоррозийная защита', onb: 'русский аналог с высокой адгезией', masterwax: 'восковая консервация полостей' };
    const brandSelect = document.getElementById('calc-brand');
    const modelSelect = document.getElementById('calc-model');
    for (const brand in carData) {
        const opt = document.createElement('option');
        opt.value = brand; opt.textContent = brand;
        brandSelect.appendChild(opt);
    }
    brandSelect.addEventListener('change', function() {
        modelSelect.innerHTML = '<option value="">— Выберите модель —</option>';
        if (this.value) {
            carData[this.value].forEach(m => { const opt = document.createElement('option'); opt.value = m; opt.textContent = m; modelSelect.appendChild(opt); });
            modelSelect.disabled = false;
        } else { modelSelect.disabled = true; }
    });
    let currentStep = 1;
    function showStep(n) { document.querySelectorAll('#l178-calc .calc-step').forEach(s => s.classList.remove('active')); document.querySelector('#l178-calc .calc-step[data-step="' + n + '"]').classList.add('active'); currentStep = n; window.scrollTo({top: 0, behavior: 'smooth'}); }
    function nextStep() { if (currentStep === 1) { if (!brandSelect.value || !modelSelect.value || !document.getElementById('calc-body-type').value) { alert('Выберите марку, модель и тип кузова'); return; } } showStep(currentStep + 1); }
    function prevStep() { showStep(currentStep - 1); }
    function getBrandMultiplier() { const group = brandGroups[brandSelect.value] || 'Отечественные'; return groupMultipliers[group] || 1.0; }
    function getBodyTypeMultiplier() { return bodyTypeMultipliers[document.getElementById('calc-body-type').value] || 1.0; }
    function getTotalMultiplier() { return getBrandMultiplier() * getBodyTypeMultiplier(); }
    function renderDisassemblyItems() {
        const container = document.getElementById('calc-elements');
        const multiplier = getTotalMultiplier();
        container.innerHTML = '';
        disassemblyItems.forEach(([name, base]) => {
            const price = Math.round(Math.round(base * multiplier / 50) * 50);
            const label = document.createElement('label');
            label.innerHTML = '<div class="element-row"><input type="checkbox" value="' + name + '" data-base="' + base + '"> <span>' + name + ' — ' + price.toLocaleString('ru-RU') + ' ₽</span> <div class="element-qty-wrap"><button type="button" class="element-qty-btn" data-action="dec" data-for="' + name + '">−</button><input type="number" class="element-qty" min="1" value="1" data-for="' + name + '"><button type="button" class="element-qty-btn" data-action="inc" data-for="' + name + '">+</button></div></div>';
            container.appendChild(label);
        });
        container.querySelectorAll('.element-qty-btn').forEach(btn => {
            btn.addEventListener('click', function(e) { e.preventDefault(); e.stopPropagation(); const input = this.parentElement.querySelector('.element-qty'); let val = parseInt(input.value) || 1; if (this.dataset.action === 'inc') { val++; } else { val = Math.max(1, val - 1); } input.value = val; const checkbox = this.closest('label').querySelector('input[type="checkbox"]'); if (!checkbox.checked) checkbox.checked = true; });
        });
        container.querySelectorAll('.element-qty').forEach(input => {
            input.addEventListener('change', function() { let val = parseInt(this.value) || 1; if (val < 1) val = 1; this.value = val; const checkbox = this.closest('label').querySelector('input[type="checkbox"]'); if (!checkbox.checked) checkbox.checked = true; });
        });
    }
    function openElementsModal() { document.getElementById('calc-elements-modal').style.display = 'flex'; document.body.style.overflow = 'hidden'; renderDisassemblyItems(); }
    function closeElementsModal() { document.getElementById('calc-elements-modal').style.display = 'none'; document.body.style.overflow = ''; document.querySelector('#calc-services input[data-type="disassembly"]').checked = document.querySelectorAll('#calc-elements input:checked').length > 0; updateElementsPreview(); }
    function confirmElementsModal() { closeElementsModal(); }
    function updateElementsPreview() { const preview = document.getElementById('calc-elements-preview'); if (!preview) return; const checked = document.querySelectorAll('#calc-elements input:checked'); if (checked.length === 0) { preview.textContent = 'Элементы не выбраны'; } else { preview.textContent = 'Выбрано: ' + Array.from(checked).map(el => { const qty = el.parentElement.querySelector('.element-qty'); const q = qty ? parseInt(qty.value) || 1 : 1; return el.value + (q > 1 ? ' ×' + q : ''); }).join(', '); } }

    function openLaserModal() {
        const brand = brandSelect.value, model = modelSelect.value, bodyType = document.getElementById('calc-body-type').value;
        if (!brand || !model || !bodyType) { alert('Сначала выберите марку, модель и тип кузова'); document.querySelector('#calc-services input[data-type="laser"]').checked = false; return; }
        document.getElementById('laser-modal-car-info').textContent = brand + ' ' + model + ' (' + bodyType + ')';
        document.getElementById('calc-laser-modal').style.display = 'flex'; document.body.style.overflow = 'hidden';
    }
    function closeLaserModal() { document.getElementById('calc-laser-modal').style.display = 'none'; document.body.style.overflow = ''; document.querySelector('#calc-services input[data-type="laser"]').checked = document.querySelectorAll('#laser-modal-services input:checked, #laser-modal-addons input:checked').length > 0; updateLaserPreview(); }
    function confirmLaserModal() { closeLaserModal(); }
    function getLaserMultiplier() { const bodyType = document.getElementById('calc-body-type').value; const m = { 'Хэтчбек': 0.9, 'Седан': 1.0, 'Кроссовер': 1.15, 'Внедорожник': 1.25 }[bodyType] || 1.0; return m * (parseFloat(document.getElementById('laser-modal-condition').value) || 1.5); }
    function calculateLaserModal() {
        const multiplier = getLaserMultiplier(); let total = 0; const services = [];
        document.querySelectorAll('#laser-modal-services input:checked').forEach(i => {
            const rate = parseInt(i.dataset.rate) || 0;
            const areaField = document.getElementById('laser-modal-area-' + i.value);
            const area = areaField ? parseFloat(areaField.value) || 0 : 0;
            const price = area > 0 ? Math.round(rate * area * multiplier) : 0;
            if (area <= 0) return;
            services.push(i.parentElement.textContent.trim() + ' — ' + price.toLocaleString('ru-RU') + ' ₽');
            total += price;
        });
        document.querySelectorAll('#laser-modal-addons input:checked').forEach(i => { const price = Math.round(parseInt(i.dataset.base) * multiplier); total += price; services.push(i.parentElement.textContent.trim() + ' — ' + price.toLocaleString('ru-RU') + ' ₽'); });
        return { total, services };
    }
    function updateLaserPreview() {
        const preview = document.getElementById('calc-laser-preview'); if (!preview) return;
        document.querySelectorAll('#laser-modal-services input').forEach(i => {
            const group = document.getElementById('laser-modal-area-' + i.value + '-group');
            if (group) group.style.display = i.checked ? 'block' : 'none';
        });
        const { services, total } = calculateLaserModal();
        if (services.length === 0) { preview.textContent = 'Лазерная очистка не выбрана'; preview.style.display = 'none'; }
        else { preview.textContent = 'Выбрано: ' + services.join('; ') + ' = ' + total.toLocaleString('ru-RU') + ' ₽'; preview.style.display = 'block'; }
    }

    function openMaterialModal() { 
        const brand = document.getElementById('calc-material').value;
        document.querySelectorAll('#calc-material-modal .material-group').forEach(g => g.style.display = 'none');
        const group = document.querySelector('#calc-material-modal .material-group[data-brand="' + brand + '"]');
        if (group) group.style.display = 'block';
        document.getElementById('calc-material-modal').style.display = 'flex'; 
        document.body.style.overflow = 'hidden'; 
    }
    function closeMaterialModal() { 
        document.getElementById('calc-material-modal').style.display = 'none'; 
        document.body.style.overflow = ''; 
        const materialSelect = document.getElementById('calc-material'); 
        materialSelect.value = document.querySelectorAll('#calc-material-modal input[type="checkbox"]:checked').length > 0 ? materialSelect.dataset.lastBrand || 'dinitrol' : ''; 
        updateMaterialPreview(); 
    }
    function confirmMaterialModal() { closeMaterialModal(); }
    function updateMaterialPreview() { 
        const preview = document.getElementById('calc-material-preview'); 
        if (!preview) return; 
        const checked = document.querySelectorAll('#calc-material-modal input[type="checkbox"]:checked'); 
        if (checked.length === 0) { preview.textContent = 'Материалы не выбраны'; preview.style.display = 'none'; } 
        else { 
            const total = Array.from(checked).reduce((sum, el) => { const qty = parseInt(el.parentElement.querySelector('.element-qty').value) || 1; const price = Math.round(parseInt(el.dataset.base) * 1.05 / 50) * 50; return sum + price * qty; }, 0); 
            const names = Array.from(checked).map(el => { const qty = parseInt(el.parentElement.querySelector('.element-qty').value) || 1; return el.parentElement.querySelector('span').textContent.trim() + (qty > 1 ? ' (×' + qty + ')' : ''); });
            preview.textContent = 'Выбрано: ' + names.join('; ') + ' = ' + total.toLocaleString('ru-RU') + ' ₽'; 
            preview.style.display = 'block'; 
        }
    }

    document.getElementById('calc-material').addEventListener('change', function() { 
        if (this.value) { this.dataset.lastBrand = this.value; openMaterialModal(); } 
        else { document.querySelectorAll('#calc-material-modal input[type="checkbox"]').forEach(i => i.checked = false); updateMaterialPreview(); } 
    });
    document.getElementById('calc-material-modal').addEventListener('click', function(e) { 
        if (e.target.classList.contains('element-qty-btn')) { 
            e.preventDefault(); e.stopPropagation(); 
            const input = e.target.parentElement.querySelector('.element-qty'); 
            let val = parseInt(input.value) || 1; 
            if (e.target.dataset.action === 'inc') { val++; } else { val = Math.max(1, val - 1); } 
            input.value = val; 
            const checkbox = e.target.closest('label').querySelector('input[type="checkbox"]'); 
            if (!checkbox.checked) checkbox.checked = true; 
            updateMaterialPreview(); 
        }
    });
    document.getElementById('calc-material-modal').addEventListener('change', function(e) { if (e.target.type === 'checkbox' || e.target.classList.contains('element-qty')) updateMaterialPreview(); });

    document.querySelector('#calc-services input[data-type="disassembly"]').addEventListener('change', function() { if (this.checked) { openElementsModal(); } else { document.querySelectorAll('#calc-elements input').forEach(i => i.checked = false); updateElementsPreview(); } });
    document.querySelector('#calc-services input[data-type="laser"]').addEventListener('change', function() { if (this.checked) { openLaserModal(); } else { document.querySelectorAll('#laser-modal-services input, #laser-modal-addons input').forEach(i => i.checked = false); document.getElementById('laser-modal-area-rust').value = '0'; document.getElementById('laser-modal-area-paint').value = '0'; document.getElementById('laser-modal-area-anticor').value = '0'; document.getElementById('laser-modal-condition').value = '1.5'; document.getElementById('laser-modal-area-rust-group').style.display = 'none'; document.getElementById('laser-modal-area-paint-group').style.display = 'none'; document.getElementById('laser-modal-area-anticor-group').style.display = 'none'; updateLaserPreview(); } });
    document.querySelectorAll('#laser-modal-services input').forEach(input => { input.addEventListener('change', function() { updateLaserPreview(); }); });
    document.querySelectorAll('#laser-modal-addons input').forEach(input => { input.addEventListener('change', updateLaserPreview); });
    document.getElementById('laser-modal-condition').addEventListener('change', updateLaserPreview);
    document.getElementById('laser-modal-area-rust').addEventListener('input', updateLaserPreview);
    document.getElementById('laser-modal-area-paint').addEventListener('input', updateLaserPreview);
    document.getElementById('laser-modal-area-anticor').addEventListener('input', updateLaserPreview);
    brandSelect.addEventListener('change', function() { if (document.querySelector('#calc-services input[data-type="disassembly"]').checked) renderDisassemblyItems(); });
    document.getElementById('calc-body-type').addEventListener('change', function() { if (document.querySelector('#calc-services input[data-type="disassembly"]').checked) renderDisassemblyItems(); });
    function resetCalc() { brandSelect.value = ''; modelSelect.innerHTML = '<option value="">— Сначала выберите марку —</option>'; modelSelect.disabled = true; document.querySelectorAll('#calc-services input').forEach(i => i.checked = false); document.querySelectorAll('#calc-elements input').forEach(i => i.checked = false); document.querySelectorAll('#laser-modal-services input, #laser-modal-addons input').forEach(i => i.checked = false); document.querySelectorAll('#calc-material-modal input[type="checkbox"]').forEach(i => i.checked = false); document.querySelectorAll('#calc-material-modal .element-qty').forEach(i => i.value = 1); document.getElementById('calc-elements-modal').style.display = 'none'; document.getElementById('calc-laser-modal').style.display = 'none'; document.getElementById('calc-material-modal').style.display = 'none'; document.getElementById('calc-body-type').value = 'Седан'; document.getElementById('calc-level').value = '1.5'; document.getElementById('calc-material').value = ''; document.getElementById('calc-name').value = ''; document.getElementById('calc-phone').value = ''; document.getElementById('calc-city').value = ''; document.getElementById('laser-modal-area-rust').value = '0'; document.getElementById('laser-modal-area-paint').value = '0'; document.getElementById('laser-modal-area-anticor').value = '0'; document.getElementById('laser-modal-condition').value = '1.5'; document.getElementById('laser-modal-area-rust-group').style.display = 'none'; document.getElementById('laser-modal-area-paint-group').style.display = 'none'; document.getElementById('laser-modal-area-anticor-group').style.display = 'none'; document.getElementById('calc-elements-preview').textContent = 'Элементы не выбраны'; document.getElementById('calc-laser-preview').textContent = 'Лазерная очистка не выбрана'; document.getElementById('calc-laser-preview').style.display = 'none'; document.getElementById('calc-material-preview').textContent = 'Материалы не выбраны'; document.getElementById('calc-material-preview').style.display = 'none'; showStep(1); }
    function calculateAnticorWork() {
        const bodyType = document.getElementById('calc-body-type').value;
        const rate = workRatesByBody[bodyType] || 1700;
        const materialChecked = document.querySelectorAll('#calc-material-modal input[type="checkbox"]:checked');
        let liters = 0;
        materialChecked.forEach(el => { liters += parseInt(el.parentElement.querySelector('.element-qty').value) || 1; });
        const price = Math.round(liters * rate);
        return { liters, price, rate };
    }

    function calculate() {
        const brand = brandSelect.value, model = modelSelect.value, bodyType = document.getElementById('calc-body-type').value, level = parseFloat(document.getElementById('calc-level').value), name = document.getElementById('calc-name').value, phone = document.getElementById('calc-phone').value, city = document.getElementById('calc-city').value;
        const multiplier = getTotalMultiplier(); let total = 0; const servicesList = [];
        const materialChecked = document.querySelectorAll('#calc-material-modal input[type="checkbox"]:checked');
        let materialTotal = 0; const materialNamesList = [];
        if (materialChecked.length > 0) {
            materialChecked.forEach(el => {
                const qty = parseInt(el.parentElement.querySelector('.element-qty').value) || 1;
                const unitPrice = Math.round(parseInt(el.dataset.base) * 1.05 / 50) * 50;
                const linePrice = unitPrice * qty;
                materialTotal += linePrice;
                materialNamesList.push(el.parentElement.querySelector('span').textContent.trim() + (qty > 1 ? ' (×' + qty + ')' : '') + ' — ' + linePrice.toLocaleString('ru-RU') + ' ₽');
            });
            total += materialTotal;
        }
        document.querySelectorAll('#calc-services input:checked').forEach(i => {
            let price = 0; let labelText = i.parentElement.textContent.trim();
            if (i.dataset.type === 'disassembly') { document.querySelectorAll('#calc-elements input:checked').forEach(el => { const qty = parseInt(el.parentElement.querySelector('.element-qty').value) || 1; const elPrice = Math.round(Math.round(parseInt(el.dataset.base) * multiplier / 50) * 50) * qty; price += elPrice; servicesList.push('<li>' + el.value + (qty > 1 ? ' (×' + qty + ')' : '') + ' — ' + elPrice.toLocaleString('ru-RU') + ' ₽</li>'); }); }
            else if (i.dataset.type === 'laser') { const laserResult = calculateLaserModal(); price += laserResult.total; laserResult.services.forEach(s => servicesList.push('<li>' + s + '</li>')); }
            else if (i.value === 'anticor') {
                const anticor = calculateAnticorWork();
                if (anticor.liters > 0) {
                    price = anticor.price;
                    servicesList.push('<li>Антикоррозийная обработка (комплекс) — ' + anticor.price.toLocaleString('ru-RU') + ' ₽</li>');
                } else { servicesList.push('<li>Антикоррозийная обработка (комплекс) — материал не выбран</li>'); }
            }
            else { price = Math.round(parseInt(i.dataset.base) * level); servicesList.push('<li>' + labelText + ' — ' + price.toLocaleString('ru-RU') + ' ₽</li>'); }
            total += price;
        });
        if (servicesList.length === 0 && materialTotal === 0) { alert('Выберите хотя бы одну услугу или материал'); return; }
        if (document.querySelector('#calc-services input[data-type="disassembly"]').checked && document.querySelectorAll('#calc-elements input:checked').length === 0) { alert('Выберите хотя бы один элемент для снятия/установки'); return; }
        if (document.querySelector('#calc-services input[data-type="laser"]').checked && calculateLaserModal().services.length === 0) { alert('Выберите хотя бы один тип лазерной очистки'); return; }
        if (!name || !phone) { alert('Укажите имя и телефон'); return; }
        document.getElementById('summary-car').textContent = brand + ' ' + model + ' (' + bodyType + ')';
        document.getElementById('summary-services').innerHTML = servicesList.join('');
        document.getElementById('summary-material').innerHTML = materialNamesList.length > 0 ? '<li>' + materialNamesList.join('</li><li>') + '</li>' : 'Не выбраны';
        document.getElementById('summary-level').textContent = document.getElementById('calc-level').options[document.getElementById('calc-level').selectedIndex].text;
        document.getElementById('summary-contacts').textContent = name + ', ' + phone + (city ? ', ' + city : '');
        document.getElementById('summary-total').textContent = total.toLocaleString('ru-RU');
        showStep(4);
    }
    </script>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('l178_calculator', 'l178_calculator_shortcode');

function l178_laser_calculator_shortcode() {
    ob_start();
    ?>
    <div id="l178-laser-calc">
    <style>
        #l178-laser-calc { --laser-bg: #0a0a0a; --laser-card: #111; --laser-border: #1f1f1f; --laser-accent: #0ea5e9; --laser-text: #e5e5e5; --laser-text-muted: #888; }
        .laser-dark #l178-laser-calc { --laser-bg: #0a0a0a; --laser-card: #111; --laser-border: #1f1f1f; --laser-text: #e5e5e5; }
        .laser-light #l178-laser-calc { --laser-bg: #f5f5f5; --laser-card: #fff; --laser-border: #ddd; --laser-text: #111; --laser-text-muted: #666; --laser-accent: #0284c7; }
        #l178-laser-calc { background: var(--laser-bg); border: 1px solid var(--laser-border); border-radius: 8px; padding: 30px; color: var(--laser-text); max-width: 900px; margin: 0 auto; }
        #l178-laser-calc .calc-step { display: none; }
        #l178-laser-calc .calc-step.active { display: block; }
        #l178-laser-calc .calc-step-title { font-size: 20px; margin-bottom: 24px; font-weight: 600; }
        #l178-laser-calc .calc-group { margin-bottom: 24px; }
        #l178-laser-calc label { display: block; margin-bottom: 10px; font-weight: 600; }
        #l178-laser-calc select, #l178-laser-calc input[type="text"], #l178-laser-calc input[type="number"] { width: 100%; padding: 12px; border-radius: 4px; border: 1px solid var(--laser-border); background: var(--laser-bg); color: var(--laser-text); }
        #l178-laser-calc .checkbox-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
        #l178-laser-calc .checkbox-grid label { font-weight: normal; display: flex; align-items: flex-start; gap: 8px; cursor: pointer; background: var(--laser-bg); border: 1px solid var(--laser-border); border-radius: 4px; padding: 12px; transition: all 0.2s ease; }
        #l178-laser-calc .checkbox-grid label:hover { border-color: var(--laser-accent); }
        #l178-laser-calc .checkbox-grid label:has(input:checked) { border-color: var(--laser-accent); background: rgba(14, 165, 233, 0.1); }
        #l178-laser-calc .checkbox-grid input[type="checkbox"] { width: auto; margin-top: 2px; }
        #l178-laser-calc .laser-services-list { display: flex; flex-direction: column; gap: 12px; }
        #l178-laser-calc .laser-service-item { border: 1px solid var(--laser-border); border-radius: 4px; padding: 12px; background: var(--laser-bg); transition: all 0.2s ease; }
        #l178-laser-calc .laser-service-item:has(input:checked) { border-color: var(--laser-accent); background: rgba(14, 165, 233, 0.1); }
        #l178-laser-calc .laser-service-item label { font-weight: normal; display: flex; align-items: flex-start; gap: 8px; cursor: pointer; margin-bottom: 0; }
        #l178-laser-calc .laser-service-item p { color: var(--laser-text-muted); font-size: 12px; margin: 6px 0 8px 24px; }
        #l178-laser-calc .laser-service-item .calc-group { margin-left: 24px; margin-top: 0; margin-bottom: 0; }
        #l178-laser-calc .laser-service-item .calc-group input { padding: 6px 10px; max-width: 120px; }
        #l178-laser-calc .calc-buttons { display: flex; justify-content: space-between; margin-top: 30px; }
        #l178-laser-calc button { padding: 14px 28px; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; }
        #l178-laser-calc .btn-primary { background: var(--laser-accent); color: #fff; box-shadow: 0 0 20px rgba(14, 165, 233, 0.3); }
        #l178-laser-calc .btn-primary:hover { background: var(--laser-accent); opacity: 0.9; }
        #l178-laser-calc .btn-secondary { background: transparent; border: 1px solid var(--laser-border); color: var(--laser-text); }
        #l178-laser-calc .btn-secondary:hover { border-color: var(--laser-accent); color: var(--laser-accent); }
        #l178-laser-calc .calc-summary { background: var(--laser-bg); padding: 24px; border-radius: 8px; border: 1px solid var(--laser-border); margin-top: 20px; }
        #l178-laser-calc .calc-summary h3 { margin-top: 0; color: #fff; }
        #l178-laser-calc .calc-summary .total { font-size: 28px; color: var(--laser-accent); font-weight: 700; }
        #l178-laser-calc .calc-summary ul { margin: 0 0 20px 0; padding-left: 20px; }
        #l178-laser-calc .calc-summary li { margin-bottom: 8px; }
        #l178-laser-calc .calc-group small { display: block; color: var(--laser-text-muted); font-size: 13px; margin-top: 8px; }
        @media (max-width: 640px) { #l178-laser-calc .checkbox-grid { grid-template-columns: 1fr; } }
    </style>
    <h2 class="calc-step-title">Калькулятор стоимости лазерной очистки</h2>
    <div class="calc-step active" data-step="1">
        <div class="calc-group">
            <label>Класс автомобиля</label>
            <select id="laser-class">
                <option value="compact">Компактный</option>
                <option value="mid" selected>Средний</option>
                <option value="full">Полноразмерный</option>
                <option value="suv">Внедорожник / пикап</option>
            </select>
        </div>
        <div class="calc-group">
            <label>Тип услуги</label>
            <div class="laser-services-list" id="laser-services">
                <div class="laser-service-item">
                    <label><input type="checkbox" value="rust" data-rate="15000"> Очистка ржавчины</label>
                    <p>Удаляем коррозию и окислы с металла перед грунтовкой и антикором.</p>
                    <div class="calc-group" id="laser-area-rust-group" style="display:none;">
                        <label>Площадь обработки, м²</label>
                        <input type="number" id="laser-area-rust" value="0" min="0" step="0.1">
                    </div>
                    </div>
                    <div class="laser-service-item">
                    <label><input type="checkbox" value="paint" data-rate="11250"> Очистка краски</label>
                    <p>Снимаем старое лакокрасочное покрытие без повреждения металла.</p>
                    <div class="calc-group" id="laser-area-paint-group" style="display:none;">
                        <label>Площадь обработки, м²</label>
                        <input type="number" id="laser-area-paint" value="0" min="0" step="0.1">
                    </div>
                    </div>
                    <div class="laser-service-item">
                    <label><input type="checkbox" value="anticor" data-rate="16875"> Очистка старого антикор покрытия</label>
                    <p>Убираем высохший или повреждённый антикор перед нанесением нового слоя.</p>
                    <div class="calc-group" id="laser-area-anticor-group" style="display:none;">
                        <label style="font-size: 13px; margin-bottom: 4px;">Площадь, м²</label>
                        <input type="number" id="laser-area-anticor" min="0" step="0.1" value="0" placeholder="0">
                    </div>
                </div>
            </div>
        </div>
        <div class="calc-group">
            <label>Состояние коррозии</label>
            <select id="laser-condition">
                <option value="1.0">Лёгкое (начальная стадия)</option>
                <option value="1.5" selected>Среднее</option>
                <option value="2.0">Тяжёлое (глубокая коррозия)</option>
            </select>
        </div>
        <div class="calc-group">
            <label>Защита металла</label>
            <div class="checkbox-grid" id="laser-addons">
                <label><input type="checkbox" value="arches" data-base="5000"> Арки</label>
                <label><input type="checkbox" value="sills" data-base="3000"> Пороги</label>
                <label><input type="checkbox" value="doors" data-base="4000"> Двери</label>
                <label><input type="checkbox" value="hood" data-base="3500"> Капот</label>
                <label><input type="checkbox" value="trunk" data-base="3500"> Крышка багажника</label>
                <label><input type="checkbox" value="fenders" data-base="3000"> Крылья</label>
            </div>
        </div>
        <div class="calc-buttons"><span></span><button class="btn-primary" onclick="laserNextStep()">Далее</button></div>
    </div>
    <div class="calc-step" data-step="2">
        <div class="calc-group"><label>Ваше имя</label><input type="text" id="laser-name" placeholder="Иван"></div>
        <div class="calc-group"><label>Телефон</label><input type="text" id="laser-phone" placeholder="+7 (999) 999-99-99"></div>
        <div class="calc-group"><label>Город / район</label><input type="text" id="laser-city" placeholder="Санкт-Петербург"></div>
        <div class="calc-buttons"><button class="btn-secondary" onclick="laserPrevStep()">Назад</button><button class="btn-primary" onclick="laserCalculate()">Рассчитать</button></div>
    </div>
    <div class="calc-step" data-step="3">
        <div class="calc-summary">
            <h3>Расчёт стоимости лазерной очистки</h3>
            <p><strong>Авто:</strong> <span id="laser-summary-class"></span></p>
            <p><strong>Услуги:</strong></p>
            <ul class="calc-services-list" id="laser-summary-services"></ul>
            <p><strong>Состояние:</strong> <span id="laser-summary-condition"></span></p>
            <p><strong>Контакты:</strong> <span id="laser-summary-contacts"></span></p>
            <p class="total">Итого: от <span id="laser-summary-total">0</span> ₽</p>
            <p><em>Точная стоимость определяется после осмотра автомобиля.</em></p>
        </div>
        <div class="calc-buttons"><button class="btn-secondary" onclick="laserPrevStep()">Назад</button><button class="btn-primary" onclick="laserReset()">Новый расчёт</button></div>
    </div>
    <script>
    const laserClassMultipliers = { compact: 0.9, mid: 1.0, full: 1.2, suv: 1.35 };
    let laserStep = 1;
    function laserShowStep(n) { document.querySelectorAll('#l178-laser-calc .calc-step').forEach(s => s.classList.remove('active')); document.querySelector('#l178-laser-calc .calc-step[data-step="' + n + '"]').classList.add('active'); laserStep = n; window.scrollTo({top: 0, behavior: 'smooth'}); }
    function laserNextStep() { const checked = document.querySelectorAll('#laser-services input:checked'); if (checked.length === 0) { alert('Выберите хотя бы один тип услуги'); return; } laserShowStep(laserStep + 1); }
    function laserPrevStep() { laserShowStep(laserStep - 1); }
    document.querySelectorAll('#laser-services input').forEach(input => { input.addEventListener('change', function() { document.querySelectorAll('#laser-services input').forEach(i => { const group = document.getElementById('laser-area-' + i.value + '-group'); if (group) group.style.display = i.checked ? 'block' : 'none'; }); }); });
    function laserCalculate() {
        const carClass = document.getElementById('laser-class').value; const condition = parseFloat(document.getElementById('laser-condition').value); const classMultiplier = laserClassMultipliers[carClass] || 1.0; const name = document.getElementById('laser-name').value; const phone = document.getElementById('laser-phone').value; const city = document.getElementById('laser-city').value; let total = 0; const servicesList = [];
        document.querySelectorAll('#laser-services input:checked').forEach(i => {
            const rate = parseInt(i.dataset.rate) || 0;
            const areaField = document.getElementById('laser-area-' + i.value);
            const area = areaField ? parseFloat(areaField.value) || 0 : 0;
            const price = area > 0 ? Math.round(rate * area * classMultiplier * condition) : 0;
            if (area <= 0) return;
            servicesList.push('<li>' + i.parentElement.textContent.trim() + ' — ' + area + ' м² × ' + rate.toLocaleString('ru-RU') + ' ₽ = ' + price.toLocaleString('ru-RU') + ' ₽</li>');
            total += price;
        });
        document.querySelectorAll('#laser-addons input:checked').forEach(i => { const price = Math.round(parseInt(i.dataset.base) * classMultiplier * condition); total += price; servicesList.push('<li>' + i.parentElement.textContent.trim() + ' — ' + price.toLocaleString('ru-RU') + ' ₽</li>'); });
        if (!name || !phone) { alert('Укажите имя и телефон'); return; }
        const classNames = { compact: 'Компактный', mid: 'Средний', full: 'Полноразмерный', suv: 'Внедорожник / пикап' }; const conditionNames = { 1.0: 'Лёгкое', 1.5: 'Среднее', 2.0: 'Тяжёлое' };
        document.getElementById('laser-summary-class').textContent = classNames[carClass]; document.getElementById('laser-summary-services').innerHTML = servicesList.join(''); document.getElementById('laser-summary-condition').textContent = conditionNames[condition]; document.getElementById('laser-summary-contacts').textContent = name + ', ' + phone + (city ? ', ' + city : ''); document.getElementById('laser-summary-total').textContent = total.toLocaleString('ru-RU'); laserShowStep(3);
    }
    function laserReset() { document.getElementById('laser-class').value = 'mid'; document.querySelectorAll('#laser-services input').forEach(i => i.checked = false); document.querySelectorAll('#laser-addons input').forEach(i => i.checked = false); document.getElementById('laser-area-rust').value = '0'; document.getElementById('laser-area-paint').value = '0'; document.getElementById('laser-area-anticor').value = '0'; document.getElementById('laser-condition').value = '1.5'; document.getElementById('laser-name').value = ''; document.getElementById('laser-phone').value = ''; document.getElementById('laser-city').value = ''; document.getElementById('laser-area-rust-group').style.display = 'none'; document.getElementById('laser-area-paint-group').style.display = 'none'; document.getElementById('laser-area-anticor-group').style.display = 'none'; laserShowStep(1); }
    </script>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('l178_laser_calculator', 'l178_laser_calculator_shortcode');

function l178_price_shortcode() {
    $bodyTypes = ['Хэтчбек' => 0.95, 'Седан' => 1.0, 'Кроссовер' => 1.15, 'Внедорожник' => 1.25];
    $groups = [
        'Отечественные' => 1.0,
        'Корейские' => 1.15,
        'Китайские' => 1.10,
        'Европейские' => 1.25,
        'Японские' => 1.20,
        'Немецкий премиум' => 1.50,
        'Американские' => 1.30,
    ];
    $services = [
        ['Снятие+установка переднего бампера', 2900],
        ['Снятие+установка заднего бампера', 2100],
        ['Снятие+установка подкрылка', 900],
        ['Снятие+установка термо-экрана', 1250],
        ['Снятие+установка защиты картера двигателя', 650],
        ['Снятие+установка защиты картера КПП', 500],
        ['Снятие+установка глушителя/катализатора', 1650],
        ['Снятие+установка колеса', 300],
        ['Снятие+установка топливного бака', 5400],
        ['Снятие+установка заднего подрамника', 5800],
        ['Снятие+установка карданного вала', 1650],
    ];
    $laserServices = [
        ['Очистка ржавчины', 8000],
        ['Очистка краски', 6000],
        ['Очистка старого антикор покрытия', 9000],
    ];
    $dinitrolMaterials = [
        ['Антикор для скрытых полостей Dinitrol ML', '1 л', 1750],
        ['Антикор для скрытых полостей Dinitrol PENETRANT LT', '1 л', 2090],
        ['Антикор для скрытых полостей Dinitrol 1000', '1 л', 2690],
        ['Антикор для днища Dinitrol Metallic', '1 л', 1390],
        ['Антикор для днища Dinitrol 482', '1 л', 2490],
        ['Антигравий Dinitrol 447 черный', '500 мл', 1990],
        ['Антикор жидкий воск Dinitrol 77B', '500 мл', 2450],
        ['Консервант для двигателя Dinitrol 4010', '500 мл', 2490],
        ['Антикор для сплавов на основе алюминия Dinitrol 713 IQ', '400 мл', 6990],
        ['Краска алкидно-полиуретановая высокотемпературная Dinitrol 8050', '400 мл', 1890],
        ['Очиститель Dinitrol Autocleaner', '1 л', 1490],
    ];
    $html = '<style>
        .l178-price-wrap { font-family: inherit; }
        .l178-price-section { margin-bottom: 40px; }
        .l178-price-section h2 { font-size: 28px; margin: 0 0 20px; }
        .l178-price-accordion { border: 1px solid var(--laser-border); border-radius: 8px; overflow: hidden; margin-bottom: 12px; }
        .l178-price-accordion summary { list-style: none; cursor: pointer; padding: 16px 20px; font-size: 18px; font-weight: 600; background: var(--laser-bg); color: var(--laser-text); display: flex; align-items: center; justify-content: space-between; }
        .l178-price-accordion summary::-webkit-details-marker { display: none; }
        .l178-price-accordion summary::after { content: "+"; font-size: 24px; color: var(--laser-accent); font-weight: 400; }
        .l178-price-accordion[open] summary::after { content: "−"; }
        .l178-price-accordion summary:hover { background: rgba(14, 165, 233, 0.08); }
        .l178-price-accordion table { width: 100%; border-collapse: collapse; }
        .l178-price-accordion th, .l178-price-accordion td { padding: 10px 8px; border-bottom: 1px solid var(--laser-border); }
        .l178-price-accordion th { text-align: left; font-weight: 600; background: var(--laser-bg); white-space: nowrap; }
        .l178-price-accordion td { text-align: center; }
        .l178-price-accordion td:first-child { text-align: left; }
        .l178-price-accordion td:last-child { white-space: nowrap; min-width: 110px; }
    </style>';

    $renderTable = function($bodyType, $bodyMultiplier, $headers, $rows) use ($groups) {
        $out = '<details class="l178-price-accordion">';
        $out .= '<summary>' . esc_html($bodyType) . '</summary>';
        $out .= '<div style="overflow-x:auto;"><table>';
        $out .= '<thead><tr><th>' . esc_html($headers[0]) . '</th>';
        for ($i = 1; $i < count($headers); $i++) { $out .= '<th style="text-align:center;">' . esc_html($headers[$i]) . '</th>'; }
        $out .= '</tr></thead><tbody>';
        foreach ($rows as $row) {
            $out .= '<tr><td>' . esc_html($row[0]) . '</td>';
            foreach ($row[1] as $price) { $out .= '<td style="font-weight:bold;">от ' . number_format($price, 0, '', ' ') . ' ₽</td>'; }
            $out .= '</tr>';
        }
        $out .= '</tbody></table></div></details>';
        return $out;
    };

    $html .= '<div class="l178-price-wrap">';
    $html .= '<div class="l178-price-section"><h2>Арматурные работы</h2>';
    foreach ($bodyTypes as $bodyType => $bodyMultiplier) {
        $rows = [];
        foreach ($services as $svc) {
            $prices = [];
            foreach ($groups as $k) { $prices[] = intval(round($svc[1] * $k * $bodyMultiplier / 50) * 50); }
            $rows[] = [$svc[0], $prices];
        }
        $headers = array_merge(['Услуга'], array_keys($groups));
        $html .= $renderTable($bodyType, $bodyMultiplier, $headers, $rows);
    }
    $html .= '</div>';

    $html .= '<div class="l178-price-section"><h2>Лазерная очистка</h2>';
    foreach ($bodyTypes as $bodyType => $bodyMultiplier) {
        $rows = [];
        foreach ($laserServices as $svc) {
            $price = intval(round($svc[1] * $bodyMultiplier / 50) * 50);
            $rows[] = [$svc[0], [$price]];
        }
        $html .= $renderTable($bodyType, $bodyMultiplier, ['Услуга', 'Цена за м²'], $rows);
    }
    $html .= '</div>';

    $onbMaterials = [
        ['Антигравий для порогов ОНБ Warrior черный', '1 л', 1499],
        ['Антигравий ОНБ КОЛЬЧУГА черный', '1 кг', 779],
        ['Обезжириватель антисиликон ОНБ', '1 л', 1099],
        ['Полиуретановое покрытие ОНБ Бастион', '1 л', 2399],
        ['Мастика антикоррозийная ОНБ АРМАДА', '1 л', 999],
        ['Грунт цинковый антикоррозийный ОНБ Cerberus Zn', '1,9 кг', 4399],
        ['Эпоксидный грунт ОНБ Экскалибур 2К1', '0,9 кг + 0,2 кг', 2199],
    ];

    $masterwaxMaterials = [
        ['Мовиль для автомобиля ML Masterwax', '1 л/0,75 кг', 830],
        ['Антигравий алкидно-уретановый 313 Masterwax черный', '1 л/1 кг', 1000],
        ['Антигравий каучуковый 314 Masterwax черный', '1 л/1 кг', 880],
        ['Мовиль для автомобиля ВОСК MasterWax MW 109', '1 л/0,85 кг', 770],
        ['Антишум Жидкие подкрылки BPM 4790 MasterWax', '1 л/1 кг', 750],
        ['Антикор Metallic MasterWax', '1 л/1 кг', 820],
        ['Мовиль классика MasterWax', '1 л/0,75 кг', 600],
        ['Мастика антикоррозийная BPM 482 MasterWax Service', '1 л/1 кг', 850],
        ['Мовиль с ЦИНКОМ MasterWax', '1 л/0,75 кг', 680],
        ['Антигравий каучуковый 314 Masterwax белый', '1 л/1 кг', 1200],
        ['Мовиль с преобразователем ржавчины MasterWax', '1 л/0,75 кг', 715],
        ['Антикор FAST Masterwax битумный', '1 л/1 кг', 950],
    ];

    $materialsByBrand = [
        'Dinitrol' => $dinitrolMaterials,
        'ONB Master' => $onbMaterials,
        'MasterWAX' => $masterwaxMaterials,
    ];

    $html .= '<div class="l178-price-section"><h2>Материалы</h2>';
    $html .= '<div class="l178-price-select" style="margin-bottom: 20px;">';
    $html .= '<label style="display:block; margin-bottom: 8px; font-weight: 600;">Марка материала</label>';
    $html .= '<select id="l178-material-brand" onchange="showMaterialBrandTable(this.value)">';
    $html .= '<option value="">— Выберите марку —</option>';
    foreach (array_keys($materialsByBrand) as $brand) {
        $html .= '<option value="' . esc_attr($brand) . '">' . esc_html($brand) . '</option>';
    }
    $html .= '</select></div>';

    foreach ($materialsByBrand as $brand => $items) {
        $html .= '<div id="l178-material-brand-' . esc_attr($brand) . '" class="l178-material-brand-table" style="display:none;">';
        $html .= '<div style="overflow-x:auto;"><table class="l178-price-accordion"><thead><tr><th>Наименование</th><th>Объём</th><th>Цена</th></tr></thead><tbody>';
        foreach ($items as $mat) {
            $priceWithMarkup = intval(round($mat[2] * 1.05 / 50) * 50);
            $html .= '<tr><td>' . esc_html($mat[0]) . '</td><td>' . esc_html($mat[1]) . '</td><td style="font-weight:bold;">' . number_format($priceWithMarkup, 0, '', ' ') . ' ₽</td></tr>';
        }
        $html .= '</tbody></table></div></div>';
    }
    $html .= '</div>';

    $html .= '<script>
    function showMaterialBrandTable(brand) {
        document.querySelectorAll(".l178-material-brand-table").forEach(function(el) { el.style.display = "none"; });
        if (brand) {
            var t = document.getElementById("l178-material-brand-" + brand);
            if (t) t.style.display = "block";
        }
    }
    </script>';

    $html .= '<p style="margin-top:15px; font-size:14px; color:var(--laser-text-muted);"><em>Цены ориентировочные. Точная стоимость зависит от модели, состояния автомобиля и сложности работ.</em></p>';
    $html .= '</div>';
    return $html;
}
add_shortcode('l178_price', 'l178_price_shortcode');

add_shortcode('l178_booking_form', function() {
    $sent = false;
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['l178_booking_submit'])) {
        $name = sanitize_text_field($_POST['l178_name'] ?? '');
        $phone = sanitize_text_field($_POST['l178_phone'] ?? '');
        $service = sanitize_text_field($_POST['l178_service'] ?? '');
        $date = sanitize_text_field($_POST['l178_date'] ?? '');
        $comment = sanitize_textarea_field($_POST['l178_comment'] ?? '');
        if ($name && $phone) {
            $to = get_option('admin_email') ?: 'info@laser178.ru';
            $subject = 'Новая запись на осмотр — ' . $name;
            $message = "Имя: $name\nТелефон: $phone\nУслуга: $service\nЖелаемая дата: $date\nКомментарий: $comment";
            $headers = ['Content-Type: text/plain; charset=UTF-8'];
            wp_mail($to, $subject, $message, $headers);
            $sent = true;
        }
    }
    $services = ['Лазерная очистка', 'Антикоррозийная обработка', 'Арматурные работы', 'Комплексная защита', 'Консервация скрытых полостей'];
    $html = '<div class="laser-card" style="padding: 30px;">';
    if ($sent) {
        $html .= '<p style="color: #4ade80; font-weight: 600;">✓ Заявка отправлена. Мы свяжемся с вами в ближайшее время.</p>';
    } else {
        $html .= '<form method="post" action="" style="display: flex; flex-direction: column; gap: 18px;">'
            . '<label style="display: flex; flex-direction: column; gap: 6px;"><span>Имя *</span><input type="text" name="l178_name" required style="padding: 12px; border-radius: 4px; border: 1px solid var(--laser-border); background: var(--laser-bg); color: var(--laser-text);"></label>'
            . '<label style="display: flex; flex-direction: column; gap: 6px;"><span>Телефон *</span><input type="tel" name="l178_phone" required style="padding: 12px; border-radius: 4px; border: 1px solid var(--laser-border); background: var(--laser-bg); color: var(--laser-text);"></label>'
            . '<label style="display: flex; flex-direction: column; gap: 6px;"><span>Услуга</span><select name="l178_service" style="padding: 12px; border-radius: 4px; border: 1px solid var(--laser-border); background: var(--laser-bg); color: var(--laser-text);">';
        foreach ($services as $s) {
            $html .= '<option value="' . esc_attr($s) . '"' . (isset($_POST['l178_service']) && $_POST['l178_service'] === $s ? ' selected' : '') . '>' . esc_html($s) . '</option>';
        }
        $html .= '</select></label>'
            . '<label style="display: flex; flex-direction: column; gap: 6px;"><span>Желаемая дата</span><input type="date" name="l178_date" style="padding: 12px; border-radius: 4px; border: 1px solid var(--laser-border); background: var(--laser-bg); color: var(--laser-text);"></label>'
            . '<label style="display: flex; flex-direction: column; gap: 6px;"><span>Комментарий</span><textarea name="l178_comment" rows="4" style="padding: 12px; border-radius: 4px; border: 1px solid var(--laser-border); background: var(--laser-bg); color: var(--laser-text);"></textarea></label>'
            . '<button type="submit" name="l178_booking_submit" class="btn-primary" style="padding: 14px 28px; font-size: 16px; cursor: pointer;">Отправить заявку</button>'
            . '</form>';
    }
    $html .= '</div>';
    return $html;
});

add_shortcode('l178_promo', function() {
    return '<div style="padding: 60px 20px; max-width: 1200px; margin: 0 auto;">
    <div class="laser-section-label">// АКЦИИ</div>
    <h2 style="font-size: 32px; margin-bottom: 30px;">СЕЗОННЫЕ ПРЕДЛОЖЕНИЯ</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
        <div class="laser-card" style="border-left: 4px solid #0ea5e9;">
            <h3>Летняя акция</h3>
            <p>Скидка 10% на комплекс антикора днища и арок до 1 сентября.</p>
            <p style="font-size: 14px; color: var(--laser-text-muted);">Условия: предварительная запись, оплата в день окончания работ.</p>
        </div>
        <div class="laser-card" style="border-left: 4px solid #22c55e;">
            <h3>Контрольный осмотр</h3>
            <p>Скидка 15% на работы при контрольном осмотре через 1 год.</p>
            <p style="font-size: 14px; color: var(--laser-text-muted);">Условия: предварительная запись, оплата в день окончания работ.</p>
        </div>
    </div>
</div>';
});

add_action('wp_head', function() {
    echo '<link rel="icon" type="image/x-icon" href="/favicon.ico?v=3" />';
    echo '<link rel="apple-touch-icon" href="/wp-content/uploads/favicon.png?v=3" />';
}, 1);

add_action('wp_footer', function() {
    echo '<div style="position:fixed;bottom:0;left:0;right:0;z-index:9999;display:flex;justify-content:center;gap:8px;padding:8px 10px 10px;background:rgba(10,10,10,0.92);border-top:1px solid rgba(255,255,255,0.08);font-family:sans-serif;" class="l178-mobile-bar">'
       . '<a href="tel:+79810971505" style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:50px;font-size:13px;font-weight:600;color:#fff;text-decoration:none;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.12);">📞 <span>Позвонить</span></a>'
       . '<a href="https://t.me/+79810971505" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:50px;font-size:13px;font-weight:600;color:#fff;text-decoration:none;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.12);">✈️ <span>Telegram</span></a>'
       . '<a href="#" onclick="alert(\'Ссылка на MAX будет добавлена позже\'); return false;" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:50px;font-size:13px;font-weight:600;color:#fff;text-decoration:none;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.12);">Ⓜ️ <span>MAX</span></a>'
       . '</div>'
       . '<div style="display:none;" class="l178-mobile-spacer"></div>'
       . '<script>(function(){var b=document.querySelector(".l178-mobile-bar"),s=document.querySelector(".l178-mobile-spacer");function r(){if(window.innerWidth<769){b.style.display="flex";s.style.display="block";s.style.height="56px";}else{b.style.display="none";s.style.display="none";}}r();window.addEventListener("resize",r);})();</script>'
       . '<style>.crisp-client, .crisp-client *, .chat-open-button, #chat-widget, .chat-btn, .messenger-button, [class*="chat"][style*="fixed"], [class*="messenger"][style*="fixed"] { bottom: 120px !important; } .laser-cycle-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; } .laser-cycle-card { display: flex; flex-direction: column; width: 100%; box-sizing: border-box; } .laser-cycle-card p { flex-grow: 1; } @media (max-width: 768px) { .laser-cycle-grid { grid-template-columns: 1fr; } .laser-cycle-card { width: 100%; } }</style>'
       . '<script>(function(){function liftChat(){var isMobile=window.innerWidth<769;var el=document.querySelectorAll("*");for(var i=0;i<el.length;i++){var st=window.getComputedStyle(el[i]);if(st.position==="fixed"&&st.bottom!=="auto"&&!isNaN(parseInt(st.bottom))){var bottomVal=parseInt(st.bottom);var isFullWidth=(st.left==="0px"||st.left==="0%")&&(st.right==="0px"||st.right==="0%");var isOurBar=el[i].closest&&el[i].closest(".l178-mobile-bar");if(isMobile&&bottomVal<200&&!isFullWidth&&!isOurBar){el[i].style.setProperty("bottom","120px","important");}else if(!isMobile&&bottomVal>=300&&!isFullWidth&&!isOurBar){el[i].style.setProperty("bottom","","");}}}}setTimeout(liftChat,1000);setTimeout(liftChat,3000);setInterval(liftChat,2000);var mo=new MutationObserver(liftChat);mo.observe(document.body,{childList:true,subtree:true});window.addEventListener("resize",liftChat);})();</script>'
       . '<script>(function(){var logo=document.querySelector(".site-branding .site-title a");if(logo){logo.innerHTML="<img src=\"/wp-content/uploads/laser-logo-latest.png\" alt=\"Лазер Антикор\" style=\"height:75px;width:auto;display:block;\" />";logo.style.background="none";logo.style.textIndent="0";logo.style.color="transparent";}})();</script>'
       . '<button type="button" id="laser-back-to-top" aria-label="Наверх" title="Наверх" style="position:fixed;bottom:24px;left:24px;width:48px;height:48px;border-radius:50%;border:1px solid var(--laser-cyan, #22d3ee);background:rgba(15,23,42,0.9);color:var(--laser-cyan, #22d3ee);cursor:pointer;z-index:9998;display:none;align-items:center;justify-content:center;box-shadow:0 0 14px rgba(34,211,238,0.45), 0 4px 20px rgba(0,0,0,0.4);transition:opacity 0.3s, transform 0.3s, box-shadow 0.3s;opacity:0;transform:translateY(20px);" onclick="window.scrollTo({top:0,behavior:&#039;smooth&#039;});">'
       . '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 15l-6-6-6 6"/></svg>'
       . '</button>'
       . '<script>(function(){var btn=document.getElementById("laser-back-to-top");if(!btn)return;function toggle(){var y=window.scrollY||window.pageYOffset;if(y>400){btn.style.display="flex";setTimeout(function(){btn.style.opacity="1";btn.style.transform="translateY(0)";},10);}else{btn.style.opacity="0";btn.style.transform="translateY(20px)";setTimeout(function(){btn.style.display="none";},300);}}toggle();window.addEventListener("scroll",toggle);})();</script>';
 }, 100);

// Risk test shortcode
add_shortcode('l178_risk_test', function($attrs) {
    $id = 'risk-' . wp_rand(1000, 9999);
    wp_enqueue_script('laser178-risk-test', plugins_url('laser178-risk-test.js', __FILE__), [], '1.0', true);
    return '<div class="laser-risk-test" id="' . esc_attr($id) . '">
        <div class="laser-risk-step active" data-step="1">
            <h3>Шаг 1: марка и модель</h3>
            <input type="text" class="laser-risk-input" name="brand" placeholder="Например, Toyota Camry" />
            <button type="button" class="laser-btn next-step">Далее</button>
        </div>
        <div class="laser-risk-step" data-step="2">
            <h3>Шаг 2: год выпуска</h3>
            <input type="number" class="laser-risk-input" name="year" placeholder="Например, 2019" min="1970" max="2030" />
            <button type="button" class="laser-btn next-step">Далее</button>
        </div>
        <div class="laser-risk-step" data-step="3">
            <h3>Шаг 3: где стоит машина?</h3>
            <select class="laser-risk-input" name="parking">
                <option value="garage">Тёплый гараж</option>
                <option value="street" selected>Улица</option>
                <option value="wet">Влажный подземный паркинг</option>
            </select>
            <button type="button" class="laser-btn finish-risk">Узнать результат</button>
        </div>
        <div class="laser-risk-result" style="display:none;">
            <h3>Ваш прогноз</h3>
            <p class="laser-risk-text"></p>
            <a href="/blog/" class="laser-btn">Читать, как защититься</a>
        </div>
    </div>';
});

// Model subscription shortcode
add_shortcode('l178_model_subscribe', function($attrs) {
    $id = 'sub-' . wp_rand(1000, 9999);
    wp_enqueue_script('laser178-subscribe', plugins_url('laser178-subscribe.js', __FILE__), [], '1.0', true);
    wp_localize_script('laser178-subscribe', 'l178_subscribe', ['ajax_url' => admin_url('admin-ajax.php')]);
    return '<div class="laser-subscribe" id="' . esc_attr($id) . '">
        <h3>Следить за моделью</h3>
        <p>Получайте уведомления, когда выходит материал по вашей марке/модели.</p>
        <form class="laser-subscribe-form">
            <input type="text" name="model" placeholder="Марка и модель, например BMW X5" required />
            <input type="email" name="email" placeholder="Ваш email" required />
            <button type="submit" class="laser-btn">Подписаться</button>
            <p class="laser-subscribe-msg"></p>
        </form>
    </div>';
});

add_action('wp_ajax_l178_subscribe', 'l178_handle_subscribe');
add_action('wp_ajax_nopriv_l178_subscribe', 'l178_handle_subscribe');
function l178_handle_subscribe() {
    $email = sanitize_email($_POST['email'] ?? '');
    $model = sanitize_text_field($_POST['model'] ?? '');
    if (!$email || !$model) {
        wp_send_json_error('Заполните все поля');
    }
    if (!is_email($email)) {
        wp_send_json_error('Некорректный email');
    }
    global $wpdb;
    $table = $wpdb->prefix . 'laser_subscribers';
    $wpdb->query("CREATE TABLE IF NOT EXISTS $table (
        id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        model VARCHAR(255) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE email = %s AND model = %s", $email, $model));
    if ($exists) {
        wp_send_json_success('Вы уже подписаны на эту модель');
    }
    $wpdb->insert($table, ['email' => $email, 'model' => $model]);
    wp_send_json_success('Подписка оформлена');
}

add_action('wp', function() {
    if (isset($_GET['l178_install']) && $_GET['l178_install'] === '1' && isset($_GET['secret']) && $_GET['secret'] === 'l178update2025') {
        global $wpdb;
        $table = $wpdb->prefix . 'laser_subscribers';
        $wpdb->query("CREATE TABLE IF NOT EXISTS $table (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL,
            model VARCHAR(255) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        // Create risk test page
        if (!get_page_by_path('risk-test')) {
            wp_insert_post([
                'post_title' => 'Узнай уровень угрозы ржавчины',
                'post_name' => 'risk-test',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_content' => '<h2>Узнайте, когда ваш авто сдастся ржавчине</h2><p>Ответьте на 3 вопроса, и мы дадим прогноз по состоянию кузова.</p>[l178_risk_test]',
            ]);
        }
        // Create subscribe page
        if (!get_page_by_path('follow-model')) {
            wp_insert_post([
                'post_title' => 'Следить за моделью',
                'post_name' => 'follow-model',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_content' => '<h2>Подпишитесь на материалы по своей модели</h2>[l178_model_subscribe]',
            ]);
        }
        echo 'INSTALLED';
        exit;
    }
});

add_action('wp', function() {
    if (is_page('home') || is_front_page()) {
        remove_filter('the_content', 'wpautop');
    }
});

// Blog grid shortcode
add_shortcode('l178_blog_grid', function($attrs) {
    $args = shortcode_atts(['count' => 12, 'category' => ''], $attrs);
    $query_args = ['post_type' => 'post', 'posts_per_page' => intval($args['count'])];
    if ($args['category']) {
        $query_args['category_name'] = sanitize_text_field($args['category']);
    }
    $query = new WP_Query($query_args);
    if (!$query->have_posts()) return '<p>Статьи пока не опубликованы.</p>';
    $out = '<div class="laser-blog-grid">';
    while ($query->have_posts()) {
        $query->the_post();
        $title = get_the_title();
        $link = get_permalink();
        $excerpt = wp_trim_words(get_the_excerpt(), 24);
        $thumb = get_the_post_thumbnail_url(get_the_ID(), 'medium');
        $out .= '<article class="laser-blog-card">';
        if ($thumb) {
            $out .= '<a href="' . esc_url($link) . '" class="laser-blog-thumb" style="background-image:url(' . esc_url($thumb) . ')"></a>';
        }
        $out .= '<div class="laser-blog-body">';
        $out .= '<h3><a href="' . esc_url($link) . '">' . esc_html($title) . '</a></h3>';
        $out .= '<p>' . esc_html($excerpt) . '</p>';
        $out .= '</div>';
        $out .= '</article>';
    }
    wp_reset_postdata();
    $out .= '</div>';
    return $out;
});

// Before/After shortcode
add_shortcode('l178_before_after', function($attrs) {
    $args = shortcode_atts(['img1' => '', 'img2' => '', 'title' => ''], $attrs);
    $id = 'ba-' . wp_rand(1000, 9999);
    if (!$args['img1'] || !$args['img2']) return '<p>Укажите img1 и img2.</p>';
    wp_enqueue_script('laser178-before-after', plugins_url('laser178-before-after.js', __FILE__), [], '1.0', true);
    return '<div class="laser-ba-card">
        ' . ($args['title'] ? '<h3>' . esc_html($args['title']) . '</h3>' : '') . '
        <div class="laser-ba" id="' . esc_attr($id) . '" data-img1="' . esc_url($args['img1']) . '" data-img2="' . esc_url($args['img2']) . '">
            <div class="laser-ba-img laser-ba-before" style="background-image:url(' . esc_url($args['img1']) . ')"></div>
            <div class="laser-ba-img laser-ba-after" style="background-image:url(' . esc_url($args['img2']) . ')"></div>
            <div class="laser-ba-handle"></div>
        </div>
    </div>';
});

// Home blog feed
add_action('wp_footer', function() {
    if (!is_front_page()) return;
    $query = new WP_Query(['post_type' => 'post', 'posts_per_page' => 3]);
    if (!$query->have_posts()) return;
    echo '<section class="laser-home-blog" style="padding:60px 20px;max-width:1200px;margin:0 auto;">';
    echo '<div class="laser-section-label">// БОРТОВОЙ ЖУРНАЛ</div>';
    echo '<h2 style="font-size:32px;margin-bottom:24px;">Свежие кейсы и статьи</h2>';
    echo '<div class="laser-blog-grid">';
    while ($query->have_posts()) {
        $query->the_post();
        $title = get_the_title();
        $link = get_permalink();
        $excerpt = wp_trim_words(get_the_excerpt(), 24);
        $thumb = get_the_post_thumbnail_url(get_the_ID(), 'medium');
        echo '<article class="laser-blog-card">';
        if ($thumb) {
            echo '<a href="' . esc_url($link) . '" class="laser-blog-thumb" style="background-image:url(' . esc_url($thumb) . ')"></a>';
        }
        echo '<div class="laser-blog-body">';
        echo '<h3><a href="' . esc_url($link) . '">' . esc_html($title) . '</a></h3>';
        echo '<p>' . esc_html($excerpt) . '</p>';
        echo '</div></article>';
    }
    wp_reset_postdata();
    echo '</div>';
    echo '<div style="margin-top:28px;text-align:center;"><a href="/blog/" class="laser-btn">Все материалы</a></div>';
    echo '</section>';
});
