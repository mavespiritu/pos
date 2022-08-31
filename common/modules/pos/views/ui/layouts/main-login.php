<?php
use backend\assets\AppAsset;
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $content string */

dmstr\web\AdminLteAsset::register($this);
$asset = AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <link href="https://fonts.googleapis.com/css?family=Varela+Round&display=swap" rel="stylesheet">
    <?php $this->head() ?>
</head>
<body class="login-page" style="background-color: white;">

<?php $this->beginBody() ?>
	<br>
	<br>
	<br>
	<br>
	<div class="text-center"><?= Html::img($asset->baseUrl.'/images/toprank.png'); ?></div>
	<h3 class="text-center">POS v2.0</h3>
    <?= $content ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
<style>
	*,h3{
		font-family: 'Varela Round', sans-serif;
	}
</style>