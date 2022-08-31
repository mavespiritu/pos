<?php 
use yii\helpers\Html;
use frontend\assets\AppAsset;
$asset = AppAsset::register($this);

$headers = [
    'accounting_student.id_number' => 'ID NUMBER',
    'concat(accounting_student.last_name," ",accounting_student.extension_name) as lastName' => 'LAST NAME',
    'accounting_student.first_name' => 'FIRST NAME',
    'accounting_student.middle_name' => 'MIDDLE NAME',
    'accounting_school.name as schoolName' => 'SCHOOL',
    'accounting_school.location as schoolLocation' => 'LOCATION OF SCHOOL',
    'accounting_student.year_graduated' => 'YEAR GRADUATED',
    'tblprovince.province_m as provinceName' => 'PROVINCE',
    'tblcitymun.citymun_m as citymunName' => 'CITY/MUNICIPALITY',
    'accounting_student.permanent_address' => 'PERMANENT ADDRESS',
    'accounting_student.contact_no' => 'CONTACT NO.',
    'accounting_student.birthday' => 'BIRTHDAY',
    'accounting_student.prc' => 'PRC APPLICATION NO.',
    'accounting_student.email_address' => 'EMAIL ADDRESS',
];

$values = [
    'id_number',
    'lastName' ,
    'first_name',
    'middle_name',
    'schoolName',
    'schoolLocation',
    'year_graduated',
    'provinceName',
    'citymunName',
    'permanent_address',
    'contact_no',
    'birthday',
    'prc',
    'email_address',
];

$width = count($fields) > 0 ? ceil(100/count($fields)) : 100;
?>
<h3 class="text-center">
	<?= Html::img($asset->baseUrl.'/images/logo-blue.png',['style' => 'height: 45px; width: 175px;']) ?>
</h3>
<h5 class="text-center">Toprank Integrated Systems<br>
						Accounting<br>
						Student Information</h5>

<table class="table table-bordered table-condensed table-hover table-responsive" style="width: 100%;">
	<tbody>
		<tr>
			<td><b>Branch</b></td>
			<td align=right><?= $season->branchProgram->branch->name ?></td>
		</tr>
		<tr>
			<td><b>Program</b></td>
			<td align=right><?= $season->branchProgram->program->name ?></td>
		</tr>
		<tr>
			<td><b>Season</b></td>
			<td align=right><?= $season->seasonName ?></td>
		</tr>
	</tbody>
</table>
<table class="table table-bordered table-condensed">
	<thead>
		<tr>
			<th>#</th>
			<?php if(!empty($fields)){ ?>
				<?php foreach($fields as $field){ ?>
					<th><?= $headers[$field] ?></th>
				<?php } ?>
			<?php } ?>
		</tr>
	</thead>
	<tbody>
		<?php if(!empty($data)){ ?>
			<?php $i = 1; ?>
			<?php foreach($data as $datum){ ?>
				<tr>
					<td style="width: 3%"><?= $i ?></td>
					<?php foreach($values as $value){ ?>
						<?= isset($datum[$value]) ? '<td style="text-align:center; width:'.$width.'%;">'.$datum[$value].'</td>' : '' ?>
					<?php } ?>
				</tr>
				<?php $i++; ?>
			<?php } ?>
		<?php }  ?>
	</tbody>
</table>