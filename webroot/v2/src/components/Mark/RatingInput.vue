<template>
  <q-rating
      :modelValue="ratingValue"
      @update:modelValue="ratingChanged"
      :size="'min(calc((100vw - 64px - '+(steps*2)+'px) / '+steps+'), 3em)'"
      :max="steps"
      color="primary"
      icon="star_border"
      icon-selected="star"
  />
</template>
<script lang="ts">
import {computed, defineComponent} from 'vue';

export default defineComponent({
  name: 'RatingInput',
  props: {
    modelValue: {
      type: Number,
    },
    steps: {
      type: Number,
      required: true
    }
  },

  setup(props, {emit}) {
    const ratingValue = computed<number>(() => {
      if (props.modelValue === undefined) {
        return 0
      }

      return props.modelValue
    })

    function ratingChanged(val: number) {
      if (0 === val) {
        emit('update:modelValue', null)
      } else {
        emit('update:modelValue', val)
      }
    }

    return {
      ratingValue,
      ratingChanged,
    }
  },
})
</script>
