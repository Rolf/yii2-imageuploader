<?php

namespace bubogumy;

use yii\base\Model;

/**
 * Модель для валидации загружаемых изображений для верификации
 * @package app\forms
 */
class Image extends Model
{
    /** @var string Загружаемый файл */
    public $file;

    /** @var int Максимальный размер загружаемого файла в байтах */
    protected $maxImageSize;

    /**
     * @var string Ошибка что "Файл не должен быть больше 10MB"
     */
    protected $tooBigMessage;

    /**
     * @var int Максимальный размер загружаемого файла в байтах (10MB)
     */
    const MAX_IMAGE_SIZE = 10485760;

    /**
     * @var string Языковая метка, что "Файл не должен быть больше 50KB"
     */
    const TOO_BIG_MESSAGE_LANG_DATA = 'error.fileMaxSize';

    /**
     * Image constructor.
     * @param int $maxImageSize
     * @param string $tooBigMessage
     * @param array $config
     */
    public function __construct($maxImageSize = null, $tooBigMessage = null, array $config = [])
    {
        $this->maxImageSize = $maxImageSize ?? self::MAX_IMAGE_SIZE;
        $this->tooBigMessage = $tooBigMessage ?? self::TOO_BIG_MESSAGE_LANG_DATA;
        parent::__construct($config);
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['file'], 'image',
                'skipOnEmpty' => false,
                'extensions' => 'png, jpg, jpeg, gif',
                'maxSize' => $this->maxImageSize,
                'tooBig' => $this->tooBigMessage,
            ],
        ];
    }
}
