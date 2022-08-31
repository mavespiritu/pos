<?php
	use yii\jui\ProgressBar;
	function number_format_short( $n, $precision = 1 ) {
	    if ($n < 900) {
	        // 0 - 900
	        $n_format = number_format($n, $precision);
	        $suffix = '';
	    } else if ($n < 900000) {
	        // 0.9k-850k
	        $n_format = number_format($n / 1000, $precision);
	        $suffix = 'K';
	    } else if ($n < 900000000) {
	        // 0.9m-850m
	        $n_format = number_format($n / 1000000, $precision);
	        $suffix = 'M';
	    } else if ($n < 900000000000) {
	        // 0.9b-850b
	        $n_format = number_format($n / 1000000000, $precision);
	        $suffix = 'B';
	    } else {
	        // 0.9t+
	        $n_format = number_format($n / 1000000000000, $precision);
	        $suffix = 'T';
	    }
	    
	    if ( $precision > 0 ) {
	        $dotzero = '.' . str_repeat( '0', $precision );
	        $n_format = str_replace( $dotzero, '', $n_format );
	    }
	    return $n_format . $suffix;
	}
?>
<div class="text-center"><h1 style="font-size: 70px;">P <?= number_format_short($discount['total']) ?></h1></div>
<div class="text-center"><p style="font-size: 14px;"><b><?= number_format($discount['no_of_students'], 0) ?></b> student/s availed the discount</p></div>
<p>Discount types availed:</p>
<table style="width: 100%; border-collapse: collapse;" cellspacing="0" cellpadding="0">
<?php if(!empty($discountTypes)){ ?>
	<?php foreach($discountTypes as $discountType){ ?>
		<tr>
			<td style="width: 40%; padding: 0;" valign="top"><?= $discountType['name'] ?></td>
			<td style="width: 50%; padding: 0;">
				<div class="progress">
					<div class="progress-bar progress-bar-success" data-transitiongoal="<?= $discount['no_of_students'] > 0 ? number_format(($discountType['total']/$discount['no_of_students'])*100, 2) : 0.00 ?>" aria-valuenow="<?= $discount['no_of_students'] > 0 ? number_format(($discountType['total']/$discount['no_of_students'])*100, 2) : 0.00 ?>" style="width: <?= $discount['no_of_students'] > 0 ? number_format(($discountType['total']/$discount['no_of_students'])*100, 2) : 0.00 ?>%; min-width: 2em;">
						<p style="color: #434348;"><?= $discount['no_of_students'] > 0 ? number_format(($discountType['total']/$discount['no_of_students'])*100, 2) : 0.00 ?>%</p>
					</div>
				</div>
			</td>
			<td style="padding-left: 10px;" valign="top"><?= $discountType['total'] ?></td>
		</tr>
	<?php } ?>
<?php } ?>
</table>

                        
           
