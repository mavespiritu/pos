# yii2-amcharts
http://www.amcharts.com/



To install, either run

```
$ php composer.phar require --prefer-dist mitrm/yii2-amcharts "dev-master"
```

or add

```
"mitrm/yii2-amcharts": "dev-master"
```

Example: 
Displays a pie
```php
$chartConfiguration = [
    'dataProvider' => [
		['author' => 'Вася', 'title' => 'Васильев Вася', 'count' => 5],
		['author' => 'Олег', 'title' => 'Васильев Олег', 'count' => 15],
	
	],
    'type' => 'pie',
    'legend' => [
        'markerType' => 'circle',
        'position' => 'right',
        'marginRight' => 30,
        'autoMargins' => false,
        'labelWidth' => 310,
        'labelText' => '[[title]]',
        'valueText' => '[[value]] ([[percents]]%)',
        'width' => 390,
    ],
    'maxLabelWidth' => 150,
    'marginLeft' => -100,
    'marginTop' => 0,
    'marginBottom' => 0,
    'labelText' => '[[value]] ([[percents]]%)',
    'valueField' => 'count',
    'titleField' => 'title',
    'descriptionField' => 'author',
    'balloonText' => '[[title]]<br><span style=\'font-size:12px\'><b>[[value]]</b> ([[percents]]%)</span>',
];
echo mitrm\amcharts\AmChart::widget([
    'chartConfiguration' => $chartConfiguration, 
    'options' => ['id' => 'chart_id'],
    'width' => '100%',
    'language' => 'ru',
]);
```
