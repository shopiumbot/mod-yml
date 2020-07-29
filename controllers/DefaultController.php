<?php

namespace shopium\mod\yml\controllers;

use core\modules\shop\models\Category;
use core\modules\shop\models\Currency;
use core\modules\shop\models\Product;
use panix\engine\behaviors\nestedsets\NestedSetsBehavior;
use panix\engine\CMS;
use Yii;
use core\components\controllers\AdminController;
use shopium\mod\yml\models\SettingsForm;
use yii\base\Exception;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\Response;
use DOMDocument;
use DOMElement;
use DOMText;
use DOMException;
use yii\base\Arrayable;
use yii\helpers\StringHelper;

class DefaultController extends AdminController
{

    /**
     * @var \SimpleXMLElement
     */
    protected $xml_promos;
    protected $xml_gifts;


    public function actionIndex()
    {

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


    public function actionProcess()
    {

        Yii::$app->response->format = Response::FORMAT_XML;
        $start = microtime(true);
        /*$yml = new XML();
        $cache = Yii::$app->cache;
        $check = $cache->get($yml->cacheFileName);
        if ($check === false) {
            $yml->createXmlFile();
            if (!YII_DEBUG)
                $cache->set($yml->cacheFileName, true, $yml->cacheTimeout);
        }*/


        //$file = dirname(__FILE__) . "/example1.xml";
        //$reader = new SXMLReader();
        // $reader->open($yml->getXmlFileFullPath());
        // $reader->parse();
        // $reader->close();


        /*$reader = new SimpleXMLReader();
        $reader->open($yml->getXmlFileFullPath());

        $reader->registerCallback("by-node-name", function($reader) {
            $element = $reader->expandSimpleXml(); // copy of the current node as a SimpleXMLElement object
            $attributes = $element->attributes(); // read element attributes

        });
        $reader->registerCallback("/by/xpath/query", function($reader) {
            $element = $reader->expandDomDocument(); // copy of the current node as a DOMNode object
            $attributes = $element->attributes(); // read element attributes

        });
        $reader->parse();
        $reader->close();*/


        /*$reader = new \XMLReader();
        $o = $reader->open($yml->getXmlFileFullPath());
        $r = $reader->read();


        CMS::dump($reader->expand());

        $reader->close();*/

        $path = Yii::getAlias('@runtime') . '/yml.xml';
        $path2 = Yii::getAlias('@runtime') . '/yml2.xml';


        /*$xml=simplexml_load_file($path) or die("Error: Cannot create object");
        foreach($xml->shop->offers->offer as $offer) {
            $attributes = $offer->attributes();
            echo $attributes->id;
            echo $attributes->available;
            CMS::dump($offer);
            foreach ($offer->param as $param){
              //  CMS::dump($param);
            }
        }
        echo microtime(true) - $start;
        die;*/

        $reader = new \XMLReader();
        $reader->open($path);
        // $xml = simplexml_import_dom($actual->importNode($reader->expand(), true));
        // CMS::dump($actual->attributes);die;

        $data = [];

        while ($reader->read()) {
            $doc = new \DOMDocument('1.0', 'UTF-8');
            //  $read=$reader->readOuterXml();
            // $dd = $doc->importNode($reader->expand(), true);
            if ($reader->nodeType == \XMLReader::ELEMENT && $reader->name == 'offer') {
                //$xml = new \SimpleXMLElement($reader->readOuterXml());
                $xml = simplexml_import_dom($doc->importNode($reader->expand(), true));
                $data['offers'][] = $xml;
            } elseif ($reader->nodeType == \XMLReader::ELEMENT && $reader->name == 'category') {
                // $xml = new \SimpleXMLElement($reader->readOuterXml());
                $xml = simplexml_import_dom($doc->importNode($reader->expand(), true));
                $data['categories'][] = $xml;
            } elseif ($reader->nodeType == \XMLReader::ELEMENT && $reader->name == 'currency') {
                // $xml = new \SimpleXMLElement($reader->readOuterXml());
                $xml = simplexml_import_dom($doc->importNode($reader->expand(), true));
                $data['currencies'][] = $xml;
            }

        }

        CMS::dump($data);
        echo microtime(true) - $start;
        die;
        $start = microtime(true);
        while ($reader->read() && $reader->name !== 'offers') ;

// now that we're at the right depth, hop to the next <product/> until the end of the tree
        //while ($reader->name === 'offer') {
        // either one should work
        $nodes = new \SimpleXMLElement($reader->readOuterXML());
        //$node = simplexml_import_dom($doc->importNode($reader->expand(), true));

        // now you can use $node without going insane about parsing
        // go to next <product />
        // $reader->next('offer');
        // }
        $data = [];
        foreach ($nodes as $key => $node) {

            /** @var $node \SimpleXMLElement */
            $data[] = $node;


            //   $data['node']['myattr'][$node->attributes()->getName()]=$node->attributes()->id;
        }


        $reader->close();

        // $data['node']->{0}->

        CMS::dump($data);
        echo microtime(true) - $start;
        die;

        // return $xml->read();
    }


    public function actionExport()
    {
        try {
            $xml = new \SimpleXMLElement('<xml version="1.0" encoding="UTF-8"/>');
            $XML_catalog = $xml->addChild('yml_catalog');
            $XML_catalog->addAttribute('date', date('Y-m-d H:i'));
            $shop = $XML_catalog->addChild('shop');
            $shop->addChild('name', 'Store name');
            $shop->addChild('company', 'company name');
            $shop->addChild('url', (Yii::$app->request->isSecureConnection)?'https://':'http://' . Yii::$app->request->serverName);


            $XML_delivery_options = $shop->addChild('delivery-options');
            $doi = $XML_delivery_options->addChild('option');
            $doi->addAttribute('cost', 200);
            $doi->addAttribute('days', 1);


            $this->loadCurrencies($shop);
            $this->loadCategories($shop);
            $this->loadOffers($shop);

            $this->loadPromos($shop);
            $this->loadGifts($shop);

            // Yii::$app->response->format = Response::FORMAT_XML;
            Header('Content-type: text/xml');

            //file_put_contents(Yii::$app->runtimePath.'/yml.xml',$xml->asXML());
            echo $xml->asXML();
            die;
        } catch (Exception $e) {
            echo 'ERROR: ' . $e->getMessage();
            die;
        }
    }

    /**
     * Подарки, которые не размещаются на Маркете
     * @param \SimpleXMLElement $node
     */
    public function loadGifts($node)
    {
        /** @var \SimpleXMLElement $promos */
        $promos = $this->xml_promos;
        $xml = $node->addChild('gifts');
        $item = $xml->addChild('gift');
        $item->addAttribute('id', 3);
        $item->addChild('name', 'Чехол iPhone X Хохлома');
        $item->addChild('picture', 'https://best.seller.ru/promos/3.jpg');


        // $this->xml_promos = $node->addChild('promos');

        $item = $promos->addChild('promo');
        $item->addAttribute('id', 'PromoGift');
        $item->addAttribute('type', 'gift with purchase');
        $item->addChild('start-date', '2018-02-01 09:00:00');
        $item->addChild('end-date', '2018-03-01 22:00:00');
        $item->addChild('description', 'Скидка 10% по уникальному промокоду!');
        $item->addChild('url', 'http://best.seller.ru/promos/10');

//Товары, участвующие в акции
        $purchase = $item->addChild('purchase');
        $purchase->addChild('product')->addAttribute('offer-id', 55);
        $purchase->addChild('required-quantity', 1);


        //Подарки на выбор
        $promogifts = $item->addChild('promo-gifts');
        $promogifts->addChild('promo-gift')->addAttribute('offer-id', 2);
        $promogifts->addChild('promo-gift')->addAttribute('gift-id', 3);


    }

    /**
     * Информация об акции
     * @param \SimpleXMLElement $node
     */
    public function loadPromos($node)
    {
        $this->xml_promos = $node->addChild('promos');

        $item = $this->xml_promos->addChild('promo');
        $item->addAttribute('id', 'Promo20');
        $item->addAttribute('type', 'promo code');
        $item->addChild('start-date', '2018-02-01 09:00:00');
        $item->addChild('end-date', '2018-03-01 22:00:00');
        $item->addChild('description', 'Скидка 10% по уникальному промокоду!');
        $item->addChild('url', 'http://best.seller.ru/promos/10');
        $item->addChild('promo-code', 'HAPPYNEWBENEFIT');
        $discount = $item->addChild('discount', '10');
        $discount->addAttribute('unit', 'currency');
        $discount->addAttribute('currency', 'UAH');
        $purchase = $item->addChild('purchase');
        $purchase->addChild('product')->addAttribute('offer-id', 55);
        $purchase->addChild('product')->addAttribute('category-id', 1);


        $item2 = $this->xml_promos->addChild('promo');
        $item2->addAttribute('id', 'Promo10');
        $item2->addAttribute('type', 'promo code');
        $item2->addChild('start-date', '2018-02-01 09:00:00');
        $item2->addChild('end-date', '2018-03-01 22:00:00');
        $item2->addChild('description', 'Скидка 10% по уникальному промокоду!');
        $item2->addChild('url', 'http://best.seller.ru/promos/10');
        $item2->addChild('promo-code', 'HAPPYNEWBENEFIT');
        $item2->addChild('discount', '10')->addAttribute('unit', 'percent');
        $purchase = $item2->addChild('purchase');
        $purchase->addChild('product')->addAttribute('offer-id', 55);
        $purchase->addChild('product')->addAttribute('category-id', 1);


        $item3 = $this->xml_promos->addChild('promo');
        $item3->addAttribute('id', 'Promo30');
        $item3->addAttribute('type', 'flash discount');
        $item3->addChild('start-date', '2018-02-01 09:00:00');
        $item3->addChild('end-date', '2018-03-01 22:00:00');
        $item3->addChild('description', 'Скидка 10% по уникальному промокоду!');
        $item3->addChild('url', 'http://best.seller.ru/promos/10');
        $purchase = $item3->addChild('purchase');
        $p1 = $purchase->addChild('product');
        $p1->addAttribute('offer-id', 55);
        $p1->addChild('discount-price', 300)->addAttribute('currency', 'UAH');


        $item4 = $this->xml_promos->addChild('promo');
        $item4->addAttribute('id', 'Promo2Plus1');
        $item4->addAttribute('type', 'n plus m');
        $item4->addChild('start-date', '2018-02-01 09:00:00');
        $item4->addChild('end-date', '2018-03-01 22:00:00');
        $item4->addChild('description', 'Скидка 10% по уникальному промокоду!');
        $item4->addChild('url', 'http://best.seller.ru/promos/10');

        $purchase = $item4->addChild('purchase');
        $purchase->addChild('product')->addAttribute('offer-id', 55);
        $purchase->addChild('product')->addAttribute('category-id', 55);
        $purchase->addChild('required-quantity', 2);
        $purchase->addChild('free-quantity', 1);


    }

    /**
     * @param \SimpleXMLElement $node
     */
    public function loadCategories($node)
    {
        $items = Category::find()->excludeRoot()->all();
        $xml = $node->addChild('categories');
        foreach ($items as $item) {
            /** @var Category|NestedSetsBehavior $item */
            $parentId = null;
            $parent = $item->parent()->asArray()->one();
            $category = $xml->addChild('category', Html::encode($item['name']));
            $category->addAttribute('id', $item['id']);

            if ($parent && $parent['id'] != 1) {
                $category->addAttribute('parentId', $parent['id']);
            }
        }
    }


    /**
     * @param \SimpleXMLElement $node
     */
    public function loadCurrencies($node)
    {
        $items = Currency::find()->asArray()->all();
        $xml = $node->addChild('currencies');
        foreach ($items as $item) {
            $category = $xml->addChild('currency', Html::encode($item['name']));
            $category->addAttribute('id', $item['iso']);
            $category->addAttribute('rate', $item['rate']);
        }
    }


    /**
     * @param \SimpleXMLElement $node
     */
    public function loadOffers($node)
    {

        $xml = $node->addChild('offers');
        $products = Product::find()->all();
        $attributesList = $this->attributesList();
        foreach ($products as $product) {
            /** @var Product $product */
            $offer = $xml->addChild('offer');
            $offer->addAttribute('id', $product->id);

            $offer->addChild('name', Html::encode($product->name));
            if ($product->manufacturer)
                $offer->addChild('vendor', Html::encode($product->manufacturer->name));

            if ($product->sku)
                $offer->addChild('model', Html::encode($product->sku));

            if (!empty($product->full_description))
                $offer->addChild('description', $this->clearText($product->full_description));

            // $offer->addChild('available', $product->available);

            //  $offer->addChild('vendorCode', "A1234567B");
            $offer->addChild('url', "http://best.seller.ru/product_page.asp?pid=12345");

            if ($product->hasDiscount) {
                $offer->addChild('price', $product->price);
                $offer->addChild('oldprice', $product->price);
            } else {
                $offer->addChild('price', $product->price);
            }


            $offer->addChild('currencyId', 'UAH');
            $offer->addChild('categoryId', $product->main_category_id);
            $offer->addChild('delivery', 'true');

            //Вес товара в килограммах с учетом упаковки.
            if ($product->weight)
                $offer->addChild('weight', $product->weight);
            $offer->addChild('dimensions', '20.1/20.551/22.5');

            $offer->addChild('manufacturer_warranty', 'true');
            $offer->addChild('country_of_origin', 'Китай');

            foreach ($product->images as $image) {
                $offer->addChild('picture', Url::to($image->getUrl(), true));
            }

            foreach ($product->getEavAttributes() as $k => $param) {
                if (isset($attributesList[$k]['list'][$param])) {
                    //  $data['params'][$attributesList[$k]['name']] = $attributesList[$k]['list'][$param];
                    $offer->addChild('param', $attributesList[$k]['list'][$param])
                        ->addAttribute('name', $attributesList[$k]['name']);
                }
            }

            //likenew OR used
            $condition = $offer->addChild('condition');
            $condition->addAttribute('type', 'likenew');
            $condition->addChild('reason', 'Повреждена упаковка');


            /**
             * Товар имеет отношение к удовлетворению сексуальных потребностей, либо иным образом эксплуатирует интерес к сексу. Возможные значения — true, false.
             */
            // $adult = $offer->addChild('adult',false);

            /*$do = $offer->addChild('delivery-options');
            $cat = $do->addChild('option');
            $cat->addAttribute('cost',300);
            $cat->addAttribute('days',1);
            $cat->addAttribute('order-before',18);*/

        }
    }

    /**
     * @param $text
     * @return string
     */
    public function clearText($text)
    {
        return '<![CDATA[' . $text . ']]>';
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
