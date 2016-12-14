<?php

use yii\db\Migration;

/**
 * Handles the creation of table `pictures`.
 */
class m161213_232613_create_pictures_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('pictures', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'image' => $this->string()->notNull(),
        ]);

        // creates index for column `user_id`
        $this->createIndex(
            'idx-pictures-user_id',
            'pictures',
            'user_id'
        );

        // add foreign key for table `user`
        $this->addForeignKey(
            'fk-pictures-user_id',
            'pictures',
            'user_id',
            'user',
            'id',
            'CASCADE'
        );

    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        // drops foreign key for table `user`
        $this->dropForeignKey(
            'fk-pictures-user_id',
            'pictures'
        );

        // drops index for column `author_id`
        $this->dropIndex(
            'idx-pictures-user_id',
            'pictures'
        );

        $this->dropTable('pictures');
    }
}
