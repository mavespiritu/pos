<?php

use yii\helpers\Html;
use yii\grid\GridView;
/* @var $this yii\web\View */
/* @var $searchModel common\modules\pos\models\PosAuditSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Audit';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pos-audit-index">
    <div class="row">
        <div class="col-md-12 col-xs-12">
            <?= $this->render('_search', [
                'model' => $model,
                'seasons' => $seasons,
            ]); ?>
        </div>
        <br>
        <div class="col-md-12 col-xs-12">
            <div id="audit-information"></div>
        </div>
    </div>
</div>
