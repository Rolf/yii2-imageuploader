<?php
/**
 * Created by PhpStorm.
 * User: kaiser
 * Date: 14.04.16
 * Time: 15:37
 */

namespace bubogumy;

use bubogumy\UploadHelper;
use Imagine\Exception\InvalidArgumentException;
use Imagine\Exception\RuntimeException;
use Imagine\Image\Box;
use yii\helpers\Json;
use yii\imagine\Image;

/**
 * Сервис для работы с изображениями
 * Содержит общие методы для всех случаев работы с изображениями
 *
 * todo Наследникам этого класса требуется рефакторинг
 */
class ImageService
{
    /**
     * Сгенерировать миниатюру и сохранить её в указанном месте
     * @param string $imagePath путь и имя файла полного изображения
     * @param string $previewPath путь и имя файла миниатюры
     */
    public function createPreview($imagePath, $previewPath)
    {
        $this->resizeImage($imagePath, $previewPath, 220, 220);
    }

    /**
     * Сгенерировать само изображение и сохранить его в указанном месте
     * @param string $imagePath путь и имя файла полного изображения
     * @param string $previewPath путь и имя файла изображения
     */
    protected function createImage($imagePath, $previewPath)
    {
        $this->resizeImage($imagePath, $previewPath, 1024, 1024);
    }

    /**
     * Загружается
     *
     * @param string $url
     * @return string
     * @throws \Exception
     */
    protected function downloadImage($url = null)
    {
        try {
            $imagine = Image::getImagine()
                ->open($url);

            $extension = explode('.', $url);
            $extension = $extension[sizeof($extension) - 1];

            $filename = 'offer_image_' . UploadHelper::getUnique() . '.' . $extension;

            $imagine->save(UploadHelper::uploadTempPath() . '/' . $filename);

            return $filename;
        } catch (InvalidArgumentException $e) {
            // TODO объединить: catch (InvalidArgumentException|RuntimeException $e)
            $lastError = error_get_last();
            \Yii::error(print_r($lastError, true), 'errorImageDownload');

            \Yii::error('', 'catalogImport');
            \Yii::error('Исключение', 'catalogImport');
            \Yii::error('Ошибка при загрузке изображения', 'catalogImport');
            \Yii::error('Сообщение: ' . $e->getMessage(), 'catalogImport');
            \Yii::error('Image url: ' . $url, 'catalogImport');
            \Yii::error('Код: ' . $e->getCode(), 'catalogImport');
            \Yii::error('Файл: ' . $e->getFile() . '(' . $e->getLine() . ')', 'catalogImport');
            \Yii::error('Трейс: ' . PHP_EOL . $e->getTraceAsString(), 'catalogImport');
            \Yii::error('', 'catalogImport');
            return null;
        } catch (RuntimeException $e) {
            $lastError = error_get_last();
            \Yii::error(print_r($lastError, true), 'errorImageDownload');

            \Yii::error('', 'catalogImport');
            \Yii::error('Исключение', 'catalogImport');
            \Yii::error('Ошибка при загрузке изображения', 'catalogImport');
            \Yii::error('Сообщение: ' . $e->getMessage(), 'catalogImport');
            \Yii::error('Image url: ' . $url, 'catalogImport');
            \Yii::error('Код: ' . $e->getCode(), 'catalogImport');
            \Yii::error('Файл: ' . $e->getFile() . '(' . $e->getLine() . ')', 'catalogImport');
            \Yii::error('Трейс: ' . PHP_EOL . $e->getTraceAsString(), 'catalogImport');
            \Yii::error('', 'catalogImport');

            return null;
        }
    }

    /**
     * Изменение размера изображения и сохранение его в указанном месте
     * @param string $imagePath путь и имя файла полного изображения
     * @param string $previewPath путь и имя файла изображения
     * @param integer $xSize по горизонтали
     * @param integer $ySize по вертикали
     */
    protected function resizeImage($imagePath, $previewPath, $xSize, $ySize)
    {
        Image::frame($imagePath)
            ->thumbnail(new Box($xSize, $ySize))
            ->save($previewPath, ['quality' => 50]);
    }
}