<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?= $title ?></title>
  <?= css($lab->theme()->css()) ?>
</head>
<body>
  <header class="topbar bar">
    <h1><?= html::a($lab->url(), $lab->title()) ?></h1>
  </header>
  <nav class="menu">
    <?= $menu ?>
  </nav>
  <?= $content ?>
  <? if(!empty($modal)): ?>
  <div class="modal">
    <div class="modal-content">
      <?= $modal ?>
    </div>
  </div>
  <? endif ?>
  <?= js($lab->theme()->js()) ?>
</body>
</html>