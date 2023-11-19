<template>
  <MarkObj
    :mark-obj-select-label="t('marks.markVariety.selectVariety')"
    :title="t('marks.markVariety.title')"
    :variety="variety || undefined"
    form-select-url="/marks/variety/select-form"
    mark-obj-select-url="/marks/variety/select-variety"
    meta-set-url="/marks/variety/set-meta"
  >
    <template #obj-preview>
      <VarietyCard
        :variety="variety || undefined"
        @change="$router.push('/marks/variety/select-variety')"
      />
    </template>
  </MarkObj>
</template>

<script lang="ts" setup>
import {computed} from 'vue'
import {useI18n} from 'vue-i18n';
import {useMarkStore} from 'stores/mark';
import MarkObj from 'components/Mark/MarkObj.vue';
import VarietyCard from 'components/Variety/VarietyCard.vue';

const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
const store = useMarkStore()

const variety = computed(() => store.variety)
</script>
