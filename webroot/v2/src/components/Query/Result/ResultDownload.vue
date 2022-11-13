<template>
  <q-btn
    :disable="!enabled"
    :label="t('queries.download')"
    class="q-my-md"
    color="primary"
    icon="download"
    @click="download"
  />

  <div
    v-if="loading"
    class="result-download__overlay"
  >
    <SpinLoader/>
  </div>
</template>

<script lang="ts" setup>
import {useI18n} from 'vue-i18n';
import {computed, ref} from 'vue';
import SpinLoader from 'components/Util/SpinLoader.vue';
import {useQueryStore} from 'stores/query';
import useApi from 'src/composables/api';

const {t} = useI18n(); // eslint-disable-line @typescript-eslint/unbound-method
const queryStore = useQueryStore();

defineProps({
  enabled: {
    type: Boolean,
    default: true,
  }
});

const loading = ref(false);

const filename = computed<string>(() => {
  // todo: use query name
  return 'export.xlsx';
})

async function download() {
  loading.value = true;

  const data = {
    baseTable: queryStore.baseTable,
    baseFilter: queryStore.baseFilter,
    markFilter: queryStore.marksAvailable ? queryStore.markFilter : null,
    columns: queryStore.getVisibleColumns,
  }

  const resp = await useApi().post<typeof data, { data: Blob }>(
    'queries/download',
    data,
    () => loading.value = false,
    {responseType: 'blob', timeout: 60000}
  );

  triggerDownload(resp as { data: Blob });
}

function triggerDownload(resp: { data: Blob }) {
  const url = URL.createObjectURL(new Blob([resp.data], {type: 'application/vnd.openxmlformatsofficedocument.spreadsheetml.sheet'}));
  const link = document.createElement('a');
  link.href = url;
  link.setAttribute('download', filename.value);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

</script>

<style scoped>
.result-download__overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(255, 255, 255, 0.8);
  z-index: 10;
}
</style>
