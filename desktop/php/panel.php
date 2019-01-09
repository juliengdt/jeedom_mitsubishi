<?php
if (!isConnect()) {
  throw new Exception('{{401 - Accès non autorisé}}');
}
?>

<div class="row">

<div class="col-md-12"><i class="fa fa-pencil pull-right cursor reportModeHidden" id="bt_editDashboardWidgetOrder" data-mode="0" style="margin-right : 10px;margin-top:7px;"></i>

  <div class="col-md-6">
    <div class="div_displayEquipement">
      <?php
      foreach (eqLogic::byType('mitsubishi') as $eqLogic) {
        echo $eqLogic->toHtml('dview');
      }
      ?>
    </div>
  </div>
</div>
<?php include_file('desktop', 'panel', 'js', 'mitsubishi');?>
