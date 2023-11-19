<template>
  <MarkObj
    :mark-obj-select-label="t('marks.markTree.selectTree')"
    :title="t('marks.markTree.title')"
    :tree="tree || undefined"
    form-select-url="/marks/tree/select-form"
    mark-obj-select-url="/marks/tree/select-tree"
    meta-set-url="/marks/tree/set-meta"
  >
    <template #obj-preview>
      <tree-card
        :tree="tree || undefined"
        @change="$router.push('/marks/tree/select-tree')"
      />
    </template>
  </MarkObj>
</template>

<script lang="ts" setup>
import {computed} from 'vue'
import {useI18n} from 'vue-i18n';
import {useMarkStore} from 'stores/mark';
import MarkObj from 'components/Mark/MarkObj.vue';
import TreeCard from 'components/Tree/TreeCard.vue';

const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
const store = useMarkStore()

const tree = computed(() => store.tree)
</script>
