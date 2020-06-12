<?php

namespace shopium\mod\yml\controllers;

use shopium\mod\yml\components\YML;
use Yii;
use yii\web\Controller;
use yii\web\Response;

class DefaultController extends Controller {

    public function actionIndex() {

        //Yii::$app->log->targets['file1']->enabled = false;
        //Yii::$app->log->targets['file2']->enabled = false;
        //Yii::$app->log->targets['file3']->enabled = false;
        //Yii::$app->log->targets['file4']->enabled = false;
        //Yii::$app->response->format = Response::FORMAT_XML;
        $xml = new YML;
        $xml->processRequest();
    }

}
