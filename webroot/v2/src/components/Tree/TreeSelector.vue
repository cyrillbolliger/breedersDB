<template>
  <!--suppress RequiredAttributes -->
  <q-input
    v-if="method === 'KEYBOARD'"
    v-model="publicid"
    :autofocus="true"
    :label="t('trees.publicid')"
    inputmode="numeric"
    outlined
    type="number"
    @keyup.enter="loadTree"
  />

  <div
    v-if="method === 'CAMERA'"
    :class="{loading}"
    class="row justify-center bg-grey-5"
  >
    <CodeScanner
      @on-detected="onScanned"
      @on-ready="onCodeScannerReady"
    />
  </div>

  <q-btn
    :disabled="!publicid"
    :label="t('general.next')"
    color="primary"
    @click="loadTree"
  />

  <SpinLoader v-if="loading"/>
</template>

<script lang="ts">
import {computed, defineComponent, onMounted, PropType, ref, watch} from 'vue'
import {useI18n} from 'vue-i18n';
import SpinLoader from 'components/Util/SpinLoader.vue';
import CodeScanner from 'components/Util/CodeScanner.vue';
import {Notify} from 'quasar';
import {useFetchTree} from 'src/composables/trees/fetchTree';

export default defineComponent({
  name: 'TreeSelector',
  emits: ['selected'],
  components: {CodeScanner, SpinLoader},
  props: {
    method: {
      type: String as PropType<'CAMERA' | 'KEYBOARD'>,
      required: true
    },
  },

  setup(props, {emit}) {
    const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
    const {fetchTreeByPublicId} = useFetchTree();
    const loadingTree = ref(false)

    const scannerLoading = ref(true)
    const publicid = ref('')

    const loading = computed(() => loadingTree.value || scannerLoading.value)

    watch(() => props.method,
      newMode => {
        scannerLoading.value = 'CAMERA' === newMode
      })

    onMounted(() => {
      if ('KEYBOARD' === props.method) {
        scannerLoading.value = false
      }
    })

    function loadTree() {
      const {data, loading} = fetchTreeByPublicId(
        publicid.value,
        () => loadingTree.value = false
      )

      loadingTree.value = loading.value

      void data.then(tree => {
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

    function onCodeScannerReady() {
      scannerLoading.value = false
    }

    return {
      t,
      publicid,
      loadTree,
      loading,
      onScanned,
      onCodeScannerReady,
    }
  }
})
</script>

<style lang="scss" scoped>
.loading {
  filter: grayscale(1) opacity(0.2) blur(4px);
  transition: all 200ms;
}
</style>
