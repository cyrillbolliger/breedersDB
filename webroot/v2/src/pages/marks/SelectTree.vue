<template>
  <q-page padding>

    <h5 class="q-mb-lg q-mt-sm">{{ t('marks.selectTree.title') }}</h5>

    <tree-selector
      @selected="setTree"
    />

  </q-page>
</template>

<script lang="ts">
import {defineComponent, ref} from 'vue'
import useLayout from 'src/composables/layout';
import useMarkTabNav from 'src/composables/marks/tab-nav';
import {useI18n} from 'vue-i18n';
import TreeSelector from 'components/Tree/TreeSelector.vue';
import {useStore} from 'src/store';

export default defineComponent({
  name: 'SelectTree',
  components: {TreeSelector},
  setup() {
    const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
    const store = useStore()

    const publicid = ref(null)

    const {setToolbarTabs, setToolbarTitle} = useLayout()
    setToolbarTabs(useMarkTabNav())
    setToolbarTitle(t('marks.title'))


    function setTree(tree) {
      store.dispatch('mark/tree', tree)
    }

    return {
      t,
      publicid,
      setTree
    }
  },
})
</script>
