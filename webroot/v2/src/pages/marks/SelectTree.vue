<template>
  <MarkLayout>
    <q-page padding>

      <h5 class="q-mb-lg q-mt-sm">{{ t('marks.selectTree.title') }}</h5>

      <tree-selector
        @selected="setTree"
      />

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

export default defineComponent({
  name: 'SelectTree',
  components: {MarkLayout, TreeSelector},
  setup() {
    const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
    const store = useMarkStore()
    const router = useRouter()

    const publicid = ref(null)

    function setTree(tree: Tree) {
      store.tree = tree;
      void router.push('/marks/tree/mark-tree')
    }

    return {
      t,
      publicid,
      setTree
    }
  },
})
</script>
