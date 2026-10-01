<?php

use yii\db\Migration;

/**
 * Добавляет сумму корзины до приветственной скидки и саму скидку (15% на первые 2 заказа).
 */
class m260513_120000_orders_add_welcome_discount extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%orders}}', 'subtotal_amount', $this->decimal(12, 2)->notNull()->defaultValue(0));
        $this->addColumn('{{%orders}}', 'welcome_discount_amount', $this->decimal(12, 2)->notNull()->defaultValue(0));
        $this->execute('UPDATE {{%orders}} SET [[subtotal_amount]] = [[total_amount]], [[welcome_discount_amount]] = 0');
    }

    public function safeDown()
    {
        $this->dropColumn('{{%orders}}', 'welcome_discount_amount');
        $this->dropColumn('{{%orders}}', 'subtotal_amount');
    }
}
