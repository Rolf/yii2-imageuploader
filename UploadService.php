<?php
/**
 * Created by PhpStorm.
 * User: artyom
 * Date: 24.08.17
 * Time: 12:09
 */

namespace bubogumy;

use bubogumy\Image as ImageForm;
use yii\web\UploadedFile;

class UploadService
{
    public $filename;

    public function run()
    {
        $file = new UploadAction(1, 'site');

        $imageForm = new ImageForm();
        $imageForm->file = UploadedFile::getInstanceByName('file');
        $imageForm->validate();

        $filename = $file->filename;

        if (!$imageForm->file->saveAs(UploadHelper::uploadTempPath() . '/' . $filename)) {
            throw new UploadException(
                'Ошибка, файл не может быть загружен',
                UploadException::API_COULD_NOT_LOAD_IMAGE
            );
        }

        $tempFileUrl = UploadHelper::baseUploadUrl() . UploadHelper::UPLOAD_TEMP_DIR . '/' . $filename;

        //       \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $result = [
            'filename' => $filename,
            'fileUrl' => $tempFileUrl,
            'preview_path' => $tempFileUrl,
        ];

        return $result;
    }
}