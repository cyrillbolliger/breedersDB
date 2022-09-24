<template>
  <div class="filter-rule">
    <div
      :class="{
        'filter-rule--and': operand === FilterOperand.And,
        'filter-rule--or': operand === FilterOperand.Or
      }"
      class="row items-center"
    >
      <q-icon
        class="drag-handle"
        name="drag_indicator"
        size="md"
        @mousedown="$emit('dragMouseDown')"
        @mouseup="$emit('dragMouseUp')"
      />
      <div class="col row q-col-gutter-sm">
        <q-select
          :label="t('queries.filter.column')"
          :model-value="column"
          :options="filterOptions"
          autocomplete="off"
          bg-color="white"
          class="col-12 col-md-4"
          dense
          outlined
          @update:model-value="updateColumn"
        />
        <q-select
          :bg-color="column === undefined ? 'transparent' : 'white'"
          :disable="column === undefined"
          :error="!comparatorIsValid && column !== undefined && comparator !== undefined"
          :label="t('queries.filter.comparator')"
          :model-value="comparator"
          :options="comparatorOptions"
          autocomplete="off"
          class="col-12 col-md-4"
          dense
          hide-bottom-space
          outlined
          @update:model-value="updateComparator"
        />
        <!--suppress PointlessBooleanExpressionJS -->
        <q-input
          v-if="hasInputCriteria || column === undefined"
          :bg-color="comparator === undefined ? 'transparent' : 'white'"
          :disable="comparator === undefined"
          :label="t('queries.filter.criteria')"
          :model-value="criteria"
          :stack-label="column?.type === PropertySchemaOptionType.Date"
          :step="criteriaStep"
          :type="criteriaInputType"
          autocomplete="off"
          class="col-12 col-md-4"
          dense
          hide-bottom-space
          outlined
          @update:model-value="updateCriteria"
        />
        <q-space
          v-else
          class="col-12 col-md-4"
        />
      </div>
      <q-icon
        :color="ruleIsValid ? 'positive' : 'negative'"
        :name="ruleIsValid ? 'check' : 'warning'"
        class="q-ml-sm"
        size="sm"
      />
      <q-btn
        class="delete-button"
        dense
        flat
        icon="delete_outline"
        rounded
        @click="deleteRule"
      />
    </div>
  </div>
</template>

<script lang="ts" setup>
import {computed, PropType} from 'vue';
import {useI18n} from 'vue-i18n';
import {
  FilterComparator,
  FilterComparatorOption,
  FilterCriteria,
  FilterOperand,
  FilterOption
} from 'src/models/query/filterTypes';
import {FilterNode} from 'src/models/query/filterNode';
import {PropertySchema, PropertySchemaOptionType} from 'src/models/query/filterOptionSchema';

const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method

defineEmits(['dragMouseDown', 'dragMouseUp'])

const props = defineProps({
  options: {
    type: Object as PropType<PropertySchema[]>,
    required: true,
  },
  node: {
    type: Object as PropType<FilterNode>,
    required: true,
  },
  operand: {
    type: String as PropType<FilterOperand>,
    required: true,
  }
});

// noinspection TypeScriptUnresolvedFunction
const filterRule = computed(() => props.node.getFilterRule())
const column = computed(() => filterRule.value?.column)
const comparator = computed(() => filterRule.value?.comparator)
const criteria = computed(() => filterRule.value?.criteria);
const filterOptions = computed<FilterOption[]>(() => {
  // noinspection TypeScriptUnresolvedFunction
  return props.options.map((option: PropertySchema) => {
    return {
      label: option.label,
      value: option.name,
      type: option.options.type,
    } as FilterOption;
  });
})

function updateColumn(value: FilterOption) {
  filterRule.value.column = value;
}

function updateComparator(value: FilterComparatorOption) {
  filterRule.value.comparator = value;
}

function updateCriteria(value: FilterCriteria) {
  filterRule.value.criteria = value;
}

function deleteRule() {
  // noinspection TypeScriptUnresolvedFunction
  props.node.remove();
}

const allComparatorOptions: FilterComparatorOption[] = [
  {
    label: t('queries.filter.equals'),
    value: FilterComparator.Equal,
    type: [PropertySchemaOptionType.Integer, PropertySchemaOptionType.Float, PropertySchemaOptionType.String, PropertySchemaOptionType.Date]
  },
  {
    label: t('queries.filter.notEquals'),
    value: FilterComparator.NotEqual,
    type: [PropertySchemaOptionType.Integer, PropertySchemaOptionType.Float, PropertySchemaOptionType.String, PropertySchemaOptionType.Date]
  },
  {
    label: t('queries.filter.less'),
    value: FilterComparator.Less,
    type: [PropertySchemaOptionType.Integer, PropertySchemaOptionType.Float, PropertySchemaOptionType.Date]
  },
  {
    label: t('queries.filter.lessOrEqual'),
    value: FilterComparator.LessOrEqual,
    type: [PropertySchemaOptionType.Integer, PropertySchemaOptionType.Float, PropertySchemaOptionType.Date]
  },
  {
    label: t('queries.filter.greater'),
    value: FilterComparator.Greater,
    type: [PropertySchemaOptionType.Integer, PropertySchemaOptionType.Float, PropertySchemaOptionType.Date]
  },
  {
    label: t('queries.filter.greaterOrEqual'),
    value: FilterComparator.GreaterOrEqual,
    type: [PropertySchemaOptionType.Integer, PropertySchemaOptionType.Float, PropertySchemaOptionType.Date]
  },
  {label: t('queries.filter.startsWith'), value: FilterComparator.StartsWith, type: [PropertySchemaOptionType.String]},
  {
    label: t('queries.filter.startsNotWith'),
    value: FilterComparator.StartsNotWith,
    type: [PropertySchemaOptionType.String]
  },
  {label: t('queries.filter.contains'), value: FilterComparator.Contains, type: [PropertySchemaOptionType.String]},
  {
    label: t('queries.filter.notContains'),
    value: FilterComparator.NotContains,
    type: [PropertySchemaOptionType.String]
  },
  {label: t('queries.filter.endsWith'), value: FilterComparator.EndsWith, type: [PropertySchemaOptionType.String]},
  {
    label: t('queries.filter.notEndsWith'),
    value: FilterComparator.NotEndsWith,
    type: [PropertySchemaOptionType.String]
  },
  {label: t('queries.filter.empty'), value: FilterComparator.Empty, type: [PropertySchemaOptionType.String]},
  {label: t('queries.filter.notEmpty'), value: FilterComparator.NotEmpty, type: [PropertySchemaOptionType.String]},
  {label: t('queries.filter.hasPhoto'), value: FilterComparator.NotEmpty, type: [PropertySchemaOptionType.Photo]},
  {label: t('queries.filter.isTrue'), value: FilterComparator.NotEmpty, type: [PropertySchemaOptionType.Boolean]},
  {label: t('queries.filter.isFalse'), value: FilterComparator.Empty, type: [PropertySchemaOptionType.Boolean]},
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
    case PropertySchemaOptionType.Date:
    case PropertySchemaOptionType.Integer:
    case PropertySchemaOptionType.Float:
      return true
    case PropertySchemaOptionType.String:
      return comparator.value?.value !== FilterComparator.Empty
        && comparator.value?.value !== FilterComparator.NotEmpty
    default:
      return false
  }
})

const criteriaInputType = computed<'date' | 'number' | 'text'>(() => {
  switch (column.value?.type) {
    case PropertySchemaOptionType.Date:
      return 'date'
    case PropertySchemaOptionType.Integer:
    case PropertySchemaOptionType.Float:
      return 'number'
    default:
      return 'text'
  }
})

const criteriaStep = computed<number | false>(() => {
  switch (column.value?.type) {
    case PropertySchemaOptionType.Integer:
      return 1
    case PropertySchemaOptionType.Float:
      return 0.1
    default:
      return false
  }
})

const criteriaInputIsValid = computed<boolean>(() => {
  if (column.value?.type === PropertySchemaOptionType.Boolean
    || column.value?.type === PropertySchemaOptionType.Photo
  ) {
    return true;
  }

  if (typeof criteria.value !== 'string') {
    return false
  }

  switch (column.value?.type) {
    case PropertySchemaOptionType.Integer:
      return Number.parseFloat(criteria.value) % 1 === 0.0
    case PropertySchemaOptionType.Float:
      return ! isNaN(Number.parseFloat(criteria.value))
    case PropertySchemaOptionType.Date:
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
