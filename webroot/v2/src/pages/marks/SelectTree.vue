<template>
  <h5 class="q-mb-lg q-mt-sm">{{ t('marks.selectTree.title') }}</h5>

  <div class="q-gutter-md">
    <ObjSelectorInputMethodChooser
      input-publicid-tree
      qr-scan-tree
      @change="setInputMethod"
    />

    <TreeSelector
      :method="inputMethod"
      @selected="setTree"
    />
  </div>
</template>

<script lang="ts" setup>
import {ref} from 'vue'
import {useI18n} from 'vue-i18n';
import {Tree} from 'src/models/tree';
import {useRouter} from 'vue-router'
import {useMarkStore} from 'stores/mark';
import ObjSelectorInputMethodChooser from 'components/Util/ObjSelectorInputMethodChooser.vue';
import TreeSelector from 'components/Tree/TreeSelector.vue';

const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
const store = useMarkStore()
const router = useRouter()

const inputMethod = ref<'CAMERA' | 'KEYBOARD'>('CAMERA')

function setTree(tree: Tree) {
  store.setTree(tree);
  void router.push('/marks/tree/mark-tree')
}

function setInputMethod(method: string) {
  if ('CAMERA' === method) inputMethod.value = 'CAMERA'
  else if ('KEYBOARD' === method) inputMethod.value = 'KEYBOARD'
  else throw new Error(`Invalid input method: ${method}`)
}

</script>
