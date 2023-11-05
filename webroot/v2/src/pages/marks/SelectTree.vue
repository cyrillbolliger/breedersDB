<template>
  <MarkLayout>
    <q-page padding>

      <h5 class="q-mb-lg q-mt-sm">{{ t('marks.selectTree.title') }}</h5>

      <div class="q-gutter-md">
        <ObjSelectorInputMethodChooser
          qr-scan-tree
          input-publicid-tree
          @change="setInputMethod"
        />

        <TreeSelector
          :method="inputMethod"
          @selected="setTree"
        />
      </div>

    </q-page>
  </MarkLayout>
</template>

<script lang="ts">
import {defineComponent, ref} from 'vue'
import {useI18n} from 'vue-i18n';
import TreeSelector from 'components/Tree/TreeSelector.vue';
import {Tree} from 'src/models/tree';
import {useRouter} from 'vue-router'
import {useMarkStore} from 'stores/mark';
import MarkLayout from 'components/Mark/MarkLayout.vue';
import ObjSelectorInputMethodChooser from 'components/Util/ObjSelectorInputMethodChooser.vue';

export default defineComponent({
  name: 'SelectTree',
  components: {ObjSelectorInputMethodChooser, MarkLayout, TreeSelector},
  setup() {
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

    return {
      t,
      setTree,
      setInputMethod,
      inputMethod
    }
  },
})
</script>
