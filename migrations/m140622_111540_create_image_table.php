<?php

use yii\db\Schema;

class m140622_111540_create_image_table extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('image', [
            'id' => 'pk',
            'file_path' => 'VARCHAR(400) NOT NULL',
            'item_id' => 'int(11) NOT NULL',
            'is_main' => 'tinyint(1) NOT NULL DEFAULT 0',
            'model_name' => 'VARCHAR(150) NOT NULL',
            'url_alias' => 'VARCHAR(400) NOT NULL',
        ]);

    }

    public function down()
    {
        echo "m140622_111540_create_image_table cannot be reverted.\n";

        return false;
    }
}
