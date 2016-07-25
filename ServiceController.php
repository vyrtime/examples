<?php

namespace aig\crm_client_app\controllers;

use aig\core\models\MenuItem;
use Yii;
use aig\crm_client_app\models\Service;
use aig\crm_client_app\models\searchs\ServiceSearch;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;

/**
 * ServiceController implements the CRUD actions for Service model.
 */
class ServiceController extends CrmController
{
    /**
     * @return array
     */
    public function accessRules()
    {
        $service = MenuItem::find()->where(['url' => '/crm_client_app/service/index'])->one();
        return [
            [
                'allow' => true,
                'actions' => ['index'],
                'roles' => [$service->permission->name]
            ],
            [
                'allow' => true,
                'actions' => ['create', 'update', 'delete'],
                'roles' => ['crm.service.full']
            ],
        ];
    }

    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ]);
    }

    /**
     * Lists all Service models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ServiceSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'model' => new Service(['scenario'=>'create']),
        ]);
    }

    /**
     * Creates a new Service model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Service();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            return $this->renderAjax('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Service model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'update';

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Удаление модели
     * @param $id
     * @param bool $force
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDelete($id, $force = false)
    {
        $model = $this->findModel($id);
        if ($force)
            $model->detachBehavior('checkRelationBehavior');

        $model->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Service model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Service the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Service::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
