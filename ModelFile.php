<?php

namespace aig\crm_client_app\models\behaviors;

use aig\crm_client_app\models\File;
use yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;

/**
 * Class ModelFile
 * Модель поведения при добавлении файлов
 * @package aig\crm_client_app\models\behaviors
 */
class ModelFile extends Behavior
{
    public $attribute = 'files'; // атрибут файла
    public $type = 'default'; // тип
    public $tmp_path = '@app/lib/aig/crm_client_app/assets/tmp/'; // путь до временной папки
    public $file_path = '@app/lib/aig/crm_client_app/assets/files/'; // путь до папки сохранения
    public $model;

    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_UPDATE => 'saveFiles',
            ActiveRecord::EVENT_AFTER_INSERT => 'saveFiles',
        ];
    }

    /**
     * Сохранение файлов
     * @param $event
     */
    public function saveFiles($event)
    {
        if (!empty($this->owner->model) && !empty($_POST[$this->owner->model][$this->attribute])) {
            $tmp_path = Yii::getAlias($this->tmp_path);
            $file_path = Yii::getAlias($this->file_path);
            foreach ($_POST[$this->owner->model][$this->attribute] as $file) {
                if (!empty($file)) {
                    $f = new File();
                    $f->object_id = $this->owner->id;
                    $f->type = $this->type;
                    $f->name = $file;
                    $f->user_id = Yii::$app->user->id;
                    $f->date = date('Y-m-d H:i:s');
                    if (is_file($tmp_path . $file)) {
                        chmod($tmp_path . $file, 0777);
                        rename($tmp_path . $file, $file_path . $file);
                        $f->save();
                    }
                }
            }
        }
    }

}