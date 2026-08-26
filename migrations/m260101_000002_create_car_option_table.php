<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Создание таблицы car_option (тех. характеристики объявления).
 */
final class m260101_000002_create_car_option_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%car_option}}', [
            'id' => $this->primaryKey(),
            'car_id' => $this->integer()->notNull(),
            'brand' => $this->string()->notNull(),
            'model' => $this->string()->notNull(),
            'year' => $this->integer()->notNull(),
            'body' => $this->string()->notNull(),
            'mileage' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-car_option-car_id',
            '{{%car_option}}',
            'car_id',
            '{{%car}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createIndex('idx-car_option-car_id', '{{%car_option}}', 'car_id');
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk-car_option-car_id', '{{%car_option}}');
        $this->dropTable('{{%car_option}}');
    }
}
