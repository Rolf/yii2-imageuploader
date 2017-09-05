<?php
/**
 * Created by PhpStorm.
 * User: mkv
 * Date: 30.05.17
 * Time: 11:42
 */

namespace bubogumy;

use yii\base\Action;
use bubogumy\Image as ImageForm;
use yii\web\UploadedFile;

/**
 * Экшен для загрузки файлов на сервер
 *
 * Class UploadAction
 * @package app\components\uploader
 */

class UploadAction extends Action
{
    use UploadTrait;

    /**
     * @var string Название файла, если не задано сгенерируется рандомно
     */
    public $filename;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->filename = $this->filename ?? uniqid();
        UploadHelper::createDirectory(UploadHelper::uploadTempPath() . '/');

        parent::init();
    }

    /**
     * This method will be invoked by the controller when the action is requested.
     */
    public function run()
    {
        $imageForm = new ImageForm();
        $imageForm->file = UploadedFile::getInstanceByName('file');
        $imageForm->validate();

        $filename = $this->filename . '.' . $imageForm->file->extension;

        if (!$imageForm->file->saveAs(UploadHelper::uploadTempPath() . '/' . $filename)) {
            throw new UploadException(
                'humanFriendlyException.couldNotLoadImage',
                UploadException::API_COULD_NOT_LOAD_IMAGE
            );
        }

        $tempFileUrl = UploadHelper::baseUploadUrl() . UploadHelper::UPLOAD_TEMP_DIR . '/' . $filename;

        $result = [
            'filename' => $filename,
            'fileUrl' => $tempFileUrl,
            'preview_path' => $tempFileUrl,
        ];

        return $this->controller->render('index.php', $result);
    }
}