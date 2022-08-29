<template>
  <div class="row items-center">
    <q-icon
      name="drag_indicator"
      size="md"
      class="drag-handle"
    />
    <div class="col row q-col-gutter-sm">
      <q-select
        class="col-12 col-md-4"
        outlined
        v-model="column"
        :options="options"
        :label="t('queries.filter.column')"
        autocomplete="off"
        dense
      />
      <q-select
        class="col-12 col-md-4"
        outlined
        v-model="comparator"
        :options="comparatorOptions"
        :label="t('queries.filter.comparator')"
        autocomplete="off"
        :error="!comparatorIsValid && column !== undefined && comparator !== undefined"
        hide-bottom-space
        dense
        :disable="column === undefined"
      />
      <q-input
        v-if="isInputCriteria || column === undefined"
        class="col-12 col-md-4"
        outlined
        v-model.trim="criteria"
        :label="t('queries.filter.criteria')"
        autocomplete="off"
        :error="!criteriaInputIsValid && column !== undefined && comparator !== undefined"
        hide-bottom-space
        dense
        :type="criteriaInputType"
        :step="criteriaStep"
        :disable="comparator === undefined"
        :stack-label="column?.type === DataType.Date"
      />
      <q-space
        class="col-12 col-md-4"
        v-else
      />
    </div>
    <q-btn
      icon="delete_outline"
      dense
      class="q-ml-sm delete-button"
      rounded
      flat
    />
  </div>

</template>

<script setup lang="ts">
import {computed, PropType, ref} from 'vue';
import {useI18n} from 'vue-i18n';
import {DataType, FilterComparator, FilterComparatorOption, FilterOption} from 'src/models/filterOptions';

const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method

defineProps({
  options: {
    type: Object as PropType<Array<FilterOption>>,
    required: true,
  },
});

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


const column = ref<FilterOption>()
const comparator = ref<FilterComparatorOption>()
const criteria = ref<string>()

const comparatorOptions = computed<FilterComparatorOption[]>(() => {
  return allComparatorOptions.filter((option: FilterComparatorOption) =>
    option.type.find(type => type === column.value?.type)
  )
});

const comparatorIsValid = computed<boolean>(() =>
  comparatorOptions.value.find((c: FilterComparatorOption) => c.value === comparator.value?.value) !== undefined
)

const isInputCriteria = computed<boolean>(() => {
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

</script>

<style scoped>
.drag-handle {
  color: rgba(0, 0, 0, 0.6);
  cursor: grab;
}
.delete-button {
  color: rgba(0, 0, 0, 0.6);
}
</style>
