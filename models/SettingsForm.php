<?php

namespace shopium\mod\yml\models;

use Yii;
use panix\engine\SettingsModel;

class SettingsForm extends SettingsModel
{

    protected $module = 'yml';

    public $name;
    public $company;
    public $url;

    public static function defaultSettings()
    {
        return [
            'company' => 'Демо кампания',
            'url' => Yii::$app->request->hostInfo,
        ];
    }

    public function rules()
    {
        return [
            [['company', 'url'], 'string'],
            [['company', 'url'], 'required'],
        ];
    }

}
