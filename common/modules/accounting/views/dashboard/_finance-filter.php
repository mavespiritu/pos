<ul class="to_do"><li><i class="fa fa-search"></i>&nbsp;&nbsp;&nbsp;&nbsp;Active Filters: &nbsp;&nbsp;&nbsp;

<?php foreach($filters as $key => $filter){ ?>
	<i class="fa fa-circle"></i>&nbsp;&nbsp;<?= $key ?>:&nbsp;&nbsp;&nbsp;<?= $filter ?>&nbsp;&nbsp;&nbsp;
<?php } ?>

</li></ul>