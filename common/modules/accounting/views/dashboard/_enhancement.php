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
<br>
<br>
<div class="text-center"><h1 style="font-size: 70px;">P <?= number_format_short($enhancement['total']) ?></h1></div>
<div class="text-center"><p style="font-size: 14px;"><b><?= number_format($enhancement['no_of_students'], 0) ?></b> student/s availed the enhancement</p></div>

                        
           
