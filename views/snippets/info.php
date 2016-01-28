<div class="info">
  <ul class="nav">
    <? foreach($pattern->files() as $f): ?>
    <li><?= html::a($pattern->url() . '/' . $f->filename(), $f->filename(), ['class' => ($file and $f->filename() == $file->filename()) ? 'active' : '']) ?></li>
    <? endforeach ?>
  </ul>
</div>
