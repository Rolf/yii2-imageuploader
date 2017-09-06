## Расширение для загрузки изображений
Для корректной работы необходимо установить [Imagine Extension for Yii 2](http://www.yiiframework.com/doc-2.0/ext-imagine-index.html)  

#### Установка  
В composer.json  
```
"require": {
    "bubogumy/imageuploader": "dev-master"
}
```  
В терминале ``composer require bubogumy/imageuploader``  

#### Настройка  

1. Добавляем в компонент, в конфиге:

````
    'imageUploader' => [
                'class' => 'bubogumy\imageService',
                'url' => '',
                'staticUrl' => 'http://localhost/web',
                'baseUploadPath' => ''
            ],
````  

2. В нужном контроллере делаем перенаправление на наш класс ``'upload' => 'bubogumy\UploadAction'``
 
В нужный метод добавляем 
````
    $model = new UserProfile();
    
    if ($model->load(Yii::$app->request->post()) && $model->save()) {
        return $this->goBack();
    }
    return $this->render('index', [
        'model' => $model,
    ]);
````
3. Создаем модель нашей таблицы, наследовав ``ActiveRecord``, подключив на трейт ``UploadTrait`` и реализировав интерфейсный класс ``UploadInterface`` с описанием событие. Так же нужно подписаться на события.  

4. Даем права на создание папок в директории.  

5.  Файл ``clear-temp`` чистит автоматически папку ``temp``

### Пример использования:  

Описание моей модели:  
````
namespace app\models;

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
````

В ``index.php`` присваиваем ``$model = new bubogumy\UserProfile();``  
Подключаем [jQuery](https://jquery.com/) для работы Ajax запросов  
Создаем форму  
````
    <form enctype="multipart/form-data" method="post" action="/site/upload" id="form">
        <input type="file" name="file">
        <input type="hidden" name="_csrf" value="<?=Yii::$app->request->getCsrfToken()?>" />
        <input type="submit" value="Загрузить" class="btn">
    </form>
````  
Добавляем Ajax  
````
    <script>
        $(function(){
            $('#form').on('submit', function(e){
                e.preventDefault();
                var $that = $(this),
                formData = new FormData($that.get(0));
                $.ajax({
                    url: $that.attr('action'),
                    type: $that.attr('method'),
                    contentType: false,
                    processData: false,
                    data: formData,
                    dataType: 'html',
                    success: function(form){
                        if(form){
                            $that.replaceWith(form);
                        }
                    }
                });
            });
        });
    </script>
````  
Для отображений Преьвю и загрузки картинки добавляем 

````
    <?php if (isset($fileUrl)) :?>
        <?php $form = ActiveForm::begin(['action' => ['index']]); ?>
            <img src="<?= $fileUrl ?>" height="100px">
            <?= $form->field($model, 'logo')->hiddenInput(['value' => $filename]) ?>
            <?= $form->field($model, 'logo_prev')->hiddenInput(['value' => $filename]) ?>
            <div class="form-group">
                <?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
            </div>
        <?php ActiveForm::end(); ?>
    <?php endif; ?>
````


