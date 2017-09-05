<?php
/**
 * Created by PhpStorm.
 * User: mkv
 * Date: 30.05.17
 * Time: 14:39
 */

namespace bubogumy;

use yii\db\ActiveRecord;
use yii\db\AfterSaveEvent;

/**
 * Поведение для загрузки файлов
 *
 * !!! Для корректной работы поведения обязательно нужно использовать интерфейс UploadInterface
 * в модели к которой относится поведение
 * Class UploadBehavior
 * @package app\components\uploader
 * @property ActiveRecord|UploadInterface $owner Модель к которой относится поведение
 */
trait UploadTrait
{
    /**
     * @var bool Загрузка только изображений
     */
    public $imageOnly = true;

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => 'beforeInsert',
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeUpdate',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
            ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
        ];
    }

    public function subscribeEvent()
    {
        $this->on(ActiveRecord::EVENT_BEFORE_INSERT, [$this, 'beforeInsert']);
        $this->on(ActiveRecord::EVENT_AFTER_INSERT, [$this, 'afterInsert']);
        $this->on(ActiveRecord::EVENT_BEFORE_UPDATE, [$this, 'beforeUpdate']);
        $this->on(ActiveRecord::EVENT_AFTER_UPDATE, [$this, 'afterUpdate']);
        $this->on(ActiveRecord::EVENT_AFTER_DELETE, [$this, 'afterDelete']);
    }

    /**
     * Вызов метода до создания записи
     */
    public function beforeInsert()
    {
        if ($this->beforeUploadEvent() && is_file($this->getTempFile())) {
            $this->generatePath();
        }
    }

    /**
     * Вызов метода после создания записи
     * @param AfterSaveEvent $event Объект события
     */
    public function afterInsert(AfterSaveEvent $event)
    {
        if ($this->beforeUploadEvent() && $this->attributeSettings()) {
            $this->saveFile(true, $event->changedAttributes);
        }
    }

    /**
     * Вызов метода до редактирования записи
     */
    public function beforeUpdate()
    {
        $isChanged = $this->isAttributeChanged($this->attributeSettings());
        if ($this->beforeUploadEvent() && is_file($this->getTempFile()) && $isChanged) {
            $this->generatePath();
        }
    }

    /**
     * Вызов метода после редактирования записи
     * @param AfterSaveEvent $event Объект события
     */
    public function afterUpdate(AfterSaveEvent $event)
    {
        if ($this->beforeUploadEvent() && array_key_exists($this->attributeSettings(), $event->changedAttributes)) {
            $this->saveFile(false, $event->changedAttributes);
        }
    }

    /**
     * Вызов метода после удаления записи
     */
    public function afterDelete()
    {
        $this->deleteFile(UploadHelper::publicPath() . $this->preparePath($this->attributeSettings()));
        if ($this->imageOnly) {
            $this->deleteFile(UploadHelper::publicPath() .$this->preparePath($this->attributePreviewSettings()));
        }
    }

    /**
     * Получить путь до файла оригинала
     * @param string $filename Название файла
     * @return string
     */
    protected function getSrcPath(string $filename)
    {
        return $this->getPath($filename, $this->pathSettings()) . $filename;
    }

    /**
     * Получить путь до файла превью
     * @param string $filename Название файла
     * @return string
     */
    protected function getPreviewPath(string $filename)
    {
        return $this->getPath($filename, $this->pathPreviewSettings()) . $filename;
    }

    /**
     * Получить url до файла
     *
     * @param string $filename Название файла
     * @param string $path Путь до папки хранения файла
     * @return string
     */
    protected function getPath(string $filename, string $path)
    {
        return UploadHelper::baseUrl() . UploadHelper::generatePath($this->getUserId(), $filename, $path);
    }

    /**
     * Сгенерировать и записать путь до файла
     */
    protected function generatePath()
    {
        try {
            $filename = $this->getAttribute($this->attributeSettings());

            if ($filename) {
                $filename = $this->filtering($filename);
                $this->setAttribute($this->attributeSettings(), $this->getSrcPath($filename));

                if ($this->imageOnly) {
                    $this->setAttribute($this->attributePreviewSettings(), $this->getPreviewPath($filename));
                }
            }
        } catch (\Exception $e) {
            \Yii::error('');
            \Yii::error('Исключение!');
            \Yii::error('Сообщение: ' . $e->getMessage());
            \Yii::error('Код: ' . $e->getCode());
            \Yii::error('Файл: ' . $e->getFile() . '(' . $e->getLine() . ')');
            \Yii::error('Трейс: ' . PHP_EOL . $e->getTraceAsString());
            \Yii::error('');

            throw new UploadException(
                'humanFriendlyException.couldNotLoadImage',
                UploadException::API_COULD_NOT_LOAD_IMAGE
            );
        }
    }

    /**
     * Фильтрация наименования файла от лишних символов
     * Допустимые символы: буквы, цифры, точка, _
     * @param array $fileName Значение для фильтрации
     * @return string
     */
    protected function filtering($fileName)
    {
        return preg_replace('/[^a-zA-Z\d_.]|\.{2,}/iu', '', $fileName);
    }

    /**
     * Сохранить файл
     *
     * @param bool $insert true при создании
     * @param array $changedAttributes Массив измененных атрибутов
     * @throws UploadException
     */
    protected function saveFile(bool $insert, array $changedAttributes = [])
    {
        try {
            $image = $this->getFileSettings();

            if (empty($image) && !$insert) {
                $this->deleteOldFiles($changedAttributes);
            } else {
                $file = UploadHelper::publicPath() . $this->preparePath($image);
                $tempFile = $this->getTempFile();

                if (is_file($tempFile)) {
                    UploadHelper::createDirectory(dirname($file));
                    copy($tempFile, $file);

                    // Если включен режим только изображений, то создаем превью для изображения
                    if ($this->imageOnly) {
                        $imagePreview = $this->getFilePreviewSettings();

                        $filePreview = UploadHelper::publicPath() .  $this->preparePath($imagePreview);

                        UploadHelper::createDirectory(dirname($filePreview));
                        (new ImageService())->createPreview($file, $filePreview);
                    }

                    // Если процесс редактирования, то удаляем старый файл
                    if (!$insert && $this->getOldAttribute($this->attributeSettings())) {
                        $this->deleteOldFiles($changedAttributes);
                    }

                    $this->deleteFile($tempFile);
                }
            }
        } catch (\Exception $e) {
            \Yii::error('');
            \Yii::error('Исключение!');
            \Yii::error('Сообщение: ' . $e->getMessage());
            \Yii::error('Код: ' . $e->getCode());
            \Yii::error('Файл: ' . $e->getFile() . '(' . $e->getLine() . ')');
            \Yii::error('Трейс: ' . PHP_EOL . $e->getTraceAsString());
            \Yii::error('');

            throw new UploadException(
                'humanFriendlyException.couldNotLoadImage',
                UploadException::API_COULD_NOT_LOAD_IMAGE
            );
        }
    }

    /**
     * Получить путь до временного файла
     * @return string
     */
    protected function getTempFile()
    {
        UploadHelper::createDirectory(UploadHelper::uploadTempPath());
        return UploadHelper::uploadTempPath() . DIRECTORY_SEPARATOR . basename($this->getAttribute($this->attributeSettings()));
    }

    /**
     * Удалить старые файлы
     * @param array $changedAttributes Массив измененных атрибутов
     */
    protected function deleteOldFiles(array $changedAttributes)
    {
        $this->deleteFile(UploadHelper::publicPath() . $this->preparePath($changedAttributes[$this->attributeSettings()]));
        if ($this->imageOnly) {
            $this->deleteFile(
                UploadHelper::publicPath() . $this->preparePath($changedAttributes[$this->attributeSettings()])
            );
        }
    }

    /**
     * Удалить файл
     *
     * @param string $file Путь до файла
     * @return bool
     */
    protected function deleteFile($file)
    {
        return is_file($file) ? @unlink($file) : false;
    }

    /**
     * Подготовить путь до существующей картинки:
     * - Убрать url до статики из пути
     * - Убрать домен из пути
     * @param string $url Url картинки
     * @return string
     */
    private function preparePath($url)
    {
        $pattern = '/' . preg_quote(\Yii::$app->params['staticUrl'], '/') . '/';
        return !empty(\Yii::$app->params['staticUrl'])
            ? preg_replace($pattern, '', $url) : parse_url($url, PHP_URL_PATH);
    }
}