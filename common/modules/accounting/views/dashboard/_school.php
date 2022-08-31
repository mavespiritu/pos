<?php
	$total = 0;
?>
<table class="striped display" id="school-table">
	<thead>
		<tr>
			<th class="text-center">School</th>
			<th class="text-center">Location</th>
			<th class="text-center">Total</th>
		</tr>
	</thead>
	<tbody>
		<?php if(!empty($schools)){ ?>
			<?php foreach($schools as $school){ ?>
				<tr>
					<td style="width: 40%"><?= strtoupper($school['name']) ?></td>
					<td style="width: 40%"><?= strtoupper($school['location']) ?></td>
					<td class="text-center"><?= $school['total'] > 0 ? number_format($school['total'], 0) : '-' ?></td>
				</tr>
				<?php $total+=$school['total']; ?>
			<?php } ?>
		<?php } ?>
	</tbody>
	<tfooter>
		<tr>
			<td align="right" colspan=2><b>TOTAL</b></td>
			<td class="text-center"><b><?= number_format($total, 0) ?></b></td>
		</tr>
	</tfooter>
</table>
<?php
        $script = '
            $( document ).ready(function() {
            	$("#school-table").DataTable({
            		"order": [[ 2, "desc" ]],
            		"dom": "Bfrtip",
			        "buttons": [
			           	"csv", "excel", "pdf", "print"
			        ]
            	});
            });
';
$this->registerJs($script);
   
?>