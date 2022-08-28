<template>
  <q-icon name="drag_indicator"/>
  <q-select
    outlined
    v-model="column"
    :options="options"
    :label="t('queries.filter.column')"
    autocomplete="off"
  />
  <q-select
    outlined
    v-model="comparator"
    :options="comparatorOptions"
    :label="t('queries.filter.comparator')"
    autocomplete="off"
    :error="!comparatorIsValid && column !== undefined && comparator !== undefined"
  />

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
    type: [DataType.Integer, DataType.Float, DataType.String, DataType.Boolean, DataType.Date]
  },
  {
    label: t('queries.filter.notEquals'),
    value: FilterComparator.NotEqual,
    type: [DataType.Integer, DataType.Float, DataType.String, DataType.Boolean, DataType.Date]
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
  {label: t('queries.filter.has'), value: FilterComparator.NotEmpty, type: [DataType.Photo]},
]


const column = ref<FilterOption>()
const comparator = ref<FilterComparatorOption>()

const comparatorOptions = computed<FilterComparatorOption[]>(() => {
  return allComparatorOptions.filter((option: FilterComparatorOption) =>
    option.type.find(type => type === column.value?.type)
  )
});

const comparatorIsValid = computed<boolean>(
  () =>
    comparatorOptions.value.find((c: FilterComparatorOption) => c.value === comparator.value?.value) !== undefined
)


</script>

<style>

</style>
