<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?= $pattern->name() ?></title>
  <?= css($css) ?>
  <? if($background): ?>
  <style>
    html, body {
      background: <?= $background ?> !important;
    }
  </style>
  <? endif ?>
</head>
<body>
  <?= $html ?>
  <?= js($js) ?>
</body>
</html>