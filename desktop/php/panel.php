<?php
if (!isConnect()) {
  throw new Exception('{{401 - Accès non autorisé}}');
}
?>

<div class="row">

  echo '<div class="col-md-6">';
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
