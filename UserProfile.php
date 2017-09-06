<?php

namespace bubogumy;

use bubogumy\UploadInterface;
use bubogumy\UploadTrait;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "lang_data.user_profile".
 *
 * @property int $id
 * @property string $name
 * @property string $logo
 * @property string $logo_prev
 */
class UserProfile extends ActiveRecord implements UploadInterface
{
    use UploadTrait;

    public $filename;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'lang_data.user_profile';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'logo', 'logo_prev'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function userFind()
    {
        UserProfile::find()
            ->all();
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'logo' => 'Logo',
            'logo_prev' => 'Logo Prev',
        ];
    }

    /**
     * ID пользователя
     *
     * @return int
     */
    public function getUserId()
    {
        return 1;
    }

    /**
     * Адрес до сурс картинки
     *
     * @return string
     */
    public function pathSettings()
    {
        return '/profile_logo/src/';
    }

    /**
     * @return bool
     */
    public function beforeUploadEvent()
    {
        return true;
    }

    /**
     * Адрес на превью картинку
     *
     * @return string
     */
    public function pathPreviewSettings()
    {
        return '/profile_logo/preview/';
    }

    /**
     * Атрибут сурс картинки
     *
     * @return string
     */
    public function attributeSettings()
    {
        return 'logo';
    }

    /**
     * Атрибут превью картинки
     *
     * @return string
     */
    public function attributePreviewSettings()
    {
        return 'logo_prev';
    }

    /**
     * Получение адреса из бд до сурса
     *
     * @return string
     */
    public function getFileSettings()
    {
        return $this->logo;
    }

    /**
     * Получение адреса из бд до превью
     *
     * @return string
     */
    public function getFilePreviewSettings()
    {
        return $this->logo_prev;
    }

    /**
     * Подпись на события
     */
    public function init()
    {
        $this->subscribeEvent();
        parent::init();
    }
}
