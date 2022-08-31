<?php
	$total = 0;
	$transferred = 0;
	$dropped = 0;
?>
<table class="striped display" id="province-table">
	<thead>
		<tr>
			<th>Province</th>
			<th class="text-center">Enrolled</th>
			<th class="text-center">Transferred</th>
			<th class="text-center">Dropped</th>
		</tr>
	</thead>
	<tbody>
		<?php if(!empty($data)){ ?>
			<?php foreach($data as $datum){ ?>
				<tr>
					<td><?= $datum['name'] ?></td>
					<td class="text-center"><?= $datum['total'] > 0 ? number_format($datum['total'], 0) : '-' ?></td>
					<td class="text-center"><?= $datum['transferred'] > 0 ? number_format($datum['transferred'], 0) : '-' ?></td>
					<td class="text-center"><?= $datum['dropped'] > 0 ? number_format($datum['dropped'], 0) : '-' ?></td>
				</tr>
				<?php $total+=$datum['total']; ?>
				<?php $transferred+=$datum['transferred']; ?>
				<?php $dropped+=$datum['dropped']; ?>
			<?php } ?>
		<?php } ?>
	</tbody>
	<tfooter>
		<tr>
			<td align="right"><b>TOTAL</b></td>
			<td class="text-center"><b><?= number_format($total, 0) ?></b></td>
			<td class="text-center"><b><?= number_format($transferred, 0) ?></b></td>
			<td class="text-center"><b><?= number_format($dropped, 0) ?></b></td>
		</tr>
	</tfooter>
</table>
<?php
        $script = '
            $( document ).ready(function() {
            	$("#province-table").DataTable({
            		"order": [[ 1, "desc" ]],
            		"dom": "Bfrtip",
			        "buttons": [
			           	"csv", "excel", "pdf", "print"
			        ]
            	});
            });
';
$this->registerJs($script);
   
?>