<?php

namespace mitrm\amcharts;

use Yii;
use yii\web\View;
use yii\helpers\Html;
/**
*$chartConfiguration = [
*    'dataProvider' => [
*		['author' => 'Вася', 'title' => 'Васильев Вася', 'count' => 5],
*		['author' => 'Олег', 'title' => 'Васильев Олег', 'count' => 15],
*	
*	],
*    'type' => 'pie',
*    'legend' => [
*        'markerType' => 'circle',
*        'position' => 'right',
*        'marginRight' => 30,
*        'autoMargins' => false,
*        'labelWidth' => 310,
*        'labelText' => '[[title]]',
*        'valueText' => '[[value]] ([[percents]]%)',
*        'width' => 390,
*    ],
*    'maxLabelWidth' => 150,
*    'marginLeft' => -100,
*    'marginTop' => 0,
*    'marginBottom' => 0,
*    'labelText' => '[[value]] ([[percents]]%)',
*    'valueField' => 'count',
*    'titleField' => 'title',
*    'descriptionField' => 'author',
*    'balloonText' => '[[title]]<br><span style=\'font-size:12px\'><b>[[value]]</b> ([[percents]]%)</span>',
*];
*echo mitrm\amcharts\AmChart::widget([
*    'chartConfiguration' => $chartConfiguration, 
*    'options' => ['id' => 'chart_id'],
*    'width' => '100%',
*    'language' => 'ru',
*]);
*/

class AmChart extends \yii\base\Widget
{
    
    public $width = '600px';
    
    public $height = '400px';

    public $options;
    
    public $language;
    
    public $zoomChart=false;
    
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
            $this->options['id'] = 'chart_' . $this->getId();
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
        $assetBundle = AmChartAsset::register($this->getView());
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
            $this->chartConfiguration['pathToImages'] = $assetBundle->baseUrl . '/amcharts/images/';
        }
        if ($this->zoomChart != false)
        {
            $this->view->registerJs('
                    '.$this->chartId.'.addListener("rendered", zoomChart_'.$this->chartId.');
                    zoomChart_'.$this->chartId.'();
                    function zoomChart_'.$this->chartId.'(){
                        '.$this->chartId.'.zoomToIndexes('.$this->chartId.'.dataProvider.length - '.$this->zoomChart['lenght_one'].', '.$this->chartId.'.dataProvider.length - '.$this->zoomChart['lenght_two'].');
                    }
                    var resizeChart = function (){
                        '.$this->chartId.'.validateSize();

                    }
                    setTimeout(resizeChart, 2000);
                ',View::POS_LOAD);
        }
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
