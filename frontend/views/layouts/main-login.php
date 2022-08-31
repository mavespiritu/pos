<?php
use frontend\assets\AppAsset;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this \yii\web\View */
/* @var $content string */
yiister\gentelella\assets\Asset::register($this);

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
    <link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Lato" />
    <?php $this->head() ?>
</head>
<body class="login-page" style="font-family: Lato; background: #FFFFFF;">

<?php $this->beginBody() ?>
    <br>
    <br>
    <br>
	<h3 class="text-center">
		<?= Html::img($asset->baseUrl.'/images/logo-blue.png',['style' => 'height: 30%; width: 30%;']) ?>
	</h3>
	<div class="row">&nbsp;</div>
    <div class="row">&nbsp;</div>
	<div class="row">
        <div class="col-md-2 col-xs-12">&nbsp;</div>   
        <div class="col-md-8 col-xs-12"><?= $content ?></div>   
        <div class="col-md-2 col-xs-12">&nbsp;</div>   
    </div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
