<?php
	use frontend\assets\AppAsset;
	use yii\helpers\Html;
	use kartik\grid\GridView;
	use yii\widgets\Pjax;
	use yiister\gentelella\widgets\Panel;
	use yii\web\JsExpression;
	use yii\helpers\Url;
	use common\modules\accounting\models\Season;

	$this->title = 'Cost Estimation';
	$this->params['breadcrumbs'][] = $this->title;

	$asset = AppAsset::register($this);
?>

<div class="dashboard-index">
	<h1><?= Html::encode($this->title) ?></h1>
	<div class="row">
		<div class="col-md-12">
			<?php Panel::begin(['header' => 'Cost Estimation']); ?>
				<div class="pull-right"><?= Html::a('Create Estimation',['/accounting/cost-estimation/create'],['class' => 'btn btn-success']) ?></div>	
				<div class="clearfix"></div>
				<br>
                <?php Pjax::begin(); ?>
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'columns' => [
                    	'seasonName',
                    	[
                        	'attribute' => 'totalGross',
                        	'value' => function($model){ return number_format($model->totalGross, 2); }
                    	],
                        [
                        	'attribute' => 'totalGrossIncome',
                        	'value' => function($model){ return number_format($model->totalGrossIncome, 2); }
                    	],
                    	[
                        	'attribute' => 'totalExpenses',
                        	'value' => function($model){ return number_format($model->totalExpenses, 2); }
                    	],
                    	[
                        	'attribute' => 'expectedIncome',
                        	'value' => function($model){ return number_format($model->expectedIncome, 2); }
                    	],
                        [
                        	'attribute' => 'netIncome',
                        	'value' => function($model){ return number_format($model->netIncome, 2); }
                    	],

                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{view}'
                        ],
                    ],
                ]); ?>
                <?php Pjax::end(); ?>
			<?php Panel::end(); ?>
		</div>
	</div>
</div>
