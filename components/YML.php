<?php

namespace shopium\mod\yml\components;


use panix\mod\shop\models\Attribute;
use panix\mod\shop\models\Currency;
use panix\mod\shop\models\Manufacturer;
use Yii;
use yii\helpers\Url;
use panix\engine\Html;
use panix\mod\shop\models\Category;
use panix\mod\shop\models\Product;
use panix\mod\shop\components\AttributeData;
use yii\helpers\VarDumper;

/**
 * Exports products catalog to YML format.
 */
class YML
{

    /**
     * @var int Maximum loaded products per one query
     */
    public $limit = 2;

    /**
     * @var string Default currency
     */
    //public $currencyIso = 'UAH';

    /**
     * @var string
     */
    public $cacheFileName = 'yml.xml';

    /**
     * @var string
     */
    public $cacheDir = '@runtime';

    /**
     * @var int
     */
    public $cacheTimeout = 86400;

    /**
     * @var resource
     */
    private $fileHandler;

    /**
     * @var integer
     */
    private $_config;
    private $attributes;
    private $manufacturers;
    private $currencies;

    /**
     * Initialize component
     */
    public function __construct()
    {
        $this->_config = Yii::$app->settings->get('yandexmarket');
        //$this->currencyIso = Yii::$app->currency->main['id'];
        $this->manufacturers = $this->getManufacturers();
        $this->currencies = $this->getCurrencies();
        $this->attributes = $this->attributesList();

    }

    /**
     * Display xml file
     */
    public function processRequest()
    {
        $cache = Yii::$app->cache;
        $check = $cache->get($this->cacheFileName);
        if ($check === false) {
            $this->createXmlFile();
            if (!YII_DEBUG)
                $cache->set($this->cacheFileName, true, $this->cacheTimeout);
        }


        header("content-type: text/xml");
        echo file_get_contents($this->getXmlFileFullPath());
        exit;
    }

    /**
     * Create and write xml to file
     */
    public function createXmlFile()
    {
        $filePath = $this->getXmlFileFullPath();
        $this->fileHandler = fopen($filePath, 'w');

        $this->write("<?xml version=\"1.0\" encoding=\"utf-8\"?>\n");
        $this->write("<!DOCTYPE yml_catalog SYSTEM \"shops.dtd\">\n" );
        $this->write('<yml_catalog date="' . date('Y-m-d H:i') . '">');
        $this->write('<shop>');


        $this->write('<delivery-options>условия доставки</delivery-options>');
        $this->write('<pickup-options>условия самовывоза</pickup-options>');
        $this->write('<enable_auto_discount>true</enable_auto_discount>');


        $this->renderShopData();
        $this->renderCurrencies();
        $this->renderCategories();
        $this->loadProducts();
        $this->write('</shop>');
        $this->write('</yml_catalog>');

        fclose($this->fileHandler);
    }

    /**
     * Write shop info
     */
    public function renderShopData()
    {
        $this->write('<name>' . $this->_config->name . '</name>');
        $this->write('<company>' . $this->_config->company . '</company>');
        $this->write('<version>' . Yii::$app->getVersion() . '</version>');
        $this->write('<platform>' . Yii::$app->name . '</platform>');
        $this->write('<url>' . $this->_config->url . '</url>');
        $this->write('<email>' . Yii::$app->settings->get('app', 'email') . '</email>');
    }

    /**
     * Write list of available currencies
     */
    public function renderCurrencies()
    {
        $this->write('<currencies>');
        foreach ($this->currencies as $currency) {
            $this->write('<currency id="' . $currency['iso'] . '" rate="' . $currency['rate'] . '"/>');
        }

        $this->write('</currencies>' );
    }

    /**
     * Write categories to xm file
     */
    public function renderCategories()
    {
        $categoryClass = Yii::$app->getModule('yandexmarket')->categoryClass;
        $categories = $categoryClass::find()->excludeRoot()->all();
        $this->write('<categories>');

        foreach ($categories as $c) {
            $parentId = null;
            $parent = $c->parent()->one(); //getparent()
            if ($parent && $parent->id != 1)
                $parentId = 'parentId="' . $parent->id . '"';
            $this->write('<category id="' . $c->id . '" ' . $parentId . '>' . Html::encode($c->name) . '</category>');
        }
        $this->write('</categories>');
    }

    /**
     * Write offers to xml file
     */
    public function loadProducts()
    {
        $productClass = Yii::$app->getModule('yandexmarket')->productClass;
        $total = ceil($productClass::find()->published()->count() / $this->limit);
        $offset = 0;

        $this->write('<offers>');


        for ($i = 0; $i <= $total; ++$i) {
            $products = $productClass::find()
                //->where(['limit' => $limit, 'offset' => $offset])
                ->limit($this->limit)
                ->offset($offset)
                ->published()
                ->all();

            $this->renderProducts($products);

            $offset += $this->limit;
        }

        $this->write('</offers>');
    }

    /**
     * @param array $products
     */
    public function renderProducts(array $products)
    {

        $data = [];
        foreach ($products as $p) {
            /** @var Product $p */
            if (!count($p->variants)) {

                if(method_exists($p,'getUrl')){
                $data['url'] = Url::to($p->getUrl(), true);
                }
                $data['price'] = Yii::$app->currency->convert($p->price, $p->currency_id);
                $data['name'] = Html::encode($p->name);

            } else {

                foreach ($p->variants as $v) {
                    $name = strtr('{product}({attr} {option})', [
                        '{product}' => $p->name,
                        '{attr}' => $v->productAttribute->title,
                        '{option}' => $v->option->value
                    ]);

                    $hashtag = '#' . $v->productAttribute->name . ':' . $v->option->id;
                    //TODO: need test product with variants

                    if(method_exists($p,'getUrl')){
                        $data['url'] = Url::to($p->getUrl(), true) . $hashtag;
                    }
                    $data['price'] = Yii::$app->currency->convert(Product::calculatePrices($p, $p->variants, 0), $p->currency_id);
                    $data['name'] = Html::encode($name);
                }
            }
            //Common options
            $data['categoryId'] = ($p->mainCategory) ? $p->mainCategory->id : false;
            $data['vendor'] = ($p->manufacturer_id) ? $this->manufacturers[$p->manufacturer_id] : false;
            if($p->currency_id){
                $data['currencyId'] = $this->currencies[$p->currency_id]['iso'];
            }else{
                $data['currencyId'] = $this->currencies[Yii::$app->currency->main['id']]['iso'];
            }


            if (!empty($p->sku))
                $data['model'] = Html::encode($p->sku);
            if (!empty($p->full_description)) {
                $data['description'] = $this->clearText($p->full_description);
            }

            foreach ($p->getEavAttributes() as $k => $a) {

                if (isset($this->attributes[$k]['list'][$a])) {
                    $data['params'][$this->attributes[$k]['name']] = $this->attributes[$k]['list'][$a];
                }
            }


            $data['images'] = [];
            foreach ($p->images as $img) {
                /** @var \panix\mod\images\models\Image $img */
                if(method_exists($img,'getUrl')){
                    $data['images'][] = Url::to($img->getUrl(), true);
                }

            }
            $this->renderOffer($p, $data);
        }
    }

    /**
     * @param Product $p
     * @param array $data
     */
    public function renderOffer($p, array $data)
    {
        $available = ($p->availability == 1) ? 'true' : 'false';
        $this->write('<offer id="' . $p->id . '" available="' . $available . '">');

        foreach ($data as $key => $val) {
            if (is_array($val)) {
                if ($key == 'params') {
                    foreach ($val as $name => $value) {
                        $this->write("<param name=\"" . $name . "\">" . $value . "</param>");
                    }
                } elseif ($key == 'images') {
                    foreach ($val as $name => $value) {
                        $this->write("<picture>" . $value . "</picture>");
                    }
                }
            } else {
                $this->write("<$key>" . $val . "</$key>");
            }
        }
        $this->write('</offer>');
    }

    /**
     * @param $text
     * @return string
     */
    public function clearText($text)
    {
        return '<![CDATA[' . $text . ']]>';
    }

    /**
     * @return string
     */
    public function getXmlFileFullPath()
    {
        return Yii::getAlias($this->cacheDir) . DIRECTORY_SEPARATOR . $this->cacheFileName;
    }

    /**
     * Write part of xml to file
     * @param $string
     */
    private function write($string)
    {
        fwrite($this->fileHandler, $string . PHP_EOL);
    }

    public function getManufacturers()
    {
        $manufacturerClass = Yii::$app->getModule('yandexmarket')->manufacturerClass;
        $manufacturers = $manufacturerClass::find()->all();
        $result = [];
        foreach ($manufacturers as $manufacturer) {
            $result[$manufacturer->id] = $manufacturer->name;
        }
        return $result;
    }

    public function getCurrencies()
    {
        $currencyClass = Yii::$app->getModule('yandexmarket')->currencyClass;
        $currencies = $currencyClass::find()->orderBy(['is_main'=>SORT_DESC])->asArray()->all();
        $result = [];
        foreach ($currencies as $currency) {
            $result[$currency['id']] = [
                'rate'=>$currency['rate'],
                'iso'=>$currency['iso']
            ];
        }
        return $result;
    }

    public function attributesList()
    {
        $attributeClass = Yii::$app->getModule('yandexmarket')->attributeClass;
        $attributes = $attributeClass::find()->all();
        $result = [];

        foreach ($attributes as $attribute) {
            if ($attribute->options) {
                $result[$attribute->name] = [];
                foreach ($attribute->options as $option) {
                    $result[$attribute->name]['list'][$option->id] = $option->value;
                    $result[$attribute->name]['name'] = $attribute->title;
                }
            }
        }
        return $result;
    }

}
