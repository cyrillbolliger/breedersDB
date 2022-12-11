import {MarkQuery, Query} from 'src/models/query/query';
import useApi from 'src/composables/api';
import {useQueryStore} from 'stores/query';
import {FilterNode} from 'src/models/query/filterNode';
import {QueryGroup} from 'src/models/queryGroup';

const api = useApi();
const store = useQueryStore();

async function saveQuery(id: string): Promise<void | Query> {
  const rawQuery = {
    baseTable: store.baseTable,
    baseFilter: store.baseFilter,
    visibleColumns: store.visibleColumns,
    showRowsWithoutMarks: store.showRowsWithoutMarks,
  } as MarkQuery;

  if (store.marksAvailable) {
    rawQuery.markFilter = store.markFilter as FilterNode;
  }

  const data = {
    code: store.queryCode,
    description: store.queryDescription,
    query_group_id: (store.queryGroup as QueryGroup|null)?.id || -1,
    raw_query: rawQuery,
  } as Query;

  if ('new' === id) {
    return api.post<Query, Query>('/queries/add', data, () => null, {}, false);
  }

  data.id = parseInt(id);
  return api.patch<Query, Query>(`/queries/edit/${id}`, data, () => null, {}, false);
}

async function deleteQuery(id: string) {
  if ('new' === id) {
    return;
  }

  return api.delete<void>(`/queries/delete/${id}`);
}

export default function useQueryMenuActions() {
  return {
    saveQuery,
    deleteQuery,
  }
}
