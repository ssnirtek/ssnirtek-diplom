-- ============================================================================
--  Bijouterie.ss: структура базы данных (MySQL 8 / MariaDB 10.4+, InnoDB, utf8mb4)
--
--  ВАЖНО: этот файл НЕ выгрузка с сервера. Он восстановлен по моделям Yii2
--  (models/*.php), миграциям (migrations/*.php) и описанию базы в дипломе
--  (10 таблиц + представление v_cart_details).
--  Названия таблиц и колонок, связи и уникальные ключи взяты из кода.
--  Типы (длины VARCHAR, точность DECIMAL) выведены из правил валидации и могут
--  отличаться от исходной базы на сервере колледжа.
--  Состояние уже включает обе миграции проекта (см. README, раздел «Запуск»).
--  Данных пользователей и заказов здесь нет.
--
--  Импорт:  mysql -u USER -p -e "CREATE DATABASE bijouterie CHARACTER SET utf8mb4;"
--           mysql -u USER -p bijouterie < database/schema.sql
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------------------------
-- Пользователи (роль user / admin, токены подтверждения почты и сброса пароля)
-- --------------------------------------------------------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id_user`              INT          NOT NULL AUTO_INCREMENT,
  `email`                VARCHAR(100) NOT NULL,
  `password_hash`        VARCHAR(255) NOT NULL,              -- bcrypt-хеш, пароль в открытом виде не хранится
  `full_name`            VARCHAR(150) NOT NULL,
  `phone`                VARCHAR(20)  NULL,
  `date_born`            DATE         NULL,
  `is_admin`             ENUM('user','admin') NOT NULL DEFAULT 'user',
  `status`               TINYINT      NOT NULL DEFAULT 1,    -- 1 = активен, 0 = неактивен
  `password_reset_token` VARCHAR(255) NULL,
  `email_confirm_token`  VARCHAR(255) NULL,
  `created_at`           DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `uq_user_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------------------------
-- Категории и товары
-- --------------------------------------------------------------------------
DROP TABLE IF EXISTS `category`;
CREATE TABLE `category` (
  `id_category`   INT          NOT NULL AUTO_INCREMENT,
  `category_name` VARCHAR(100) NOT NULL,
  `description`   VARCHAR(355) NOT NULL,
  PRIMARY KEY (`id_category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `product`;
CREATE TABLE `product` (
  `id_product`    INT           NOT NULL AUTO_INCREMENT,
  `category_id`   INT           NOT NULL,
  `name`          VARCHAR(200)  NOT NULL,
  `description`   TEXT          NULL,                         -- после миграции m260526_171500
  `price`         DECIMAL(10,2) NOT NULL,
  `is_discount`   TINYINT(1)    NOT NULL DEFAULT 0,
  `old_price`     DECIMAL(10,2) NULL,                         -- больше price, когда включена скидка
  `quantity`      INT           NOT NULL DEFAULT 0,           -- остаток на складе
  `image_product` VARCHAR(255)  NULL,
  `is_active`     TINYINT(1)    NOT NULL DEFAULT 1,
  `created_at`    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_product`),
  KEY `idx_product_category` (`category_id`),
  CONSTRAINT `fk_product_category` FOREIGN KEY (`category_id`) REFERENCES `category` (`id_category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------------------------
-- Акции и привязка товаров к акциям
-- --------------------------------------------------------------------------
DROP TABLE IF EXISTS `promo`;
CREATE TABLE `promo` (
  `id_promo`         INT          NOT NULL AUTO_INCREMENT,
  `title`            VARCHAR(255) NOT NULL,
  `description`      TEXT         NULL,
  `discount_percent` INT          NULL,                      -- 0..100
  `conditions`       TEXT         NULL,
  `start_date`       DATE         NULL,
  `end_date`         DATE         NULL,
  `is_active`        TINYINT(1)   NOT NULL DEFAULT 1,
  `sort_order`       INT          NOT NULL DEFAULT 0,
  `image_promo`      VARCHAR(255) NULL,
  PRIMARY KEY (`id_promo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `promo_product`;
CREATE TABLE `promo_product` (
  `id_promo_product` INT           NOT NULL AUTO_INCREMENT,
  `promo_id`         INT           NOT NULL,
  `product_id`       INT           NOT NULL,
  `old_price`        DECIMAL(10,2) NULL,
  `price_discount`   DECIMAL(10,2) NULL,
  PRIMARY KEY (`id_promo_product`),
  UNIQUE KEY `uq_promo_product` (`promo_id`, `product_id`),
  CONSTRAINT `fk_pp_promo`   FOREIGN KEY (`promo_id`)   REFERENCES `promo`   (`id_promo`)   ON DELETE CASCADE,
  CONSTRAINT `fk_pp_product` FOREIGN KEY (`product_id`) REFERENCES `product` (`id_product`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------------------------
-- Корзина
-- --------------------------------------------------------------------------
DROP TABLE IF EXISTS `cart`;
CREATE TABLE `cart` (
  `id_cart`    INT      NOT NULL AUTO_INCREMENT,
  `user_id`    INT      NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_cart`),
  KEY `idx_cart_user` (`user_id`),
  CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `cart_item`;
CREATE TABLE `cart_item` (
  `id`         INT NOT NULL AUTO_INCREMENT,
  `cart_id`    INT NOT NULL,
  `product_id` INT NOT NULL,
  `quantity`   INT NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_ci_cart` (`cart_id`),
  KEY `idx_ci_product` (`product_id`),
  CONSTRAINT `fk_ci_cart`    FOREIGN KEY (`cart_id`)    REFERENCES `cart`    (`id_cart`)    ON DELETE CASCADE,
  CONSTRAINT `fk_ci_product` FOREIGN KEY (`product_id`) REFERENCES `product` (`id_product`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------------------------
-- Заказы: суммы, приветственная скидка 15 % (первые 2 заказа), статусы
-- --------------------------------------------------------------------------
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id_orders`               INT           NOT NULL AUTO_INCREMENT,
  `user_id`                 INT           NOT NULL,
  `subtotal_amount`         DECIMAL(12,2) NOT NULL DEFAULT 0,   -- миграция m260513_120000
  `welcome_discount_amount` DECIMAL(12,2) NOT NULL DEFAULT 0,   -- миграция m260513_120000
  `total_amount`            DECIMAL(12,2) NOT NULL,
  `status`                  ENUM('pending','processing','completed','cancelled') NOT NULL DEFAULT 'pending',
  `full_name`               VARCHAR(150)  NULL,
  `phone`                   VARCHAR(20)   NULL,
  `email`                   VARCHAR(100)  NULL,
  `address`                 VARCHAR(255)  NULL,
  `comment`                 TEXT          NULL,
  `created_at`              DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_orders`),
  KEY `idx_orders_user` (`user_id`),
  KEY `idx_orders_status` (`status`),
  CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `order_item`;
CREATE TABLE `order_item` (
  `id`         INT           NOT NULL AUTO_INCREMENT,
  `order_id`   INT           NOT NULL,
  `product_id` INT           NOT NULL,
  `quantity`   INT           NOT NULL,
  `price`      DECIMAL(10,2) NOT NULL,                          -- цена фиксируется на момент заказа
  PRIMARY KEY (`id`),
  KEY `idx_oi_order` (`order_id`),
  KEY `idx_oi_product` (`product_id`),
  CONSTRAINT `fk_oi_order`   FOREIGN KEY (`order_id`)   REFERENCES `orders`  (`id_orders`)   ON DELETE CASCADE,
  CONSTRAINT `fk_oi_product` FOREIGN KEY (`product_id`) REFERENCES `product` (`id_product`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------------------------
-- Избранное и отзывы
-- --------------------------------------------------------------------------
DROP TABLE IF EXISTS `favorites`;
CREATE TABLE `favorites` (
  `id_favorite` INT      NOT NULL AUTO_INCREMENT,
  `user_id`     INT      NOT NULL,
  `product_id`  INT      NOT NULL,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_favorite`),
  UNIQUE KEY `uq_favorite` (`user_id`, `product_id`),             -- один товар в избранном один раз
  CONSTRAINT `fk_fav_user`    FOREIGN KEY (`user_id`)    REFERENCES `user`    (`id_user`)    ON DELETE CASCADE,
  CONSTRAINT `fk_fav_product` FOREIGN KEY (`product_id`) REFERENCES `product` (`id_product`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews` (
  `id_review`   INT        NOT NULL AUTO_INCREMENT,
  `user_id`     INT        NOT NULL,
  `product_id`  INT        NULL,
  `rating`      TINYINT    NOT NULL DEFAULT 5,                  -- 1..5
  `text`        TEXT       NOT NULL,
  `is_approved` TINYINT(1) NOT NULL DEFAULT 1,                  -- публикуется после модерации
  `created_at`  DATETIME   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_review`),
  KEY `idx_reviews_product` (`product_id`),
  CONSTRAINT `chk_reviews_rating` CHECK (`rating` BETWEEN 1 AND 5),
  CONSTRAINT `fk_reviews_user`    FOREIGN KEY (`user_id`)    REFERENCES `user`    (`id_user`)    ON DELETE CASCADE,
  CONSTRAINT `fk_reviews_product` FOREIGN KEY (`product_id`) REFERENCES `product` (`id_product`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------------------------
-- Представление для суммы корзины (используется в Cart.php и CartController.php:
-- SELECT SUM(total_price) FROM v_cart_details WHERE id_cart = :cart_id).
-- Упрощённая реконструкция: цена товара без учёта акций.
-- --------------------------------------------------------------------------
DROP VIEW IF EXISTS `v_cart_details`;
CREATE VIEW `v_cart_details` AS
SELECT
  c.id_cart,
  ci.id            AS id_cart_item,
  ci.product_id,
  ci.quantity,
  p.name           AS product_name,
  p.price          AS unit_price,
  (p.price * ci.quantity)                       AS total_price,
  (COALESCE(p.old_price, p.price) * ci.quantity) AS original_total
FROM cart c
JOIN cart_item ci ON ci.cart_id = c.id_cart
JOIN product   p  ON p.id_product = ci.product_id;

-- --------------------------------------------------------------------------
-- Категории из описания магазина (демо-справочник, не выгрузка)
-- --------------------------------------------------------------------------
INSERT INTO `category` (`category_name`, `description`) VALUES
  ('Колье',      'Колье и ожерелья из бисера ручной работы'),
  ('Серьги',     'Серьги из бисера и стекляруса'),
  ('Браслеты',   'Браслеты из бисера'),
  ('Кольца',     'Кольца из бисера'),
  ('Аксессуары', 'Аксессуары и подарочная упаковка');
