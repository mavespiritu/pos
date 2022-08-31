<?php

namespace mitrm\amcharts;

use yii\web\AssetBundle;

/**
 * Widget asset bundle.
 */
class AmChartAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@vendor/mitrm/yii2-amcharts/assets';

    /**
     * @inheritdoc
     */
    public $js = [
        'amcharts/amcharts.js', 
        'amcharts/pie.js', 
        'amcharts/serial.js', 
    ];

    /**
     * @inheritdoc
     */
    public $depends = [
        'yii\web\JqueryAsset',
    ];
    
    public function addLanguageJs($language = null)
    {
        $language = $language ? substr($language,0,2) : substr(Yii::$app->language,0,2);
        if($language!='en'){
            $this->js[] = 'amcharts/lang/' . $language . '.js';
        }
    }
    
    public function addExportJs()
    {
        $exportJsPaths = [
            'exporting/amexport.js',
            'exporting/canvg.js',
            'exporting/filesaver.js',
            'exporting/jspdf.js',
            'exporting/jspdf.plugin.addimage.js',
            'exporting/rgbcolor.js',
        ];
        foreach ($exportJsPaths as $path)
        {
            $this->js[] = $path;
        }
    }
    
    public function addThemeJs($theme)
    {
        $this->js[] = 'amcharts/themes/' . $theme . '.js';
    }
}
