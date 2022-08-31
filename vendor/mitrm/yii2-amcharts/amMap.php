<?php

namespace mitrm\amcharts;

use Yii;
use yii\web\View;
use yii\helpers\Html;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class amMap extends \yii\base\Widget
{
    
    public $width = '600px';
    
    public $height = '400px';

    public $options;
    
    public $language;
    
    /**
     * Подключаемые карты
     * @var type 
     */
    public $maps = [];


    protected $_chartId;
    
    /**
     * @var array the AmChart configuration array
     * @see http://docs.amcharts.com/3/javascriptcharts
     */
    public $chartConfiguration = [];
    
    
    public function init()
     {
        if (!isset($this->options['id'])) 
        {
            $this->options['id'] = 'ammap_' . $this->getId();
        }
        $this->chartId = $this->options['id'];
        parent::init();
    }
    
    public function run()
    {
        $this->makeChart();
        $this->options['style'] = "width: {$this->width}; height: {$this->height};";
        echo Html::tag('div', '', $this->options);
    }
    
    protected function makeChart()
    {
        if (!isset($this->chartConfiguration['language']))
        {
            $this->chartConfiguration['language'] = $this->language ? $this->language : Yii::$app->language;
        }
        $assetBundle = amMapAsset::register($this->getView());
        if (isset($this->chartConfiguration['theme']))
        {
            $assetBundle->addThemeJs($this->chartConfiguration['theme']);
        }
        if (isset($this->chartConfiguration['amExport']))
        {
            $assetBundle->addExportJs();
        }
        $assetBundle->addLanguageJs($this->chartConfiguration['language']);
        if (!isset($this->chartConfiguration['pathToImages']))
        {
            $this->chartConfiguration['pathToImages'] = $assetBundle->baseUrl . '/ammap/images/';
        }
        if (isset($this->chartConfiguration['dataProvider']['map']))
        {
            $this->maps[] = $this->chartConfiguration['dataProvider']['map'];
        }
        $assetBundle->addMapJs($this->maps);
        
        $chartConfiguration = json_encode($this->chartConfiguration);
        $js = $this->chartId . " = AmCharts.makeChart(\"{$this->chartId}\", {$chartConfiguration});";
        $this->getView()->registerJs($js, View::POS_READY);
    }

    protected function setChartId($value)
    {
        $this->_chartId = $value;
    }
    
    protected function getChartId()
    {
        return $this->_chartId;
    }
}