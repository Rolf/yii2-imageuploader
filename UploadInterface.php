<?php
/**
 * Created by PhpStorm.
 * User: mkv
 * Date: 30.05.17
 * Time: 17:13
 */

namespace bubogumy;

/**
 * Интерфейс для загрузки файлов
 * Interface UploadInterface
 * @package app\components\uploader
 */
interface UploadInterface
{
    /**
     * Получить ID пользователя
     * @return int
     */
    public function getUserId();

    /**
     * Действие перед событием загрузки файла
     * Если метод вернет true, то далее выполнится алгоритм сохранения файла,
     * иначе сохранение файла не произойдет, при этом ошибки не будет
     * @return bool
     */
    public function beforeUploadEvent();

    public function pathSettings();

    public function pathPreviewSettings();

    public function attributeSettings();

    public function getFileSettings();

    public function attributePreviewSettings();
}
