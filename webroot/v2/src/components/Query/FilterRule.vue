<template>
  <div class="filter-rule">
    <div
      class="row items-center"
      :class="{
        'filter-rule--and': operand === FilterOperand.And,
        'filter-rule--or': operand === FilterOperand.Or
      }"
    >
      <q-icon
        name="drag_indicator"
        size="md"
        class="drag-handle"
        @mousedown="$emit('dragMouseDown')"
        @mouseup="$emit('dragMouseUp')"
      />
      <div class="col row q-col-gutter-sm">
        <q-select
          class="col-12 col-md-4"
          outlined
          :model-value="column"
          @update:model-value="updateColumn"
          :options="options"
          :label="t('queries.filter.column')"
          autocomplete="off"
          dense
          bg-color="white"
        />
        <q-select
          class="col-12 col-md-4"
          outlined
          :model-value="comparator"
          @update:model-value="updateComparator"
          :options="comparatorOptions"
          :label="t('queries.filter.comparator')"
          autocomplete="off"
          :error="!comparatorIsValid && column !== undefined && comparator !== undefined"
          hide-bottom-space
          dense
          :disable="column === undefined"
          :bg-color="column === undefined ? 'transparent' : 'white'"
        />
        <!--suppress PointlessBooleanExpressionJS -->
        <q-input
          v-if="hasInputCriteria || column === undefined"
          class="col-12 col-md-4"
          outlined
          :model-value="criteria"
          @update:model-value="updateCriteria"
          :label="t('queries.filter.criteria')"
          autocomplete="off"
          hide-bottom-space
          dense
          :type="criteriaInputType"
          :step="criteriaStep"
          :disable="comparator === undefined"
          :stack-label="column?.type === DataType.Date"
          :bg-color="comparator === undefined ? 'transparent' : 'white'"
        />
        <q-space
          class="col-12 col-md-4"
          v-else
        />
      </div>
      <q-icon
        :name="ruleIsValid ? 'check' : 'warning'"
        :color="ruleIsValid ? 'positive' : 'negative'"
        size="sm"
        class="q-ml-sm"
      />
      <q-btn
        icon="delete_outline"
        dense
        class="delete-button"
        @click="deleteRule"
        rounded
        flat
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import {computed, PropType} from 'vue';
import {useI18n} from 'vue-i18n';
import {
  DataType,
  FilterComparatorOption,
  FilterCriteria,
  FilterLeaf,
  FilterOperand,
  FilterOption
} from 'src/store/module-query/state';
import {useStore} from 'src/store';
import {FilterComparator} from 'src/models/filterOptions';

const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
const store = useStore();

defineEmits(['dragMouseDown', 'dragMouseUp'])

const props = defineProps({
  options: {
    type: Object as PropType<Array<FilterOption>>,
    required: true,
  },
  node: {
    type: Object as PropType<FilterLeaf>,
    required: true,
  },
  operand: {
    type: String as PropType<FilterOperand>,
    required: true,
  }
});

// noinspection TypeScriptUnresolvedVariable
const column = computed<FilterOption | undefined>(() => props.node.filter.column)
// noinspection TypeScriptUnresolvedVariable
const comparator = computed<FilterComparatorOption | undefined>(() => props.node.filter.comparator)
// noinspection TypeScriptUnresolvedVariable
const criteria = computed<FilterCriteria | undefined>(() => props.node.filter.criteria);

function updateColumn(value: FilterOption) {
  store.commit('query/updateFilterColumn', {node: props.node, value})
}

function updateComparator(value: FilterComparatorOption) {
  store.commit('query/updateFilterComparator', {node: props.node, value})
}

function updateCriteria(value: FilterCriteria) {
  store.commit('query/updateFilterCriteria', {node: props.node, value})
}

function deleteRule() {
  store.commit('query/deleteFilter', {node: props.node})
}

const allComparatorOptions: FilterComparatorOption[] = [
  {
    label: t('queries.filter.equals'),
    value: FilterComparator.Equal,
    type: [DataType.Integer, DataType.Float, DataType.String, DataType.Date]
  },
  {
    label: t('queries.filter.notEquals'),
    value: FilterComparator.NotEqual,
    type: [DataType.Integer, DataType.Float, DataType.String, DataType.Date]
  },
  {
    label: t('queries.filter.less'),
    value: FilterComparator.Less,
    type: [DataType.Integer, DataType.Float, DataType.Date]
  },
  {
    label: t('queries.filter.lessOrEqual'),
    value: FilterComparator.LessOrEqual,
    type: [DataType.Integer, DataType.Float, DataType.Date]
  },
  {
    label: t('queries.filter.greater'),
    value: FilterComparator.Greater,
    type: [DataType.Integer, DataType.Float, DataType.Date]
  },
  {
    label: t('queries.filter.greaterOrEqual'),
    value: FilterComparator.GreaterOrEqual,
    type: [DataType.Integer, DataType.Float, DataType.Date]
  },
  {label: t('queries.filter.startsWith'), value: FilterComparator.StartsWith, type: [DataType.String]},
  {label: t('queries.filter.startsNotWith'), value: FilterComparator.StartsNotWith, type: [DataType.String]},
  {label: t('queries.filter.contains'), value: FilterComparator.Contains, type: [DataType.String]},
  {label: t('queries.filter.notContains'), value: FilterComparator.NotContains, type: [DataType.String]},
  {label: t('queries.filter.endsWith'), value: FilterComparator.EndsWith, type: [DataType.String]},
  {label: t('queries.filter.notEndsWith'), value: FilterComparator.NotEndsWith, type: [DataType.String]},
  {label: t('queries.filter.empty'), value: FilterComparator.Empty, type: [DataType.String]},
  {label: t('queries.filter.notEmpty'), value: FilterComparator.NotEmpty, type: [DataType.String]},
  {label: t('queries.filter.hasPhoto'), value: FilterComparator.NotEmpty, type: [DataType.Photo]},
  {label: t('queries.filter.isTrue'), value: FilterComparator.NotEmpty, type: [DataType.Boolean]},
  {label: t('queries.filter.isFalse'), value: FilterComparator.Empty, type: [DataType.Boolean]},
]

const comparatorOptions = computed<FilterComparatorOption[]>(() => {
  return allComparatorOptions.filter((option: FilterComparatorOption) =>
    option.type.find(type => type === column.value?.type)
  )
});

const comparatorIsValid = computed<boolean>(() =>
  comparatorOptions.value.find((c: FilterComparatorOption) => c.value === comparator.value?.value) !== undefined
)

const hasInputCriteria = computed<boolean>(() => {
  switch (column.value?.type) {
    case DataType.Date:
    case DataType.Integer:
    case DataType.Float:
      return true
    case DataType.String:
      return comparator.value?.value !== FilterComparator.Empty
        && comparator.value?.value !== FilterComparator.NotEmpty
    default:
      return false
  }
})

const criteriaInputType = computed<'date' | 'number' | 'text'>(() => {
  switch (column.value?.type) {
    case DataType.Date:
      return 'date'
    case DataType.Integer:
    case DataType.Float:
      return 'number'
    default:
      return 'text'
  }
})

const criteriaStep = computed<number | false>(() => {
  switch (column.value?.type) {
    case DataType.Integer:
      return 1
    case DataType.Float:
      return 0.1
    default:
      return false
  }
})

const criteriaInputIsValid = computed<boolean>(() => {
  if (typeof criteria.value !== 'string') {
    return false
  }

  switch (column.value?.type) {
    case DataType.Integer:
      return Number.parseFloat(criteria.value) % 1 === 0.0
    case DataType.Float:
      return ! isNaN(Number.parseFloat(criteria.value))
    case DataType.Date:
      return ! isNaN(Date.parse(criteria.value))
    default:
      return criteria.value.length > 0
  }
})

const ruleIsValid = computed<boolean>(() => {
  return !! (column.value && comparatorIsValid.value && criteriaInputIsValid.value)
})

</script>

<style scoped>
.filter-rule {
  padding: 4px 3px 4px 1px;
  background: #EFEFEF;
}

.filter-rule--and {
  border-left-color: var(--q-primary);
}

.filter-rule--or {
  border-left-color: var(--q-accent);
}

.drag-handle {
  color: rgba(0, 0, 0, 0.6);
  cursor: grab;
}

.drag-handle:hover {
  color: var(--q-primary);
}

.delete-button {
  color: rgba(0, 0, 0, 0.6);
}

.delete-button:hover,
.delete-button:focus {
  color: var(--q-negative);
}
</style>
