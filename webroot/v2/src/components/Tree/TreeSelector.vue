<template>
  <div class="q-gutter-md">

    <!--suppress RequiredAttributes -->
    <q-input
      outlined
      v-model="publicid"
      :label="t('trees.publicid')"
      @keyup.enter="loadTree"
      type="text"
    />

    <div
      class="q-mb-md row justify-center bg-grey-5"
      :class="{loading}"
    >
      <CodeScanner
        @on-detected="onScanned"
      />
    </div>

    <q-btn
      color="primary"
      :label="t('general.next')"
      :disabled="!publicid"
      @click="loadTree"
    />

    <Loader v-if="loading"/>
  </div>
</template>

<script lang="ts">
import {defineComponent, ref} from 'vue'
import {useI18n} from 'vue-i18n';
import Loader from 'components/Util/Loader.vue';
import {Tree} from 'src/models/tree';
import useApi from 'src/composables/api'
import CodeScanner from 'components/Util/CodeScanner.vue';
import {Notify} from 'quasar';

export default defineComponent({
  name: 'TreeSelector',
  emits: ['selected'],
  components: {CodeScanner, Loader},

  setup(_, {emit}) {
    const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
    const {working, get} = useApi()

    const publicid = ref('')

    function loadTree() {
      const params = new URLSearchParams()
      params.append('fields[]', 'publicid')
      params.append('term', publicid.value)
      const url = 'trees/get-tree?' + params.toString()

      void get<Tree>(url)
        .then(tree => {
          if (tree) {
            emit('selected', tree)
          } else {
            Notify.create({
              message: t('general.failedToLoadData'),
              color: 'negative',
              closeBtn: true
            })
          }
        })
    }

    function onScanned(code: string) {
      publicid.value = code
      loadTree()
    }

    return {
      t,
      publicid,
      loadTree,
      loading: working,
      onScanned,
    }
  }
})
</script>

<style scoped lang="scss">
  .loading {
    filter: grayscale(1) opacity(0.2) blur(4px);
    transition: all 200ms;
  }
</style>
