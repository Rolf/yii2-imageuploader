## Расширение для загрузки изображений
Для корректной работы необходимо установить [Imagine Extension for Yii 2](http://www.yiiframework.com/doc-2.0/ext-imagine-index.html)  

1. Добавляем в параметры следующие ключи:

````
    'Url' => '',
    'StaticUrl' => 'адрес вашего сайта',
    'baseUploadPath' => ''
````  
2. Накатываем миграцию из папки migrations
```
./yii migrate --migrationPath=@vendor/bubogumy/imageuploader/migrations
```

3. В controller/SiteController.php в метод ``actions`` добавляем ``'upload' => 'app\services\UploadAction'``  
Так же правим метод actionIndex и вставляем следующее  
````
    $model = new UserProfile();
    
    if ($model->load(Yii::$app->request->post()) && $model->save()) {
        return $this->goBack();
    }
    return $this->render('index', [
        'model' => $model,
    ]);
````
4. В UserProfile.php в методе ``tableName`` пишем в самом начале название своей базы данных  

5. Даем права на создание папок в директории.  

### Как работает:  
В ``index.php`` присваиваем ``$model = new UserProfile();``  
Подключаем [jQuery] для работы Ajax запросов  
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


