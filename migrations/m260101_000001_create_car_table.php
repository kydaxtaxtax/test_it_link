<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Создание таблицы car (объявления автомобилей).
 */
final class m260101_000001_create_car_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%car}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'description' => $this->text()->notNull(),
            'price' => $this->decimal(12, 2)->notNull(),
            'photo_url' => $this->string()->notNull(),
            'contacts' => $this->string()->notNull(),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('NOW()'),
        ]);
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%car}}');
    }
}
