<template>
  <MarkObj
    :mark-obj-select-label="t('marks.markBatch.selectBatch')"
    :title="t('marks.markBatch.title')"
    :batch="batch || undefined"
    form-select-url="/marks/batch/select-form"
    mark-obj-select-url="/marks/batch/select-batch"
    meta-set-url="/marks/batch/set-meta"
    @saved="refreshKey++"
    :key="refreshKey"
  >
    <template #obj-preview>
      <BatchCard
        :batch="batch || undefined"
        @change="$router.push('/marks/batch/select-batch')"
      />
    </template>
  </MarkObj>
</template>

<script lang="ts" setup>
import {computed, ref} from 'vue'
import {useI18n} from 'vue-i18n';
import {useMarkStore} from 'stores/mark';
import MarkObj from 'components/Mark/MarkObj.vue';
import BatchCard from 'components/Batch/BatchCard.vue';

const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
const store = useMarkStore()

const batch = computed(() => store.batch)
const refreshKey = ref(0)
</script>
