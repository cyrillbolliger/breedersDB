<?php

declare(strict_types=1);

namespace App\Domain\QueryExporter;

use App\Model\Entity\BatchesView;
use App\Model\Entity\MarksView;
use App\Model\Entity\TreesView;
use App\Model\Entity\VarietiesView;
use Cake\Collection\CollectionInterface;
use Cake\Datasource\FactoryLocator;
use Cake\ORM\Entity;
use Cake\Routing\Router;

class MarkedCollectionDataExtractor extends DataExtractor
{
    private const MARK_COLUMNS = [
        // 'marked_obj'  <-- this is added manually and only here for documentation
        //                   only used for varieties
        'Marks.mark_id',
        'Marks.mark_form_id',
        'Marks.property_id',
        'Marks.name',
        'Marks.date',
        'Marks.created',
        'Marks.author',
        'Marks.exceptional_mark',
        'Marks.value',
    ];

    private bool $hasMarkColumns;
    private bool $isVarietiesView;

    private array $currentKey = [
        'row' => 0,
        'mark' => 0,
        'tree' => 0,
        'tree.mark' => 0,
    ];

    private Entity $currentRow;

    public function __construct(
        CollectionInterface $collection,
        array $columnKeys,
        private readonly bool $onlyRowsWithMarks,
    ) {
        parent::__construct($collection, $columnKeys);
        $this->rewind();
    }

    public function next(): void
    {
        if ($this->hasMoreMarks()) {
            $this->currentKey['mark']++;
            return;
        }

        // set mark key to an invalid value so the get
        // marks method will grab the tree mark.
        $this->currentKey['mark'] = -2;

        if ($this->hasMoreTreeMarks()) {
            $this->currentKey['tree.mark']++;
            return;
        }

        while ($this->hasMoreTrees()) {
            $this->currentKey['tree']++;
            $this->currentKey['tree.mark'] = 0;

            if ($this->hasTreeMarks()) {
                return;
            }
        }

        unset($this->currentRow);
        $this->currentKey['row']++;
        $this->currentKey['mark'] = 0;
        $this->currentKey['tree'] = 0;
        $this->currentKey['tree.mark'] = 0;
        $this->collection->next();

        if ($this->onlyRowsWithMarks) {
            $this->advanceCursorUntilRowWithMarks();
        }
    }

    private function hasMoreMarks(): bool
    {
        $row = $this->getCurrentRow();
        $nextMark = $this->currentKey['mark'] + 1;

        return isset($row->marks_view[$nextMark]);
    }

    private function getCurrentRow(): Entity|null
    {
        if (!isset($this->currentRow) && $this->valid()) {
            $this->currentRow = $this->collection->current();
        }

        return $this->currentRow ?? null;
    }

    public function valid(): bool
    {
        return $this->collection->valid();
    }

    public function current(): array|null
    {
        if (!$this->valid()) {
            return null;
        }

        $data = [];
        foreach ($this->getEntityFieldNames() as $name) {
            $data[$name] = $this->collection->current()->$name;
        }

        if ($this->hasMarkColumns()) {
            $data += $this->getMarkColumns() ?? [];
        }

        return $data;
    }

    private function hasMarkColumns(): bool
    {
        if (!isset($this->hasMarkColumns)) {
            $this->hasMarkColumns = false;
            foreach ($this->columnKeys as $key) {
                if (str_starts_with($key, 'Mark.')) {
                    $this->hasMarkColumns = true;
                    break;
                }
            }
        }

        return $this->hasMarkColumns;
    }

    private function getMarkColumns(): array|null
    {
        $mark = $this->getMark();
        if ($mark) {
            return $mark;
        }

        return $this->getTreeMark();
    }

    private function getMark(): array|null
    {
        $row = $this->getCurrentRow();

        if (!$row
            || !isset($row->marks_view[$this->currentKey['mark']])) {
            return null;
        }

        /** @var MarksView $markView */
        $markView = $row->marks_view[$this->currentKey['mark']];

        $markedObj = match (true) {
            $row instanceof BatchesView => $row->crossing_batch,
            $row instanceof VarietiesView => $row->convar,
            $row instanceof TreesView => $row->publicid,
        };

        return $this->getColumnsFromMarksView($markView, $markedObj ?? null);
    }

    private function getColumnsFromMarksView(MarksView $marksView, string $markedObj = null): array
    {
        $mark = [];

        if ($markedObj && $this->isVarietiesView()) {
            $mark['marked_obj'] = $markedObj;
        }

        foreach (self::MARK_COLUMNS as $prefixedColumn) {
            $column = str_replace('Marks.', '', $prefixedColumn);
            if ('value' === $column) {
                $mark[$prefixedColumn] = $this->getTypedMarkValue($marksView);
            } else {
                $mark[$prefixedColumn] = $marksView->$column;
            }
        }

        return $mark;
    }

    private function isVarietiesView(): bool
    {
        if (!isset($this->isVarietiesView)) {
            $row = $this->getCurrentRow();
            $this->isVarietiesView = $row instanceof VarietiesView;
        }

        return $this->isVarietiesView;
    }

    private function getTreeMark(): array|null
    {
        $tree = $this->getCurrentTree();

        if (!$tree || !$this->hasTreeMarks()) {
            return null;
        }

        /** @noinspection PhpUndefinedFieldInspection */
        $marksView = $tree->marks_view[$this->currentKey['tree.mark']];

        return $this->getColumnsFromMarksView($marksView, $tree->publicid);
    }

    private function getCurrentTree(): TreesView|null
    {
        $row = $this->getCurrentRow();
        $treeKey = $this->currentKey['tree'];

        if (!$row
            || !isset($row->trees_view[$treeKey])) {
            return null;
        }

        return $row->trees_view[$treeKey];
    }

    private function hasTreeMarks(): bool
    {
        $tree = $this->getCurrentTree();

        if (!$tree) {
            return false;
        }

        return isset($tree->marks_view[$this->currentKey['tree.mark']]);
    }

    private function hasMoreTreeMarks(): bool
    {
        $tree = $this->getCurrentTree();

        if (!$tree) {
            return false;
        }

        $nextTreeMark = $this->currentKey['tree.mark'] + 1;

        return isset($tree->marks_view[$nextTreeMark]);
    }

    private function hasMoreTrees(): bool
    {
        $row = $this->getCurrentRow();
        $nextTree = $this->currentKey['tree'] + 1;

        return isset($row->trees_view[$nextTree]);
    }

    public function key(): array
    {
        return $this->currentKey;
    }

    public function rewind(): void
    {
        $this->currentKey = [
            'row' => 0,
            'mark' => 0,
            'tree' => 0,
            'tree.mark' => 0,
        ];
        $this->collection->rewind();

        if ($this->onlyRowsWithMarks) {
            $this->advanceCursorUntilRowWithMarks();
        }
    }

    private function advanceCursorUntilRowWithMarks(): void
    {
        while ($this->valid() && !$this->getMark() && !$this->getTreeMark()) {
            $this->next();
        }
    }

    public function getHeaders(): array
    {
        $headers = parent::getHeaders();

        if ($this->hasMarkColumns()) {
            if ($this->isVarietiesView()) {
                $headers[] = __('Marked object');
            }

            $marksTable = FactoryLocator::get('Table')->get('MarksView');
            /** @noinspection PhpPossiblePolymorphicInvocationInspection */
            $marksTableName = $marksTable->getTranslatedName();

            foreach (self::MARK_COLUMNS as $key) {
                $column = str_replace('Marks.', '', $key);
                /** @noinspection PhpPossiblePolymorphicInvocationInspection */
                $headers[] = "$marksTableName > " . $marksTable->getTranslatedColumnName($column);
            }
        }

        return $headers;
    }

    private function getTypedMarkValue(MarksView $marksView): float|\DateTimeImmutable|bool|int|string
    {
        $value = trim($marksView->value);

        if ('' === $value) {
            return '';
        }

        return match ($marksView->field_type) {
            'INTEGER' => (int) $value,
            'FLOAT' => (float) $value,
            'BOOLEAN' => (bool) $value,
            'DATE' => date_create_immutable_from_format('m/d/y H:i:s.u', "{$value} 00:00:00.000000"),
            'PHOTO' => Router::url(['prefix' => 'REST1', 'controller' => 'Photos', 'action' => 'view', $value], true),
            default => $value,
        };
    }
}
