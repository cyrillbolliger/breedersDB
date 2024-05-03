<template>
  <div class="row no-wrap">
    <q-linear-progress size="1.5em" :value="progress" color="grey-7" rounded>
      <div class="absolute-full flex flex-center">
        <q-badge color="white" text-color="black" :label="count" class="text-weight-bold" />
      </div>
    </q-linear-progress>
    <q-btn
      v-if="count >= total"
      flat
      color="primary"
      @click="$emit('reset')"
      size="sm"
      dense
      class="q-px-sm"
      style="white-space: nowrap"
    >{{t('marks.markCounter.reset')}}</q-btn>
  </div>
  <i18n-t :keypath="`marks.markCounter.${type}`" tag="small">
    <template #count><strong>{{ count }}</strong></template>
    <template #total>{{ total }}</template>
  </i18n-t>
</template>

<script setup lang="ts">
import {computed} from 'vue';
import {useI18n} from 'vue-i18n';
import useMarkType from 'src/composables/marks/type';

defineEmits<{
  (e: 'reset'): void;
}>();

export type MarkCounterProps = {
  count: number;
  total: number;
}

const props = defineProps<MarkCounterProps>();
const progress = computed(() => props.count / props.total );

const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
const type = useMarkType()
</script>
