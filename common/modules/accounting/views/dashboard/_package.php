<?php
	$availedAmount = 0;
	$no_of_students = 0;
	$packageAmount = 0;
?>
<table class="striped display" id="package-table">
	<thead>
		<tr>
			<th class="text-center">Branch - Program</th>
			<th class="text-center">Package Code</th>
			<th class="text-center">Package Amount</th>
			<th class="text-center">No. of Students</th>
			<th class="text-center">Total Amount</th>
		</tr>
	</thead>
	<tbody>
		<?php if(!empty($packages)){ ?>
			<?php foreach($packages as $package){ ?>
				<tr>
					<td><?= $package['branchProgramName'] ?></td>
					<td><?= $package['packageName'] ?></td>
					<td class="text-center"><?= $package['packageAmount'] > 0 ? number_format($package['packageAmount'], 2) : '-' ?></td>
					<td class="text-center"><?= number_format($package['no_of_students'], 0) ?></td>
					<td class="text-center"><?= $package['availedAmount'] > 0 ? number_format($package['availedAmount'], 2) : '-' ?></td>
				</tr>
				<?php $packageAmount+=$package['packageAmount']; ?>
				<?php $no_of_students+=$package['no_of_students']; ?>
				<?php $availedAmount+=$package['availedAmount']; ?>
			<?php } ?>
		<?php } ?>
	</tbody>
	<tfooter>
		<tr>
			<td align="right" colspan="2"><b>TOTAL</b></td>
			<td class="text-center"><b><?= number_format($packageAmount, 2) ?></b></td>
			<td class="text-center"><b><?= number_format($no_of_students, 0) ?></b></td>
			<td class="text-center"><b><?= number_format($availedAmount, 2) ?></b></td>
		</tr>
	</tfooter>
</table>
<?php
        $script = '
            $( document ).ready(function() {
            	$("#package-table").DataTable({
            		"order": [[ 3, "desc" ]],
            		"dom": "Bfrtip",
			        "buttons": [
			           	"csv", "excel", "pdf", "print"
			        ]
            	});
            });
';
$this->registerJs($script);
   
?>