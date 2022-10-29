import {useQueryStore} from 'stores/query';
import {BaseTable} from 'src/models/query/query';
import {FilterNode} from 'src/models/query/filterNode';

function setVisibleColumns(visibleColumns: string[]) {
  window.localStorage.setItem(
    getVisibleColumnsKey(),
    JSON.stringify(visibleColumns)
  );
}

function getVisibleColumns(defaultValue: string[] = []): string[] {
  const encoded = window.localStorage.getItem(
    getVisibleColumnsKey()
  );

  return encoded
    ? JSON.parse(encoded) as string[]
    : defaultValue;
}

function getVisibleColumnsKey() {
  const baseTableName = useQueryStore().baseTable;
  return `breedersdb_query_visible_columns--${baseTableName}`;
}

function setBaseTable(baseTableName: BaseTable) {
  window.localStorage.setItem(
    getBaseTableKey(),
    baseTableName
  );
}

function getBaseTable(defaultValue: BaseTable): BaseTable {
  return window.localStorage.getItem(getBaseTableKey()) as BaseTable
    || defaultValue;
}

function getBaseTableKey() {
  return 'breedersdb_query_base_table';
}

function setBaseFilter(filter: FilterNode) {
  window.localStorage.setItem(
    getBaseFilterKey(),
    JSON.stringify(filter)
  );
}

function getBaseFilter(defaultValue: FilterNode): FilterNode {
  const encoded = window.localStorage.getItem(
    getBaseFilterKey()
  );

  return encoded
    ? FilterNode.FromJSON(encoded)
    : defaultValue;
}

function getBaseFilterKey() {
  const baseTableName = useQueryStore().baseTable;
  return `breedersdb_query_base_filter--${baseTableName}`;
}

function setMarkFilter(filter: FilterNode) {
  window.localStorage.setItem(
    getMarkFilterKey(),
    JSON.stringify(filter)
  );
}

function getMarkFilter(defaultValue: FilterNode): FilterNode {
  const encoded = window.localStorage.getItem(
    getMarkFilterKey()
  );

  return encoded
    ? FilterNode.FromJSON(encoded)
    : defaultValue;
}

function getMarkFilterKey() {
  const baseTableName = useQueryStore().baseTable;
  return `breedersdb_query_mark_filter--${baseTableName}`;
}

export default function useQueryLocalStorageHelper() {
  return {
    setVisibleColumns,
    getVisibleColumns,
    setBaseTable,
    getBaseTable,
    setBaseFilter,
    getBaseFilter,
    setMarkFilter,
    getMarkFilter,
  }
}
