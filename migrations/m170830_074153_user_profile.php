<?php

use yii\db\Migration;

/**
 * Class m170830_074153_user_profile
 */
class m170830_074153_user_profile extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('user_profile',[
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'logo' => $this->string(),
            'logo_prev' => $this->string(),
        ]);


    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('user_profile');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m170830_074153_user_profile cannot be reverted.\n";

        return false;
    }
    */
}
