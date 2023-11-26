<template>
  <h5 class="q-mb-lg q-mt-sm">{{ t('marks.selectVariety.title') }}</h5>

  <div class="q-gutter-md">
    <ObjSelectorInputMethodChooser
      input-publicid-tree
      qr-scan-tree
      search-select-variety
      @change="value => inputMethod = value"
    />

    <TreeSelector
      v-if="inputMethod === 'CAMERA' || inputMethod === 'KEYBOARD'"
      :method="inputMethod"
      @selected="setVarietyFromTree"
    />

    <VarietySelector
      v-else
      @selected="setVariety"
    />
  </div>
</template>

<script lang="ts" setup>
import {ref} from 'vue'
import {useI18n} from 'vue-i18n';
import TreeSelector from 'components/Tree/TreeSelector.vue';
import {Tree} from 'src/models/tree';
import {useMarkStore} from 'stores/mark';
import ObjSelectorInputMethodChooser from 'components/Util/ObjSelectorInputMethodChooser.vue';
import {useRouter} from 'vue-router';
import {Notify} from 'quasar';
import {Variety} from 'src/models/variety';
import VarietySelector from 'components/Variety/VarietySelector.vue';

const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
const store = useMarkStore()
const router = useRouter()

const inputMethod = ref<'SEARCH' | 'CAMERA' | 'KEYBOARD'>('SEARCH')

function setVarietyFromTree(tree: Tree) {
  setVariety(tree.variety)
}

function setVariety(variety: Variety | undefined | null) {
  if ( ! variety) {
    Notify.create({
      message: t('general.failedToLoadData'),
      color: 'negative',
      closeBtn: true
    })
    return
  }

  store.setVariety(variety)
  void router.push('/marks/variety/mark-variety')
}
</script>
