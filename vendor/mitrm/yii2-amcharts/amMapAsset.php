<?php

namespace mitrm\amcharts;

use yii\web\AssetBundle;

/**
 * Widget asset bundle.
 */
class amMapAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@mitrm/yii2-amcharts/assets';

    /**
     * @inheritdoc
     */
    public $js = [
        'ammap/ammap.js', 
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
        $this->js[] = 'ammap/themes/' . $theme . '.js';
    }
    
    /**
     * Подключение карт
     * @param type $theme
     */
    public function addMapJs($maps)
    {
        foreach ($maps as $map) {
            
        }
        $this->js[] = 'ammap/maps/js/' . $map . '.js';
    }
}
