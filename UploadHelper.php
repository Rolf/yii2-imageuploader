<?php

namespace bubogumy;

use yii\helpers\FileHelper;

/**
 * Методы для загрузки файлов на сервер от клиентов
 * @package app\helpers
 */
class UploadHelper extends FileHelper
{
    /** Ключ для хранения счетчиков */
    const RAND_INC_CACHE_KEY = 'file_rand_suffix_counter_';

    /** Права доступа - для всех */
    const PUBLIC_MODE = 0777;

    /** Директория для загрузки файлов */
    const UPLOAD_DIR = '/uploads';

    /** Директория для временных файлов */
    const UPLOAD_TEMP_DIR = '/temp';

    /** @var string $path */
    private static $path;

    /** @var string $url */
    private static $url;

    /**
     * Приватная директория для хранения файлов
     * @var string $privateDir
     */
    private static $privateDir = '/uploads';

    /**
     * Публичная директория для хранения файлов
     * @var string $publicDir
     */
    private static $publicDir = '/web';

    /**
     * Получить базовую директорию для загрузок, по умолчанию - директория проекта
     * По этому пути загружать нельзя, только в дочерние директории!
     *
     * @return string
     */
    public static function basePath()
    {
        if (self::$path === null) {
            self::$path = (\Yii::$app->params['baseUploadPath'] ?? '') ?: (\Yii::$app->basePath ?? dirname(__DIR__));
            if (\Yii::$app->params['envName'] == 'ci') {
                self::$path = (\Yii::$app->basePath ?? dirname(__DIR__)) . '/../static';
            }
        }

        return self::$path;
    }

    /**
     * Получить приватную директорию для загрузок
     * @return string
     */
    public static function privatePath()
    {
        return self::basePath() . self::$privateDir;
    }

    /**
     * Получить публичную директорию для загрузок
     * @return string
     */
    public static function publicPath()
    {
        return self::basePath() . self::$publicDir;
    }

    /**
     * Получить базовый url к статике
     *
     * @return string
     */
    public static function baseUrl()
    {
        if (self::$url === null) {
            self::$url = (\Yii::$app->params['staticUrl'] ?? '') ?: \Yii::$app->params['url'];
        }

        return self::$url;
    }

    /**
     * Получить базовый url к директории с загруженными файлами
     *
     * @return string
     */
    public static function baseUploadUrl()
    {
        return self::baseUrl() . self::UPLOAD_DIR;
    }

    /**
     * Получить публичную директорию для временных файлов
     *
     * @return string
     */
    public static function uploadTempPath()
    {
        return self::publicPath() . self::UPLOAD_DIR . self::UPLOAD_TEMP_DIR;
    }

    /**
     * Получить уникальную строку для имени файла
     * Уникальность гарантируется счетчиком, который обнуляется не чаще двух секунд, и меткой времени
     * Рандомное число оставлено для видимости случайного характера имен загружаемых файлов
     *
     * @return string
     */
    public static function getUnique()
    {
        $rand = rand(1000, 9999);
        $counter = \Yii::$app->cache->increment(self::RAND_INC_CACHE_KEY, 2);
        $time = time();

        return $rand . $counter . '_' . $time;
    }

    /**
     * Права по умолчанию - rwxrwxrwx
     * (в отличие от родительского метода - rwxrwxr-x)
     * @inheritdoc
     */
    public static function createDirectory($path, $mode = self::PUBLIC_MODE, $recursive = true)
    {
        return parent::createDirectory($path, $mode, $recursive);
    }

    /**
     * Сделать урл абсолютным
     * @param string $url
     * @return string
     */
    public static function setUrl($url)
    {
        if (null === $url || '' === $url) {
            return $url;
        }

        $isAbsolute = (1 === preg_match('/^http[s]?:\/\//', $url));

        return $isAbsolute ? $url : self::baseUrl() . $url;
    }

    /**
     * Проверка на существование файла в статик директории
     *
     * @param $filePath string путь до файла начиная с рута статуи директории
     * @return bool
     */
    public static function fileExists($filePath)
    {
        return file_exists(UploadHelper::basePath() . $filePath);
    }

    /**
     * Сгенерировать путь до папки пользователя определенной сущности
     * Пример: /uploads/00/00/0b/profile_logo/src/90/73/e0/profile_logo_84501_1496391739.png
     *
     * @param int $userId ID пользователя
     * @param string $filename Название файла
     * @param string $path Путь до папки хранения файла
     * @return string
     */
    public static function generatePath(int $userId, string $filename, string $path)
    {
        return UploadHelper::UPLOAD_DIR . DIRECTORY_SEPARATOR
        . UploadHelper::getHexPath($userId)
        . $path
        . UploadHelper::getMd5Path($filename) . DIRECTORY_SEPARATOR;
    }

    /**
     * Генерирует и возвращает путь на основе переданного значения с помощью HEX конвертации
     * Пример: 2094 => 000/000/82e
     * @param int $number Число для генерации hex
     * @param int $deep Глубина вложенности
     * @param int $digitLen Длина разрядов
     * @return string
     */
    public static function getHexPath(int $number, int $deep = 3, int $digitLen = 2)
    {
        $hexValue = dechex($number);

        while (($length = strlen($hexValue)) < $deep * $digitLen) {
            $hexValue = '0' . $hexValue;
        }

        return self::getPath($hexValue, strlen($hexValue), $digitLen);
    }

    /**
     * Генерирует и возвращает путь на основе переданного значения
     * Пример: 5a039d78af643039881750fb3cf2a36c => 5a/03/9d
     * @param string $value Строка для разбивки на разряды
     * @param int $deep Глубина вложенности
     * @param int $digitLen Длина разрядов
     * @return string
     */
    public static function getMd5Path(string $value, int $deep = 3, int $digitLen = 2)
    {
        return self::getPath(md5($value), $deep * $digitLen, $digitLen);
    }

    /**
     * Разбивает строку на разряды и возвращает путь
     * Пример: aabbcc => aa/bb/cc
     * @param string $value Строка для разбивки на разряды
     * @param int $length Длина конечной строки
     * @param int $digitLen Длина разрядов
     * @return string
     */
    private static function getPath($value, $length, $digitLen)
    {
        $path = '';
        for ($i = 0, $curDigitLen = 0; $i < $length; $i++, $curDigitLen++) {
            if ($curDigitLen === $digitLen) {
                $path .= '/' . $value[$i];
                $curDigitLen = 0;
            } else {
                $path .= $value[$i];
            }
        }

        return $path;
    }
}
