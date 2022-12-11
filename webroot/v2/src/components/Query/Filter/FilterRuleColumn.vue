<template>
  <q-select
    :error="isInvalid"
    :label="t('queries.filter.column')"
    :model-value="modelValue"
    :options="filteredFilterOptions"
    autocomplete="off"
    bg-color="white"
    class="col-12 col-md-4"
    dense
    hide-bottom-space
    outlined
    use-input
    @filter="filterFilterOptions"
    @update:model-value="value => $emit('update:modelValue', value)"
  >
    <template v-slot:no-option>
      <q-item>
        <q-item-section class="text-grey">
          {{t('queries.filter.noResults')}}
        </q-item-section>
      </q-item>
    </template>
  </q-select>
</template>
<script lang="ts" setup>
import {useI18n} from 'vue-i18n';
import {computed, onMounted, PropType, ref, watch} from 'vue';
import {PropertySchema} from 'src/models/query/filterOptionSchema';
import {FilterOption} from 'src/models/query/filterTypes';
import {filterOptions as filterSelectOptions, FilterUpdateFn} from 'src/composables/queries/filterRuleSelectOptionFilter';

const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method

const emit = defineEmits<{
  (e: 'update:modelValue', value: PropertySchema): void
  (e: 'valid'): void
  (e: 'invalid'): void
}>()

const props = defineProps({
  options: {
    type: Object as PropType<PropertySchema[]>,
    required: true,
  },
  modelValue: Object as PropType<PropertySchema>,
})

const filterOptions = computed<FilterOption[]>(() => {
  // noinspection TypeScriptUnresolvedFunction
  return props.options.map((option: PropertySchema) => {
    return {
      label: option.label,
      value: option.name,
      schema: option,
    } as FilterOption;
  });
})

const filteredFilterOptions = ref(filterOptions.value);

function filterFilterOptions(value: string, update: FilterUpdateFn) {
  filterSelectOptions<FilterOption>(
    value,
    update,
    filterOptions.value,
    filteredFilterOptions,
    item => item.label
  );
}

const isValid = computed<boolean>(() => {
  if (props.modelValue === undefined || ! ('value' in props.modelValue)) {
    return false;
  }

  // noinspection TypeScriptUnresolvedVariable
  return filterOptions.value.findIndex(item => item.value === props.modelValue.value) > -1;
})

const isInvalid = computed<boolean>(() => {
  return ! isValid.value && props.modelValue !== undefined;
})

function emitValidity() {
  if (isValid.value) {
    emit('valid');
  }
  if (isInvalid.value) {
    emit('invalid');
  }
}

watch(isValid, emitValidity);
watch(isInvalid, emitValidity);
onMounted(emitValidity);
</script>
