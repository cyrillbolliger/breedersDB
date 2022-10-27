import {useQueryStore} from 'stores/query';
import {BaseTable} from 'src/models/query/query';

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

export default function useQueryLocalStorageHelper() {
  return {
    setVisibleColumns,
    getVisibleColumns,
    setBaseTable,
    getBaseTable,
  }
}
