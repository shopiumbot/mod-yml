<?php

namespace shopium\mod\yml;

use Yii;
use yii\base\BootstrapInterface;
use panix\engine\WebModule;
use panix\mod\admin\widgets\sidebar\BackendNav;

class Module extends WebModule implements BootstrapInterface
{

    public $icon = 'yandex';

    public function bootstrap($app)
    {
        $app->urlManager->addRules(
            [
                'yml.xml' => 'yml/default/index',
            ]
        );
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
