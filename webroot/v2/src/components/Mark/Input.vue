<template>
  <mark-input-item
    v-if="fType === FieldTypes.Rating"
    :title="name"
    :note="note"
  >
    <q-rating
      :modelValue="ratingValue"
      @update:modelValue="$emit('update:modelValue', $event)"
      :size="'min(calc((100vw - 64px - '+((steps+1)*2)+'px) / '+(steps+1)+'), 3em)'"
      :max="steps + 1"
      color="primary"
      icon="star_border"
      icon-selected="star"
    />
  </mark-input-item>

  <mark-input-item
    v-if="fType === FieldTypes.Integer"
    :title="name"
    :note="note"
  >
    <!--suppress RequiredAttributes -->
    <q-input
      outlined
      v-model="value"
      :label="name"
      type="number"
      :min="numberConstraints.min"
      :max="numberConstraints.max"
      :step="numberConstraints.step"
    />
  </mark-input-item>

  <mark-input-item
    v-if="fType === FieldTypes.Float"
    :title="name"
    :note="note"
  >
    <!--suppress RequiredAttributes -->
    <q-input
      outlined
      v-model="value"
      :label="name"
      type="number"
      :min="numberConstraints.min"
      :max="numberConstraints.max"
      :step="numberConstraints.step"
    />
  </mark-input-item>

  <mark-input-item
    v-if="fType === FieldTypes.Boolean"
    :title="name"
    :note="note"
  >
    <!--suppress RequiredAttributes -->
    <q-toggle
      v-model="value"
      checked-icon="check"
      :label="name"
      unchecked-icon="clear"
    />
  </mark-input-item>

  <mark-input-item
    v-if="fType === FieldTypes.Date"
    :title="name"
    :note="note"
  >
    <!--suppress RequiredAttributes -->
    <q-input
      outlined
      v-model="value"
      :label="name"
      type="date"
    />
  </mark-input-item>

  <mark-input-item
    v-if="fType === FieldTypes.String"
    :title="name"
    :note="note"
  >
    <!--suppress RequiredAttributes -->
    <q-input
      outlined
      v-model="value"
      :label="name"
      type="text"
      autogrow
      hide-bottom-space
    />
  </mark-input-item>
</template>

<script lang="ts">
import {computed, defineComponent, PropType, ref, watch} from 'vue'
import {MarkFormFieldNumberConstraint, MarkFormFieldType, MarkValueValue} from 'src/models/form';
import MarkInputItem from 'components/Mark/InputItem.vue'
import {useI18n} from 'vue-i18n';

enum FieldTypes {
  Rating,
  Float,
  Integer,
  Boolean,
  Date,
  String
}

export default defineComponent({
  name: 'MarkInput',
  components: {MarkInputItem},
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
      type: [Number, String, Boolean, Date],
    }
  },

  setup(props, {emit}) {
    const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method

    const value = computed({
      get: () => props.modelValue,
      set: (val) => emit('update:modelValue', val)
    })

    const ratingValue = computed<number>(() => {
      if (typeof props.modelValue === 'number') {
        return props.modelValue
      }

      return 0
    })

    const steps = computed<number>(() => {
      if (!props.numberConstraints) {
        return 0;
      }

      const constraints = props.numberConstraints;

      return (constraints.max - constraints.min) / constraints.step
    });

    function ratableSteps(steps: number) {
      if (!props.numberConstraints) {
        return false
      }

      if (!Number.isInteger(steps)) {
        return false
      }

      if (props.numberConstraints.min !== 1) {
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
        default:
          return FieldTypes.String
      }
    })

    return {
      fType,
      FieldTypes,
      steps,
      value,
      ratingValue,
      t
    }
  }
})

</script>
