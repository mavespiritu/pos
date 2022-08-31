<?php
	$total = 0;
?>
<table class="striped display" id="season-table">
	<thead>
		<tr>
			<th class="text-center">Branch - Program</th>
			<th class="text-center">Season</th>
			<th class="text-center">No. of Students</th>
		</tr>
	</thead>
	<tbody>
		<?php if(!empty($seasons)){ ?>
			<?php foreach($seasons as $season){ ?>
				<tr>
					<td style="width: 40%"><?= strtoupper($season['branchProgramName']) ?></td>
					<td style="width: 40%"><?= strtoupper($season['seasonName']) ?></td>
					<td class="text-center"><?= $season['total'] > 0 ? number_format($season['total'], 0) : '-' ?></td>
				</tr>
				<?php $total+=$season['total']; ?>
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
            	$("#season-table").DataTable({
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