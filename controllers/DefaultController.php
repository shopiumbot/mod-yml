<?php

namespace shopium\mod\yml\controllers;

use shopium\mod\yml\components\YML;
use Yii;
use core\components\controllers\AdminController;
use shopium\mod\yml\models\SettingsForm;

class DefaultController extends AdminController {

    public function actionIndex() {

        $this->pageName = Yii::t('yml/default', 'MODULE_NAME');

        $this->breadcrumbs[] = [
            'label' => Yii::t('shop/default', 'MODULE_NAME'),
            'url' => ['/admin/shop']
        ];
        $this->breadcrumbs[] = $this->pageName;
        $model = new SettingsForm;

        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                $model->save();
            }
            $this->refresh();
        }
        return $this->render('index', ['model' => $model]);
    }
    public function actionProcess() {

        //Yii::$app->log->targets['file1']->enabled = false;
        //Yii::$app->log->targets['file2']->enabled = false;
        //Yii::$app->log->targets['file3']->enabled = false;
        //Yii::$app->log->targets['file4']->enabled = false;
        //Yii::$app->response->format = Response::FORMAT_XML;
        $xml = new YML();
        $xml->processRequest();
    }
}
