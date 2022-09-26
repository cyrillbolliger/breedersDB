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
import {computed, PropType, ref, watch} from 'vue';
import {PropertySchema} from 'src/models/query/filterOptionSchema';
import {FilterOption} from 'src/models/query/filterTypes';

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

function filterFilterOptions(value: string, update: (cb: () => void) => void) {
  if (value === '') {
    update(() => {
      filteredFilterOptions.value = filterOptions.value;
    })
    return;
  }

  update(() => {
    const locale = navigator.languages[0] || navigator.language;
    const needle = value.toLocaleLowerCase(locale);

    filteredFilterOptions.value = filterOptions.value.filter(
      v => v.label.toLocaleLowerCase(locale).indexOf(needle) > -1
    );
  })
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
