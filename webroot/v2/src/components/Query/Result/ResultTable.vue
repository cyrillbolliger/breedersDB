<template>
  <q-table
    v-model:pagination="pagination"
    :class="{'query-result-table--fullscreen': fullscreen}"
    :columns="columns"
    :fullscreen="fullscreen"
    :loading="loading"
    :rows="rows"
    :rows-per-page-options="[10,100,1000]"
    :title="t('queries.results')"
    :virtual-scroll-item-size="48"
    :virtual-scroll-sticky-size-start="48"
    :visible-columns="visibleColumns"
    binary-state-sort
    class="query-result-table"
    color="primary"
    row-key="name"
    virtual-scroll
    @request="event => $emit('requestData', event)"
  >
    <template #top-right>
      <ResultTableColumnSelector
        :columns="columns"
        v-model="visibleColumns"
      />

      <q-btn
        :icon="fullscreen ? 'fullscreen_exit' : 'fullscreen'" class="q-ml-md" dense
        flat
        round
        @click="fullscreen = !fullscreen"
      />
    </template>

    <template #header-cell="props">
      <q-th :props="props">
        {{ props.col.label }}
        <q-btn
          dense
          flat
          icon="close"
          round
          size="xs"
          @click.stop="hideColumn(props.col.name)"
        />
      </q-th>
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
import {MarkCell, QueryResponse, QueryResponseSchemas, ViewEntity} from 'src/models/query/query';
import {useQueryStore} from 'stores/query';
import {PropertySchema, PropertySchemaOptionType} from 'src/models/query/filterOptionSchema';
import {QTable, QTableColumn} from 'quasar';
import {useI18n} from 'vue-i18n';
import ResultTableColumnSelector from 'components/Query/Result/ResultTableColumnSelector.vue';
import {MarkFormProperty} from 'src/models/form';
import ResultTableCell from 'components/Query/Result/ResultTableCell.vue';

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

const fullscreen = ref(false);
const visibleColumns = ref<string[]>([]);

function hideColumn(name: string) {
  visibleColumns.value = visibleColumns.value.filter(column => column !== name);
}

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

function getMarkData(row: ViewEntity, property: MarkFormProperty) {
  if (! ('marks_view' in row)) {
    return [];
  }

  const marks: MarkCell[] = (row.marks_view as ViewEntity[])
    .filter((mark: ViewEntity) => mark.property_id === property.id)
    .map((mark: ViewEntity) => {
      const entity = Object.assign({}, row);
      delete entity.marks_view;
      delete entity.trees_view;

      const cell = mark as MarkCell;
      cell.entity = entity as MarkCell['entity'];
      return cell;
    });

  if ('trees_view' in row && Array.isArray(row.trees_view)) {
    for (const tree of row.trees_view) {
      marks.push(...getMarkData(tree, property))
    }
  }

  return marks;
}

const columns = computed<QTableColumn[]>(() => {
  const schema = schemas.value[baseTableName.value];

  if ( ! schema) {
    return [];
  }

  const columns = schema.map((item: PropertySchema) => {
    const isNum = item.options.type === PropertySchemaOptionType.Integer
      || item.options.type === PropertySchemaOptionType.Float;

    return {
      name: item.name,
      label: item.label,
      field: item.name,
      align: isNum ? 'right' : 'left',
      sortable: true,
      format: (val: string | number | Date | null | undefined) => formatColumnValue(val || null, item.options.type)
    } as QTableColumn;
  });

  if (store.marksAvailable) {
    const namePrefix = t('queries.Marks') + ' > ';
    const markColumns = store.markFormProperties.map((item: MarkFormProperty) => {
      return {
        name: `Mark.${item.id}`,
        label: namePrefix + item.name,
        field: (row: ViewEntity) => getMarkData(row, item),
        align: 'center',
        sortable: false,
      } as QTableColumn;
    });

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
