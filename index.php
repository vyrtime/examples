<?php

use yii\helpers\Html;
use yii\grid\GridView;
use \yii\bootstrap\Modal;
use \yii\bootstrap\Button;
use aig\crm_client_app\models\CrmAsset;
use aig\crm_client_app\models\Service;
use aig\crm_client_app\models\ColorboxAsset;

ColorboxAsset::register($this);

/* @var $this yii\web\View */
/* @var $searchModel aig\crm_client_app\models\searchs\ServiceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Услуги';
$this->params['breadcrumbs'][] = $this->title;
CrmAsset::register($this);
?>
<div class="service-index">

    <?php Modal::begin([
        'id' => 'addService',
        'header' => '<h4>Добавить услугу</h4>',
        'clientOptions' => false,
    ]); ?>

    <div class="modal-body">
        <?php echo $this->render('_form', ['model'=>$model]); ?>
    </div>

    <?php Modal::end(); ?>

    <h1>
        <?= Html::encode($this->title) ?>
        <?php
            if (Yii::$app->user->can('crm.service.full')) {
                echo Button::widget([
                    'label' => 'Добавить услугу',
                    'options' => [
                        'data-toggle' => 'modal',
                        'data-target' => '#addService',
                        'class' => 'pull-right btn btn-primary',
                    ]
                ]);
            }
        ?>
    </h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?php $session = Yii::$app->session; ?>
    <?php if ($session->getFlash('checkRelations')) : ?>
        <?= '<div class="alert alert-danger">'. $session->getFlash('checkRelations') .'</div>'; ?>
    <?php endif; ?>

    <?= GridView::widget([
        'id' => 'service-list',
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        'columns' => [
            [
                'header'=>'Наименование',
                'attribute'=>'name',
                'format'=>'html',
                'value'=>function($dataProvider) {
                    return $dataProvider['parent_id'] ? $dataProvider['name'] : '<strong>' . $dataProvider['name'] . '</strong>';
                },
                'filter' => false,
            ],
            [
                'header'=>'Шаблоны КП',
                'format'=>'html',
                'value'=>function($dataProvider) {
                    if ($dataProvider['parent_id'])
                        return Html::a(
                            Service::getFilesCount($dataProvider['id'], 'service-cp'),
                            ['/crm_client_app/crm/show-files', 'id' => $dataProvider['id'], 'type' => 'service-cp'],
                            ['class' => 'ajax-link', 'title' => 'Шаблоны КП']
                        );
                    else
                        return '';
                }
            ],
            [
                'header'=>'Шаблоны договора',
                'format'=>'html',
                'value'=>function($dataProvider) {
                    if ($dataProvider['parent_id'])
                        return Html::a(
                            Service::getFilesCount($dataProvider['id'], 'service-contract'),
                            ['/crm_client_app/crm/show-files', 'id' => $dataProvider['id'], 'type' => 'service-contract'],
                            ['class' => 'ajax-link', 'title' => 'Шаблоны договора']
                        );
                    else
                        return '';
                }
            ],
            [
                'header' => 'Расчет стоимости',
                'format' => 'html',
                'value' => function($dataProvider) {
                    if ($dataProvider['formula'] != 0) {
                        if ($dataProvider['formula_type'] == '%') {
                            return '+'. $dataProvider['formula'] . $dataProvider['formula_type'];
                        } else {
                            return '+'. $dataProvider['formula'];
                        }
                    } else {
                        return 0;
                    }
                },
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update} {delete}',
                'buttons' => [
                    'update'=>function ($url, $model) {
                        if (Yii::$app->user->can('crm.service.full')) {
                            $customurl = ['update', 'id' => $model['id']];
                            return \yii\helpers\Html::a('<span class="glyphicon glyphicon-pencil"></span>', $customurl,
                                [
                                    'title' => Yii::t('yii', 'Редактировать'),
                                ]);
                        }
                    },
                    'delete'=>function ($url, $model) {
                        if (Yii::$app->user->can('crm.service.full')) {
                            $customurl = ['delete', 'id' => $model['id']];
                            return \yii\helpers\Html::a('<span class="glyphicon glyphicon-trash"></span>', $customurl,
                                [
                                    'title' => Yii::t('yii', 'Редактировать'),
                                    'data-pjax' => '0',
                                    'data-method' => 'post',
                                    'data-confirm' => 'Вы уверены, что хотите удалить этот элемент?',
                                    'aria-label' => 'Удалить'
                                ]);
                        }
                    }
                ],
            ],
        ],
    ]); ?>

</div>

<?= $this->render('/crm/_modalWindow'); ?>
