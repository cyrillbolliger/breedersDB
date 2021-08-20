<template>
  <div class="q-gutter-md">

    <div
      class="q-mb-md"
      style="background-color: #ccc; height: 200px; padding: 1em"
    >
      Placeholder for quagga scanner
    </div>

    <q-input
      outlined
      v-model="publicid"
      :label="t('trees.publicid')"
      @keyup.enter="loadTree"
      type="text"
    />

    <q-btn
      color="primary"
      :label="t('general.next')"
      v-if="publicid"
      @click="loadTree"
    />

    <Loader v-if="loading"/>
  </div>
</template>

<script lang="ts">
import {defineComponent, ref} from 'vue'
import {useI18n} from 'vue-i18n';
import Loader from 'components/Util/Loader.vue';
import {Tree} from 'components/models';
import useApi from 'src/composables/api'

export default defineComponent({
  name: 'TreeSelector',
  emits: ['selected'],
  components: {Loader},

  setup(_, {emit}) {
    const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
    const {working, get} = useApi()

    const publicid = ref('')

    function loadTree() {
      const params = new URLSearchParams()
      params.append('fields[]', 'publicid')
      params.append('term', publicid.value)
      const url = 'trees/get-tree?' + params.toString()

      get(url)
        .then((tree: Tree) => {
          if (tree) {
            emit('selected', tree)
          }
        })
    }

    return {
      t,
      publicid,
      loadTree,
      loading: working,
    }
  }
})
</script>
