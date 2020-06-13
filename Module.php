<?php

namespace shopium\mod\yml;

use Yii;
use yii\base\BootstrapInterface;
use panix\engine\WebModule;
use panix\mod\admin\widgets\sidebar\BackendNav;
use yii\web\GroupUrlRule;

class Module extends WebModule implements BootstrapInterface
{

    public $icon = 'yandex';

    public function bootstrap($app)
    {
        $app->urlManager->addRules(
            [
                'yml.xml' => 'yml/default/process',
            ]
        );


        $groupUrlRule = new GroupUrlRule([
            'prefix' => $this->id,
            'rules' => [
                '<controller:[0-9a-zA-Z_\-]+>' => '<controller>/index',
                '<controller:[0-9a-zA-Z_\-]+>/<action:[0-9a-zA-Z_\-]+>' => '<controller>/<action>',
                //'<action:\w+>' => 'default/<action>',

            ],
        ]);
        $app->getUrlManager()->addRules($groupUrlRule->rules, false);

    }

    public function getAdminMenu()
    {
        return [
            'shop' => [
                'items' => [
                    'integration' => [
                        'items' => [
                            [
                                'label' => Yii::t('yml/default', 'MODULE_NAME'),
                                'url' => ['/admin/yml'],
                                'icon' => $this->icon,
                            ],
                        ]
                    ]
                ]
            ]
        ];
    }

    public function getAdminSidebar()
    {
        return (new BackendNav())->findMenu('shop')['items'];
    }

    public function getInfo()
    {
        return [
            'label' => Yii::t('yml/default', 'MODULE_NAME'),
            'author' => 'andrew.panix@gmail.com',
            'version' => '1.0',
            'icon' => $this->icon,
            'description' => Yii::t('yml/default', 'MODULE_DESC'),
            'url' => ['/admin/yml'],
        ];
    }
}
