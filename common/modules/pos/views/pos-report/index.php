<?php

use yii\helpers\Html;
/* @var $this yii\web\View */
/* @var $searchModel common\modules\pos\models\PosProgramSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = $title;
$this->params['breadcrumbs'][] = ['label' => 'Reports'];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pos-report-index">
	 <div class="row">
        <div class="col-md-4 col-xs-12">&nbsp;</div>
        <div class="col-md-4 col-xs-12">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <p style="font-size: 20px;">Generate Report</p>
                </div>
                <div class="box-body">
                	<?= $this->render('_search', [
						'model' => $model,
						'branchPrograms' => $branchPrograms,
						'seasons' => $seasons,
					]); ?>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xs-12">&nbsp;</div>
</div>
