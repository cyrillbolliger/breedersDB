<template>
  <q-space
    v-if="hide"
    class="col-12 col-md-4"
  />

  <template v-else>
    <q-select
      v-if="isEnum"
      :bg-color="disabled ? 'transparent' : 'white'"
      :disable="disabled"
      :error="isInvalid"
      :label="t('queries.filter.criteria')"
      :model-value="modelValue"
      :options="filteredSelectOptions"
      autocomplete="off"
      class="col-12 col-md-4"
      dense
      hide-bottom-space
      outlined
      use-input
      @filter="filterSelectOptions"
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

    <q-input
      v-else
      :bg-color="disabled ? 'transparent' : 'white'"
      :disable="disabled"
      :error="isInvalid"
      :label="t('queries.filter.criteria')"
      :max="maxValue"
      :maxlength="maxLen"
      :min="minValue"
      :model-value="modelValue"
      :pattern="pattern"
      :stack-label="isDate || isTime"
      :step="step"
      :type="inputType"
      autocomplete="off"
      class="col-12 col-md-4"
      dense
      hide-bottom-space
      outlined
      @update:model-value="value => $emit('update:modelValue', value.trim())"
    />
  </template>
</template>

<script lang="ts" setup>
import {computed, PropType, ref, watch} from 'vue';
import {PropertySchema, PropertySchemaOptions, PropertySchemaOptionType} from 'src/models/query/filterOptionSchema';
import {useI18n} from 'vue-i18n';
import naturalSort from 'src/composables/naturalSort';

const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
  (e: 'valid'): void
  (e: 'invalid'): void
}>()

const props = defineProps({
  schema: {
    type: Object as PropType<PropertySchema | null>,
    required: false,
  },
  disabled: {
    type: Boolean,
    required: true,
  },
  hide: {
    type: Boolean,
    required: true,
  },
  modelValue: String,
});

// noinspection TypeScriptUnresolvedVariable
const type = computed<null | PropertySchemaOptionType>(() => props.schema?.options.type || null);

const isEnum = computed<boolean>(() => {
  return type.value === PropertySchemaOptionType.Enum;
});

const isDate = computed<boolean>(() => {
  return type.value === PropertySchemaOptionType.Date;
})

const isTime = computed<boolean>(() => {
  return type.value === PropertySchemaOptionType.Time;
})

const step = computed<number | undefined>(() => {
  // noinspection TypeScriptUnresolvedVariable
  const options: PropertySchemaOptions | undefined = props.schema?.options || undefined;
  if (options && 'validation' in options && 'step' in options.validation) {
    return options.validation.step;
  }
  return undefined;
})

const minValue = computed<number | undefined>(() => {
  // noinspection TypeScriptUnresolvedVariable
  const options: PropertySchemaOptions | undefined = props.schema?.options || undefined;
  if (options && 'validation' in options && 'min' in options.validation) {
    return options.validation.min;
  }
  return undefined;
})

const maxValue = computed<number | undefined>(() => {
  // noinspection TypeScriptUnresolvedVariable
  const options: PropertySchemaOptions | undefined = props.schema?.options || undefined;
  if (options && 'validation' in options && 'max' in options.validation) {
    return options.validation.max;
  }
  return undefined;
})

const maxLen = computed<number | undefined>(() => {
  // noinspection TypeScriptUnresolvedVariable
  const options: PropertySchemaOptions | undefined = props.schema?.options || undefined;
  if (options && 'validation' in options && 'maxLen' in options.validation) {
    return options.validation.maxLen || undefined;
  }
  return undefined;
})

const pattern = computed<string | undefined>(() => {
  // noinspection TypeScriptUnresolvedVariable
  const options: PropertySchemaOptions | undefined = props.schema?.options || undefined;
  if (options && 'validation' in options && 'pattern' in options.validation) {
    return options.validation.pattern || undefined;
  }
  return undefined;
})

const selectOptions = computed<string[]>(() => {
  // noinspection TypeScriptUnresolvedVariable
  const options: PropertySchemaOptions | null = props.schema?.options || null;
  if (options && 'validation' in options && 'options' in options.validation) {
    const selectOptions = options.validation.options;
    return naturalSort(selectOptions);
  }
  return [];
})

const filteredSelectOptions = ref(selectOptions.value);

const inputType = computed<'date' | 'time' | 'number' | 'text'>(() => {
  switch (type.value) {
    case PropertySchemaOptionType.Date:
      return 'date'
    case PropertySchemaOptionType.Time:
      return 'time'
    case PropertySchemaOptionType.Integer:
    case PropertySchemaOptionType.Float:
      return 'number'
    default:
      return 'text'
  }
})

const isValidInteger = computed<boolean>(() => {
  if (props.modelValue === undefined || type.value !== PropertySchemaOptionType.Integer) {
    return false;
  }

  const value = Number.parseFloat(props.modelValue);

  if (step.value !== undefined && value % step.value !== 0.0) {
    return false;
  }

  if (minValue.value !== undefined && value < minValue.value) {
    return false;
  }

  return ! (maxValue.value !== undefined && value > maxValue.value);
})

const isValidFloat = computed<boolean>(() => {
  if (props.modelValue === undefined || type.value !== PropertySchemaOptionType.Float) {
    return false;
  }

  const value = Number.parseFloat(props.modelValue);

  if (step.value !== undefined && value % step.value !== 0.0) {
    return false;
  }

  if (minValue.value !== undefined && value < minValue.value) {
    return false;
  }

  return ! (maxValue.value !== undefined && value > maxValue.value);
})

const isValidDate = computed<boolean>(() => {
  if (props.modelValue === undefined || type.value !== PropertySchemaOptionType.Date) {
    return false;
  }

  return ! isNaN(Date.parse(props.modelValue));
})

const isValidString = computed<boolean>(() => {
  if (props.modelValue === undefined || type.value !== PropertySchemaOptionType.String) {
    return false;
  }

  if (maxLen.value !== undefined && props.modelValue.length > maxLen.value) {
    return false;
  }

  return ! (pattern.value !== undefined && props.modelValue.match(`/${pattern.value}/`) === null);
})

const isValidEnum = computed<boolean>(() => {
  if (props.modelValue === undefined || type.value !== PropertySchemaOptionType.Enum) {
    return false;
  }

  return selectOptions.value.indexOf(props.modelValue) > -1;
})

const isValidPhoto = computed<boolean>(() => {
  return ! (props.modelValue === undefined || type.value !== PropertySchemaOptionType.Photo);
})

const isValidBoolean = computed<boolean>(() => {
  return ! (props.modelValue === undefined || type.value !== PropertySchemaOptionType.Boolean);
})

const isValid = computed<boolean>(() => {
  // todo: implement isValidTime, isValidTimestamp
  return isValidInteger.value
    || isValidFloat.value
    || isValidDate.value
    || isValidString.value
    || isValidEnum.value
    || isValidPhoto.value
    || isValidBoolean.value;
})

const isInvalid = computed<boolean>(() => {
  return ! isValid.value && props.modelValue !== undefined;
})

function filterSelectOptions(value: string, update: (cb: () => void) => void) {
  if (value === '') {
    update(() => {
      filteredSelectOptions.value = selectOptions.value;
    })
    return;
  }

  update(() => {
    const locale = navigator.languages[0] || navigator.language;
    const needle = value.toLocaleLowerCase(locale);

    filteredSelectOptions.value = selectOptions.value.filter(
      v => v.toLocaleLowerCase(locale).indexOf(needle) > -1
    );
  })
}

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
