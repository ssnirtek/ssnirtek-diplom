<?php

namespace app\components;

/**
 * Размер страницы каталога по ширине экрана (синхронизируется JS → cookie catalog_ps).
 * Значения: 15 (≥1200px), 12 (768–1199px), 14 (<768px).
 * Cookie задаётся из браузера без Yii-подписи — читаем из $_COOKIE и валидируем whitelist.
 */
class CatalogPageSize
{
    public const COOKIE_NAME = 'catalog_ps';

    /** @var int[] */
    public const ALLOWED = [12, 14, 15];

    public static function resolveFromRequest(): int
    {
        $raw = $_COOKIE[self::COOKIE_NAME] ?? null;
        if ($raw === null || $raw === '') {
            return 15;
        }
        $n = (int) $raw;

        return in_array($n, self::ALLOWED, true) ? $n : 15;
    }
}
