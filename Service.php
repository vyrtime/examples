<?php

namespace aig\crm_client_app\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * This is the model class for table "service".
 *
 * @property integer $id
 * @property string $name
 * @property integer $parent_id
 * @property Service $parent
 * @property Service[] $services
 */
class Service extends \aig\core\components\model\ActiveRecord
{
    public $cp_files=[];
    public $contract_files=[];
    public $subservices=[];
    public $old_subservices=[];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'service';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['parent_id'], 'integer'],
            [['formula'], 'integer'],
            [['name'], 'string', 'max' => 128],
            [['cpFiles', 'contractFiles', 'formula_type'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['update'] = ['name', 'formula', 'formula_type'];
        return $scenarios;
    }

    public function behaviors()
    {
        return [
            'cp_file' => [
                'class' => 'aig\crm_client_app\models\behaviors\ModelFile',
                'attribute' => 'cp_files',
                'type' => 'service-cp',
                'model' => 'Service',
            ],
            'contract_files' => [
                'class' => 'aig\crm_client_app\models\behaviors\ModelFile',
                'attribute' => 'contract_files',
                'type' => 'service-contract',
                'model' => 'Service',
            ],
            'checkRelationBehavior' => [
                'class' => 'aig\crm_client_app\models\behaviors\CheckRelationBehavior',
                'relations' => [
                    'servicesDeals' => [
                        'model_name' => 'У сделок',
                        'field_name' => 'услуги',
                    ],
                    'subservicesDeals' => [
                        'model_name' => 'У сделок',
                        'field_name' => 'подуслуги',
                    ],
                ],
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Наименование',
            'subservices' => 'Подуслуга',
            'old_subservices' => 'Подуслуга',
            'cp_files' => 'Шаблоны КП',
            'contract_files' => 'Шаблоны договора',
            'formula_type' => 'Тип расчета',
            'formula' => 'Расчет стоимости',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(Service::className(), ['id' => 'parent_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServices()
    {
        return $this->hasMany(Service::className(), ['parent_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCpFiles()
    {
        return $this->hasMany(File::className(), ['object_id' => 'id'])->where(['type' => 'service-cp']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContractFiles()
    {
        return $this->hasMany(File::className(), ['object_id' => 'id'])->where(['type' => 'service-contract']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDealServices()
    {
        return $this->hasMany(DealSubserviceCost::className(), ['service_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServicesDeals()
    {
        return $this->hasMany(Deal::className(), ['id' => 'deal_id'])->via('dealServices');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDealSubservices()
    {
        return $this->hasMany(DealSubserviceCost::className(), ['subservice_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubservicesDeals()
    {
        return $this->hasMany(Deal::className(), ['id' => 'deal_id'])->via('dealSubservices');
    }

    /**
     * Вывод количества файлов по типу
     * @param $id
     * @param $type
     * @return int|string
     */
    public static function getFilesCount($id, $type)
    {
        return File::find()->where(['type' => $type, 'object_id' => $id])->count();
    }

    public function afterSave ($insert, $changedAttributes)
    {
        $this->updateItems();
    }

    /**
     * Обновление подуслуг
     */
    public function updateItems()
    {
        if ($this->parent_id === null) {
            $items = Yii::$app->request->post('Service', []);
            if (!empty($items)) {
                if (isset($items['subservices'])) {
                    foreach ($items['subservices'] as $item) {
                        if (!empty($item)) {
                            $i = new Service();
                            $i->parent_id = $this->id;
                            $i->name = $item;
                            $i->save();
                        }
                    }
                }
                if (isset($items['old_subservices'])) {
                    foreach ($items['old_subservices'] as $id => $item) {
                        if (!empty($item)) {
                            $i = Service::findOne($id);
                            $i->name = $item;
                            $i->save();
                        }
                    }
                }
            }
        }
    }

}
