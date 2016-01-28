<div class="toolbar bar">
  <nav class="buttons">
    <?= html::a('?view=php', 'PHP', ['class' => r($view == 'php', 'active')]) ?>
    <?= html::a('?view=html', 'HTML', ['class' => r($view == 'html', 'active')]) ?>
    <?= html::a('?view=preview', 'Preview', ['class' => r($view == 'preview', 'active')]) ?>
    <?= html::a($pattern->url() . '/preview', 'Raw', ['target' => '_blank']) ?>
  </nav>
</div>

<div class="preview">  
  <?= $content ?>
</div>