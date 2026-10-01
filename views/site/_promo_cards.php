<?php

/** @var app\models\Promo[] $promos */

use yii\helpers\Html;

?>
<div class="last-promo-grid" role="list">
    <?php foreach ($promos as $promo): ?>
        <article class="last-promo-card" role="listitem">
            <?php if (!empty($promo->image_promo)): ?>
                <div class="promo-image">
                    <img src="<?= Html::encode($promo->image_promo) ?>" alt="<?= Html::encode($promo->title) ?>" loading="lazy" decoding="async">
                </div>
            <?php else: ?>
                <div class="promo-image promo-image--empty" aria-hidden="true"></div>
            <?php endif; ?>
            <div class="promo-info">
                <?php if (!empty($promo->discount_percent)): ?>
                        <?php endif; ?>
                <h3 class="promo-title"><?= Html::encode($promo->title) ?></h3>
                <?php if (!empty($promo->description)): ?>
                    <p class="promo-description"><?= Html::encode($promo->description) ?></p>
                <?php endif; ?>
                <?php
                $dates = [];
                if (!empty($promo->start_date)) {
                    $ts = strtotime((string) $promo->start_date);
                    if ($ts) {
                        $dates[] = 'с ' . date('d.m.Y', $ts);
                    }
                }
                if (!empty($promo->end_date)) {
                    $ts = strtotime((string) $promo->end_date);
                    if ($ts) {
                        $dates[] = 'до ' . date('d.m.Y', $ts);
                    }
                }
                ?>
                <?php if ($dates !== []): ?>
                    <p class="promo-dates"><?= Html::encode(implode(' · ', $dates)) ?></p>
                <?php endif; ?>
            </div>
        </article>
    <?php endforeach; ?>
</div>
