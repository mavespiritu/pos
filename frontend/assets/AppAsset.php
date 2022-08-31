<?php

namespace frontend\assets;

use yii\web\AssetBundle;
use faryshta\disableSubmitButtons\Asset as DisableSubmitButtonAsset;

/**
 * Main frontend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
        'css/pace.css',
        /*'https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css',
        'https://cdn.datatables.net/fixedheader/3.1.5/css/fixedHeader.dataTables.min.css',
        'https://cdn.datatables.net/buttons/1.5.6/css/buttons.dataTables.min.css',*/
    ];
    public $js = [
        'js/pace.js',
        /*'https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
        'https://cdn.datatables.net/fixedheader/3.1.5/js/dataTables.fixedHeader.min.js',*/
        /*'https://code.jquery.com/jquery-3.3.1.js',*/
        /*'https://cdn.datatables.net/buttons/1.5.6/js/dataTables.buttons.min.js',
        'https://cdn.datatables.net/buttons/1.5.6/js/buttons.flash.min.js',
        'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js',
        'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js',
        'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js',
        'https://cdn.datatables.net/buttons/1.5.6/js/buttons.html5.min.js',
        'https://cdn.datatables.net/buttons/1.5.6/js/buttons.print.min.js'*/
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'faryshta\assets\ActiveFormDisableSubmitButtonsAsset',
    ];
}
