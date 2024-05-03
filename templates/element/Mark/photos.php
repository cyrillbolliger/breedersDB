<?php
/** @var $allMarks Marks[] */
$allMarks = [];
foreach ($marks as $mark_values) {
    $allMarks = array_merge($allMarks, $mark_values->toArray());
}
$photoMarks = array_filter($allMarks, function ($m) {
    return $m->mark_form_property->field_type === 'PHOTO';
});
$imgs = array_map(function ($m) {
    $url = $this->Url->build(['prefix' => 'REST1', 'controller' => 'Photos', 'action' => 'view', $m->value]);
    $fileExt = pathinfo($url, PATHINFO_EXTENSION);
    $entityName = $m->mark->tree?->name ?? $m->mark->tree?->publicid ?? $m->mark->variety?->convar ?? $m->mark->batch?->code;
    return [
        'url' => $url,
        'title' => $entityName . ', ' . $m->mark_form_property->name . ', ' . $m->mark->date . ', ' . $m->mark->author,
        'downloadName' => \Cake\Core\Configure::read('Org.abbreviation')
            . '-' . $entityName
            . '-' . $m->mark->created->format('Ymd\THis')
            . '-' . $m->mark->id
            . '.' . $fileExt,
    ];
}, $photoMarks);
?>

<?php
if (!empty($imgs)): ?>
    <h4><?= __('Photos') ?></h4>
    <div style="display: flex; flex-wrap: wrap; gap: 0.5em">
        <?php
        foreach ($imgs as $img): ?>
            <div style="display: flex; flex-direction: column;">
                <a href="<?= $img['url'] ?>" target="_blank">
                    <img src="<?= $img['url'] ?>?h=200"
                         srcset="<?= $img['url'] ?>?h=200, <?= $img['url'] ?>?h=400 2x"
                         alt="<?= $img['title'] ?>"
                         title="<?= $img['title'] ?>"
                         style="height: 200px"
                    >
                </a>
                <a
                    href="<?= $img['url'] ?>"
                    class="button"
                    style="padding: 0.5em; margin: 0"
                    download="<?= $img['downloadName'] ?>"
                ><?= __('Download') ?></a>
            </div>
        <?php
        endforeach; ?>
    </div>
<?php
endif; ?>
