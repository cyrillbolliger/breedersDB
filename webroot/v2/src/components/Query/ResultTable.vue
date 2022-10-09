<template>
  <q-table
    :columns="columns"
    :rows="rows"
    :loading="loading"
    row-key="name"
    virtual-scroll
    :virtual-scroll-item-size="48"
    :virtual-scroll-sticky-size-start="48"
    :pagination="{ rowsPerPage: 0 }"
    :rows-per-page-options="[0]"
    class="query-result-table"
  />
</template>

<script lang="ts" setup>
import {computed, PropType} from 'vue';
import {QueryResponse, QueryResponseSchemas, ViewEntity} from 'src/models/query/query';
import {useQueryStore} from 'stores/query';
import {PropertySchema, PropertySchemaOptionType} from 'src/models/query/filterOptionSchema';

const props = defineProps({
  result: {
    type: Object as PropType<QueryResponse>
  },
  loading: {
    type: Boolean,
    default: false,
  }
});

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
