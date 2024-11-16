<?php
$pre = "color: whitesmoke; background-color: blue; font-family: Consolas; padding: 5px; border-radius: 3px;";
$pre_ = "color: white; background-color: darkblue; font-family: Consolas; padding: 5px; border-radius: 3px";
?>
<style>
  .child-text-wrap * {
    text-wrap: inherit;
  }
</style>
<div class="child-text-wrap"
  style="color: white; background-color:black; border-radius: 5px; padding: 15px; font-family: sans-serif; text-wrap: wrap;">
  <b style="color: red"><?= get_class($throwable) ?></b>
  <i style="background-color: blueviolet; padding: 5px; border-radius: 3px"><?= $throwable->getCode() ?></i>

  <pre style="<?= $pre ?>"><?= $throwable->getMessage() ?></pre>
  <h3>Stack Trace</h3>
  <pre style="<?= $pre_ ?>"><?= $throwable->getTraceAsString() ?></pre>
  <b style="margin:0; padding:0">At</b>
  <i style="font-weight: bold; ">
    <span style="color:yellow;"><?= $throwable->getFile() ?></span>
    Line
    <span style="color:blueviolet"><?= $throwable->getLine() ?></span>
  </i>
  <hr>
  <?= ($prev = $throwable->getPrevious()) && view('pluslib::exception', ['throwable' => $prev]) ?>
</div>