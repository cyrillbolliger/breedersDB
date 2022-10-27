import {useQueryStore} from 'stores/query';

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


export default function useQueryLocalStorageHelper() {
  return {
    setVisibleColumns,
    getVisibleColumns,
  }
}
