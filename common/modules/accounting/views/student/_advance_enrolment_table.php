<?php 
    use yii\helpers\Html;
    use yii\helpers\Url;
    use yii\bootstrap\Modal;
    use yii\web\View;
?>
<?php if($model->advanceEnrolments){ ?>
    <?php $i = 1; ?>
    <table class="table table-condensed detail-view" style="width: 100%;">
        <thead>
            <tr>
                <th>Advance Enrolment (Season)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($model->advanceEnrolments as $enrolment){ ?>
                <tr>
                    <td><?= $i ?>. <?= Html::button($enrolment->season->seasonName, ['value' => Url::to(['/accounting/student/show-advance-enrolment', 'id' => $enrolment->id]), 'style' => 'border: none; background: none; color: #23528F;', 'class' => 'show-season']) ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
<?php } ?>
<?php
  Modal::begin([
    'id' => 'genericModal',
    'size' => "modal-lg",
    'header' => '<div id="genericModalHeader"></div>'
  ]);
  echo '<div id="genericModalContent" style="min-height:480px"></div>';
  Modal::end();
?>
<?php
        $script = '
            $( document ).ready(function() {

                $(".show-season").click(function(){
                  $("#genericModal").modal("show").find("#genericModalContent").load($(this).attr("value"));
                });

            });
';
$this->registerJs($script);
   
?>