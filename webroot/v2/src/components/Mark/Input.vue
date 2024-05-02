<template>
  <mark-input-item
    :title="name"
    :note="note"
    :value="fType === FieldTypes.Rating ? value : null"
  >
    <rating-input
      v-if="fType === FieldTypes.Rating"
      v-model="value"
      :steps="steps"
      :withZero="numberConstraints?.min === 0"
    />

    <!--suppress RequiredAttributes -->
    <q-input
      v-if="fType === FieldTypes.Integer"
      outlined
      v-model="value"
      :label="name"
      type="number"
      :min="numberConstraints.min"
      :max="numberConstraints.max"
      :step="numberConstraints.step"
      :rules="[val => validNumber(val, numberConstraints)]"
    />

    <!--suppress RequiredAttributes -->
    <q-input
      v-if="fType === FieldTypes.Float"
      outlined
      v-model="value"
      :label="name"
      type="number"
      :min="numberConstraints.min"
      :max="numberConstraints.max"
      :step="numberConstraints.step"
      :rules="[val => validNumber(val, numberConstraints)]"
    />

    <!--suppress RequiredAttributes -->
    <q-toggle
      v-if="fType === FieldTypes.Boolean"
      v-model="value"
      checked-icon="check"
      :label="name"
      unchecked-icon="clear"
      toggle-indeterminate
    />

    <!--suppress RequiredAttributes -->
    <q-input
      v-if="fType === FieldTypes.Date"
      outlined
      v-model="value"
      :label="name"
      type="date"
    />

    <!--suppress RequiredAttributes -->
    <q-input
      v-if="fType === FieldTypes.String"
      outlined
      v-model="value"
      :label="name"
      type="text"
      autogrow
      hide-bottom-space
    />

    <template
      v-if="fType === FieldTypes.Photo"
    >
      <!--suppress RequiredAttributes -->
      <q-file
        outlined
        v-model="value"
        :label="name"
        :multiple="false"
        accept="image/*"
        capture="environment"
        clearable
        v-if="progress === 0"
      >
        <template v-slot:before>
          <q-icon name="photo_camera" />
        </template>
      </q-file>

      <q-linear-progress
        rounded
        size="20px"
        :value="progress"
        v-else
      />
    </template>
  </mark-input-item>
</template>

<script lang="ts">
import {computed, defineComponent, PropType} from 'vue'
import {MarkFormFieldNumberConstraint, MarkFormFieldType} from 'src/models/form';
import MarkInputItem from 'components/Mark/InputItem.vue'
import {useI18n} from 'vue-i18n';
import RatingInput from 'components/Mark/RatingInput.vue';

enum FieldTypes {
  Rating,
  Float,
  Integer,
  Boolean,
  Date,
  String,
  Photo
}

export default defineComponent({
  name: 'MarkInput',
  components: {RatingInput, MarkInputItem},
  emits: [
    'reset:modelValue',
    'update:modelValue'
  ],
  props: {
    id: {
      type: Number,
      required: true
    },
    name: {
      type: String,
      required: true,
    },
    numberConstraints: {
      type: Object as PropType<MarkFormFieldNumberConstraint>,
    },
    fieldType: {
      type: String as PropType<MarkFormFieldType>,
      required: true
    },
    note: {
      type: String,
    },
    modelValue: {
      // do not type this, as it may be a simple number,
      // there are complaints on runtime that this is not an object
      // type: Object as PropType<MarkValueValue>,
    },
    progress: {
      type: Number,
      default: 0,
    },
  },

  setup(props, {emit}) {
    const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method

    const value = computed({
      get: () => props.modelValue,
      set: (val) => {
        if (null === val || '' === val) {
          emit('reset:modelValue')
        } else {
          emit('update:modelValue', val)
        }
      }
    })

    const steps = computed<number>(() => {
      if ( ! props.numberConstraints) {
        return 0;
      }

      const constraints = props.numberConstraints;

      return ((constraints.max - constraints.min) / constraints.step) + 1
    });

    function ratableSteps(steps: number) {
      if ( ! props.numberConstraints) {
        return false
      }

      if ( ! Number.isInteger(steps)) {
        return false
      }

      if (props.numberConstraints.min !== 0 && props.numberConstraints.min !== 1) {
        return false
      }

      if (props.numberConstraints.step !== 1) {
        return false
      }

      return steps >= 1 && steps <= 10
    }

    const fType = computed<FieldTypes>(() => {
      switch (props.fieldType) {
        case MarkFormFieldType.Boolean:
          return FieldTypes.Boolean
        case MarkFormFieldType.Date:
          return FieldTypes.Date
        case MarkFormFieldType.Float:
          return FieldTypes.Float
        case MarkFormFieldType.Integer:
          if (ratableSteps(steps.value)) {
            return FieldTypes.Rating
          } else {
            return FieldTypes.Integer
          }
        case MarkFormFieldType.Photo:
          return FieldTypes.Photo
        default:
          return FieldTypes.String
      }
    })

    function validNumber(value: number, constraints: MarkFormFieldNumberConstraint) {
      // valid if no value or no constraints
      if ( ! value || ! constraints) {
        return true
      }

      // invalid if outside of range
      if (value < constraints.min || value > constraints.max) {
        return false
      }

      // check if step is respected
      return (((value - constraints.min) / constraints.step) % 1.0) === 0
    }

    return {
      fType,
      FieldTypes,
      steps,
      value,
      t,
      validNumber,
    }
  }
})

</script>
