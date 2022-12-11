<template>
  <q-table
    v-model:pagination="pagination"
    :class="{'query-result-table--fullscreen': fullscreen}"
    :columns="orderedColumns"
    :fullscreen="fullscreen"
    :loading="loading"
    :rows="rows"
    :rows-per-page-options="[10,100,1000]"
    :title="t('queries.results')"
    :virtual-scroll-item-size="48"
    :virtual-scroll-sticky-size-start="48"
    :visible-columns="visibleColumns"
    :wrap-cells="true"
    binary-state-sort
    class="query-result-table"
    color="primary"
    row-key="name"
    virtual-scroll
    @request="event => $emit('requestData', event)"
  >
    <template #top-right>
      <q-toggle
        v-if="hasVisibleMarkColumns"
        v-model="showRowsWithoutMarks"
        :label="t('queries.showRowsWithoutMarks')"
        dense
      />

      <ResultTableColumnSelector
        :columns="columns"
        class="q-ml-lg"
      />

      <q-btn
        :icon="fullscreen ? 'fullscreen_exit' : 'fullscreen'" class="q-ml-md" dense
        flat
        round
        @click="fullscreen = !fullscreen"
      />
    </template>

    <template #header-cell="props">
      <ResultTableHeaderCell
        :cellProps="props"
        @colDropped="reorderColumns"
        @hideColumn="hideColumn"
      />
    </template>

    <template #body-cell="props">
      <ResultTableCell
        :cellProps="props"
      />
    </template>
  </q-table>
</template>

<script lang="ts" setup>
import {computed, onMounted, PropType, ref, watch} from 'vue';
import {QueryResponse, QueryResponseSchemas, ViewEntity} from 'src/models/query/query';
import {useQueryStore} from 'stores/query';
import {QTable, QTableColumn} from 'quasar';
import {useI18n} from 'vue-i18n';
import ResultTableColumnSelector from 'components/Query/Result/ResultTableColumnSelector.vue';
import ResultTableCell from 'components/Query/Result/ResultTableCell.vue';
import ResultTableHeaderCell from 'components/Query/Result/ResultTableHeaderCell.vue';
import useResultColumnConverter from 'src/composables/queries/resultTableColumnConverter';

defineEmits<{
  (e: 'requestData', data: Parameters<QTable['onRequest']>[0]): void
}>();

const props = defineProps({
  result: {
    type: Object as PropType<QueryResponse>
  },
  loading: {
    type: Boolean,
    default: false,
  }
});

const ROWS_PER_PAGE = 100;

const {t} = useI18n(); // eslint-disable-line @typescript-eslint/unbound-method
const store = useQueryStore();
const columnConverter = useResultColumnConverter();

const fullscreen = ref(false);
const columnOrder = ref<string[]>([]);

const visibleColumns = computed(() => store.getVisibleColumns)
const hasVisibleMarkColumns = computed(() => store.hasVisibleMarkColumns);

const showRowsWithoutMarks = computed<boolean>({
  get: () => store.showRowsWithoutMarks,
  set: (show: boolean) => store.setShowRowsWithoutMarks(show),
});

function hideColumn(name: string) {
  store.setVisibleColumns(
    store.getVisibleColumns.filter(column => column !== name)
  );
}

const baseTableName = computed(() => {
  return `${store.baseTable}View`;
});

const schemas = computed<QueryResponseSchemas>(() => {
  // noinspection TypeScriptUnresolvedVariable
  return props.result?.schema || [];
})

const rowData = computed<ViewEntity[]>(() => {
  // noinspection TypeScriptUnresolvedVariable
  return props.result?.results || [];
})


function reorderColumns(targetColName: string, moveColName: string, pos: 'before' | 'after') {
  // noinspection TypeScriptValidateTypes
  let moveColIdx = columnOrder.value.indexOf(moveColName);
  // noinspection TypeScriptValidateTypes
  let targetColIdx = columnOrder.value.indexOf(targetColName);

  if (0 === columnOrder.value.length || -1 === moveColIdx || -1 === targetColIdx) {
    columnOrder.value = columns.value.map(col => col.name);
    // noinspection TypeScriptValidateTypes
    moveColIdx = columnOrder.value.indexOf(moveColName);
  }

  // remove moveCol
  columnOrder.value.splice(moveColIdx, 1);

  // noinspection TypeScriptValidateTypes
  targetColIdx = columnOrder.value.indexOf(targetColName);

  if ('after' === pos) {
    targetColIdx++;
  }

  columnOrder.value.splice(targetColIdx, 0, moveColName);
}

const orderedColumns = computed(() => {
  if (0 === columnOrder.value.length) {
    return columns.value;
  }

  return columns.value.slice().sort((a, b) => {
    // noinspection TypeScriptValidateTypes
    return columnOrder.value.indexOf(a.name) - columnOrder.value.indexOf(b.name);
  });
});

const columns = computed<QTableColumn[]>(() => {
  const schema = schemas.value[baseTableName.value];

  if ( ! schema) {
    return [];
  }

  const columns = columnConverter.schemaToColumn(schema);

  if (store.marksAvailable) {
    const markColumns = columnConverter.markFormPropertiesToColumn(
      store.markFormProperties,
      t
    );
    columns.push(...markColumns);
  }

  return columns;
});

const rows = computed(() => {
  return rowData.value?.map((item: ViewEntity) => {
    // add base table prefix to all non nested columns
    const data = {};
    for (const key of Object.keys(item)) {
      const value = item[key];
      const newKey = Array.isArray(value) ? key : `${baseTableName.value}.${key}`;
      data[newKey] = value;
    }
    return data;
  });
});

const totalRowsDB = computed<number>(() => {
  // noinspection TypeScriptUnresolvedVariable
  return props.result?.count || 0;
});

const offset = computed<number>(() => {
  // noinspection TypeScriptUnresolvedVariable
  return props.result?.offset || 0;
})

const page = computed<number>(() => {
  return 1 + (offset.value / rowsPerPage.value)
})

const sortBy = computed<string>(() => {
  // noinspection TypeScriptUnresolvedVariable
  return props.result?.sortBy || '';
});

const descending = computed<boolean>(() => {
  // noinspection TypeScriptUnresolvedVariable
  return props.result?.order === 'desc';
});

const rowsPerPage = computed<number>(() => {
  // noinspection TypeScriptUnresolvedVariable
  return props.result?.limit || ROWS_PER_PAGE;
});

const pagination = ref({
  sortBy: sortBy.value,
  descending: descending.value,
  page: page.value,
  rowsPerPage: rowsPerPage.value,
  rowsNumber: totalRowsDB.value
})


watch(totalRowsDB, count => pagination.value.rowsNumber = count);
watch(page, num => pagination.value.page = num);
watch(sortBy, col => pagination.value.sortBy = col);
watch(descending, order => pagination.value.descending = order);
watch(rowsPerPage, limit => pagination.value.rowsPerPage = limit);

onMounted(() => {
  void store.maybeLoadMarkFormProperties();
});
</script>

<style>
/* Do not scope this tag, else we loose the
   sticky definitions for the header and footer */

.query-result-table {
  height: calc(100vh - 100px);
}

.query-result-table--fullscreen {
  height: 100vh;
}

/*noinspection CssUnusedSymbol*/
.query-result-table thead tr:first-child th {
  /* bg color is important for th; just specify one */
  background-color: #ffffff;
}

/*noinspection CssUnusedSymbol*/
.query-result-table .q-table__bottom,
.query-result-table .q-table__top {
  background-color: #efefef;
}

.query-result-table thead tr th {
  position: sticky;
  z-index: 1;
  top: 0;
}

/* this is when the loading indicator appears */
.query-result-table.q-table--loading thead tr:last-child th {
  /* height of all previous header rows */
  top: 48px
}

</style>
