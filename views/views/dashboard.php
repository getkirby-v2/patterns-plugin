<div class="dashboard">
  <div class="toolbar bar"></div>

  <? if($markdown): ?>
  <div class="preview preview-markdown">
    <div class="text">
      <?= $markdown ?>
    </div>
  </div>
  <? else: ?>
  <div class="preview">
    <div class="error">
      Add a <em>readme.md</em> to your patterns folder<br> to make this screen more informative
    </div>
  </div>
  <? endif ?>

</div>