<?php

namespace shopium\mod\yml\controllers;

use panix\engine\CMS;
use shopium\mod\yml\components\SimpleXMLReader;
use shopium\mod\yml\components\SXMLReader;
use shopium\mod\yml\components\XML;
use shopium\mod\yml\components\YML;
use Yii;
use core\components\controllers\AdminController;
use shopium\mod\yml\models\SettingsForm;
use yii\httpclient\XmlParser;
use yii\web\Response;

class DefaultController extends AdminController
{

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

        $path = Yii::getAlias('@runtime').'/yml.xml';
        $path2 = Yii::getAlias('@runtime').'/yml2.xml';


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




        //$actual = new \DOMDocument;
       // $actual->load($path);
       // $xml = simplexml_import_dom($actual->importNode($reader->expand(), true));
       // CMS::dump($actual->attributes);die;

        $data=[];

        while($reader->read()){
            $doc = new \DOMDocument('1.0','UTF-8');
          //  $read=$reader->readOuterXml();
           // $dd = $doc->importNode($reader->expand(), true);
            if($reader->nodeType == \XMLReader::ELEMENT && $reader->name == 'offer'){
                //$xml = new \SimpleXMLElement($reader->readOuterXml());
                $xml = simplexml_import_dom($doc->importNode($reader->expand(), true));
                $data['offers'][]=$xml;
            }elseif($reader->nodeType == \XMLReader::ELEMENT && $reader->name == 'category'){
               // $xml = new \SimpleXMLElement($reader->readOuterXml());
                $xml = simplexml_import_dom($doc->importNode($reader->expand(), true));
                $data['categories'][]=$xml;
            }elseif($reader->nodeType == \XMLReader::ELEMENT && $reader->name == 'currency'){
               // $xml = new \SimpleXMLElement($reader->readOuterXml());
                $xml = simplexml_import_dom($doc->importNode($reader->expand(), true));
                $data['currencies'][]=$xml;
            }

        }

        CMS::dump($data);
        echo microtime(true) - $start;
        die;
        $start = microtime(true);
        while ($reader->read() && $reader->name !== 'offers');

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
}
