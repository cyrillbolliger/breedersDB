<template>
  <div class="row">
    <q-btn
      v-if="withZero"
      color="primary"
      :style="'opacity: ' + (modelValue === 0 ? 1 : 0.4)"
      icon="exposure_zero"
      outline
      :size="'min(calc((100vw - 64px - '+(steps*2)+'px) / '+1.75*steps+'), 1.714em)'"
      flat
      dense
      class="q-pa-none"
      @click="zeroRatingClicked"
    />
    <q-rating
        :modelValue="ratingValue"
        @update:modelValue="ratingChanged"
        :size="'min(calc((100vw - 64px - '+(steps*2)+'px) / '+steps+'), 3em)'"
        :max="max"
        color="primary"
        icon="star_border"
        icon-selected="star"
    />
  </div>
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
    },
    withZero: {
      type: Boolean,
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

    function zeroRatingClicked() {
      if (props.modelValue === 0) {
        emit('update:modelValue', null)
      } else {
        emit('update:modelValue', 0)
      }
    }

    const max = computed(() => props.withZero ? props.steps - 1 : props.steps);

    return {
      ratingValue,
      ratingChanged,
      max,
      zeroRatingClicked
    }
  },
})
</script>
