<template>
  <q-table
    ref="tableRef"
    v-model:pagination="pagination"
    :columns="columns"
    :loading="loading"
    :rows="rows"
    :rows-per-page-options="[]"
    class="query-result-table"
    row-key="name"
    @request="event => $emit('requestData', event)"
  />
</template>

<script lang="ts" setup>
import {computed, onMounted, PropType, ref, watch} from 'vue';
import {QueryResponse, QueryResponseSchemas, ViewEntity} from 'src/models/query/query';
import {useQueryStore} from 'stores/query';
import {PropertySchema, PropertySchemaOptionType} from 'src/models/query/filterOptionSchema';
import {QTable} from 'quasar';

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

const store = useQueryStore();

function formatColumnValue(val: string | number | Date | null, type: PropertySchemaOptionType) {
  if (null === val) {
    return '';
  } else if (val instanceof Date) {
    return val.toLocaleDateString();
  } else if (typeof (val) === 'number') {
    return val.toLocaleString();
  } else if (type === PropertySchemaOptionType.Integer) {
    return parseInt(val).toLocaleString();
  } else if (type === PropertySchemaOptionType.Float) {
    return parseFloat(val).toLocaleString();
  } else if (type === PropertySchemaOptionType.Date) {
    return new Date(val).toLocaleDateString();
  }

  return val;
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

const columns = computed(() => {
  const schema = schemas.value[baseTableName.value];

  if ( ! schema) {
    console.error(`Schema not found: ${baseTableName.value}`);
    return [];
  }

  return schema.map((item: PropertySchema) => {
    const isNum = item.options.type === PropertySchemaOptionType.Integer
      || item.options.type === PropertySchemaOptionType.Float;

    return {
      name: item.name,
      label: item.label,
      field: item.name,
      align: isNum ? 'right' : 'left',
      sortable: true,
      format: (val: string | number | Date | null | undefined) => formatColumnValue(val || null, item.options.type)
    }
  });
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
  return 1 + (offset.value / ROWS_PER_PAGE)
})

const sortBy = computed<string>(() => {
  // noinspection TypeScriptUnresolvedVariable
  return props.result?.sortBy || '';
});

const descending = computed<boolean>(() => {
  // noinspection TypeScriptUnresolvedVariable
  return props.result?.order === 'desc';
});

const tableRef = ref<QTable | undefined>()
const pagination = ref({
  sortBy: sortBy.value,
  descending: descending.value,
  page: page.value,
  rowsPerPage: ROWS_PER_PAGE,
  rowsNumber: totalRowsDB.value
})

onMounted(() => {
  // get initial data from server (1st page)
  // noinspection TypeScriptUnresolvedFunction
  // tableRef.value.requestServerInteraction()
})

watch(totalRowsDB, count => pagination.value.rowsNumber = count);
watch(page, num => pagination.value.page = num);
watch(sortBy, col => pagination.value.sortBy = col);
watch(descending, order => pagination.value.descending = order);

</script>

<style>
/* Do not scope this tag, else we loose the
   sticky definitions for the header and footer */

.query-result-table {
  height: calc(100vh - 100px);
}

.query-result-table .q-table__top,
.query-result-table .q-table__bottom,
.query-result-table thead tr:first-child th {
  /* bg color is important for th; just specify one */
  background-color: #ffffff;
}

.query-result-table thead tr th {
  position: sticky;
  z-index: 1;
}

.query-result-table thead tr:first-child th {
  top: 0;
}

/* this is when the loading indicator appears */
.query-result-table.q-table--loading thead tr:last-child th {
  /* height of all previous header rows */
  top: 48px
}

</style>
