<?php
namespace shopium\mod\yml\components;

class XmlResponseFormatter extends \yii\web\XmlResponseFormatter {
    public $rootTag = 'yml_catalog';
    public $itemTag = 'offer';
}