<template>
  <div class="row items-start">
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
    <div class="column">
      <q-rating
          :modelValue="ratingValue"
          @update:modelValue="ratingChanged"
          :size="'min(calc((100vw - 64px - '+(steps*2)+'px) / '+steps+'), 3em)'"
          :max="max"
          color="primary"
          icon="star_border"
          icon-selected="star"
      />
      <div v-if="legendValues" class="row justify-between">
        <div
          v-for="(item, index) in legendValues"
          :style="`width: ${100/legendValues.length}%`"
          :key="index"
        >
          <small
            class="legend"
          >{{item}}</small>
        </div>
      </div>
    </div>
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
    },
    legend: {
      type: String,
    }
  },

  setup(props, {emit}) {
    const ratingValue = computed<number>(() => {
      if (props.modelValue === undefined) {
        return 0
      }

      return props.modelValue
    })

    const legendValues = computed<string[]>( () => {
      return props.legend ? props.legend.split(';').map(v => v.trim()) : []
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
      zeroRatingClicked,
      legendValues
    }
  },
})
</script>

<style scoped>
  .legend {
    writing-mode: tb;
    white-space: nowrap;
    margin: 0.5em auto 0;
    display: block;
    font-weight: bolder;
  }
</style>
