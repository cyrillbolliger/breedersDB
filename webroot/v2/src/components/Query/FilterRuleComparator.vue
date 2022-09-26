<template>
  <q-select
    :bg-color="disabled ? 'transparent' : 'white'"
    :disable="disabled"
    :error="isInvalid"
    :label="t('queries.filter.comparator')"
    :model-value="modelValue"
    :options="comparatorOptions"
    autocomplete="off"
    class="col-12 col-md-4"
    dense
    hide-bottom-space
    outlined
    @update:model-value="value => $emit('update:modelValue', value)"
  />
</template>
<script lang="ts" setup>
import {useI18n} from 'vue-i18n';
import {computed, PropType, watch} from 'vue';
import {FilterComparator, FilterComparatorOption} from 'src/models/query/filterTypes';
import {PropertySchema, PropertySchemaOptionType} from 'src/models/query/filterOptionSchema';

const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method

const emit = defineEmits<{
  (e: 'update:modelValue', value: FilterComparatorOption): void
  (e: 'valid'): void
  (e: 'invalid'): void
}>()

const props = defineProps({
  schema: Object as PropType<PropertySchema>,
  disabled: {
    type: Boolean,
    required: true,
  },
  modelValue: Object as PropType<FilterComparatorOption>,
})

const allComparatorOptions: FilterComparatorOption[] = [
  {
    label: t('queries.filter.equals'),
    value: FilterComparator.Equal,
    type: [PropertySchemaOptionType.Integer, PropertySchemaOptionType.Float, PropertySchemaOptionType.String, PropertySchemaOptionType.Date, PropertySchemaOptionType.Enum]
  },
  {
    label: t('queries.filter.notEquals'),
    value: FilterComparator.NotEqual,
    type: [PropertySchemaOptionType.Integer, PropertySchemaOptionType.Float, PropertySchemaOptionType.String, PropertySchemaOptionType.Date, PropertySchemaOptionType.Enum]
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
  // noinspection TypeScriptUnresolvedVariable
  return allComparatorOptions.filter((option: FilterComparatorOption) =>
    option.type.find(type => type === props.schema?.options.type)
  )
});

const isValid = computed<boolean>(() => {
  if (undefined === props.modelValue || ! ('value' in props.modelValue)) {
    return false;
  }

  // noinspection TypeScriptUnresolvedVariable
  return comparatorOptions.value
    .findIndex((c: FilterComparatorOption) => c.value === props.modelValue.value) > -1;
})

const isInvalid = computed<boolean>(() => {
  return ! isValid.value && props.modelValue !== undefined;
})

watch(isValid, () => {
  if (isValid.value) {
    emit('valid');
  }
});

watch(isInvalid, () => {
  if (isInvalid.value) {
    emit('invalid');
  }
})
</script>
