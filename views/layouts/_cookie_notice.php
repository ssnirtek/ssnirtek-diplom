<?php
use yii\helpers\Html;
?>

<div id="cookie-notice" class="cookie-notice" style="display: none;">
    <div class="cookie-content">
        <p class="cookie-text">
            Мы используем файлы cookie для улучшения работы сайта. 
            Продолжая использование сайта, вы соглашаетесь с нашей 
            <?= Html::a('политикой конфиденциальности', ['/site/policy'], ['class' => 'cookie-link']) ?>.
        </p>
        <?= Html::button('Принять', [
            'class' => 'cookie-btn',
            'onclick' => 'acceptCookies()'
        ]) ?>
    </div>
</div>

<script>
function acceptCookies() {
    document.getElementById('cookie-notice').style.display = 'none';
    localStorage.setItem('cookies_accepted', 'true');
}

if (!localStorage.getItem('cookies_accepted')) {
    document.getElementById('cookie-notice').style.display = 'block';
}
</script>