<template>
  <MarkLayout>
    <q-page padding>

      <h5 class="q-mb-lg q-mt-sm">{{ t('marks.selectVariety.title') }}</h5>

      <div class="q-gutter-md">
        <ObjSelectorInputMethodChooser
          search-select-variety
          qr-scan-tree
          input-publicid-tree
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

    </q-page>
  </MarkLayout>
</template>

<script lang="ts">
import {defineComponent, ref} from 'vue'
import {useI18n} from 'vue-i18n';
import TreeSelector from 'components/Tree/TreeSelector.vue';
import {Tree} from 'src/models/tree';
import {useMarkStore} from 'stores/mark';
import MarkLayout from 'components/Mark/MarkLayout.vue';
import ObjSelectorInputMethodChooser from 'components/Util/ObjSelectorInputMethodChooser.vue';
import {useRouter} from 'vue-router';
import {Notify} from 'quasar';
import {Variety} from 'src/models/variety';
import VarietySelector from 'components/Variety/VarietySelector.vue';

export default defineComponent({
  name: 'SelectVariety',
  components: {VarietySelector, ObjSelectorInputMethodChooser, MarkLayout, TreeSelector},
  setup() {
    const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
    const store = useMarkStore()
    const router = useRouter()

    const inputMethod = ref<'SEARCH' | 'CAMERA' | 'KEYBOARD'>('SEARCH')

    function setVarietyFromTree(tree: Tree) {
      if (!tree.variety) {
        Notify.create({
            message: t('general.failedToLoadData'),
            color: 'negative',
            closeBtn: true
          })
        return
      }

      setVariety(tree.variety)
    }

    function setVariety(variety: Variety){
      store.setVariety(variety)
      void router.push('/marks/variety/mark-variety')
    }

    return {
      t,
      setVarietyFromTree,
      setVariety,
      inputMethod,
    }
  },
})
</script>
