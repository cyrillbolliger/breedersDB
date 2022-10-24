import {defineStore} from 'pinia';
import {BaseTable, FilterDragNode} from 'src/models/query/query';
import {FilterNode} from 'src/models/query/filterNode';
import {FilterOperand, FilterType} from 'src/models/query/filterTypes';
import {MarkFormProperty} from 'src/models/form';
import useApi from 'src/composables/api';

export interface QueryState {
  baseTable: BaseTable,
  baseFilter: FilterNode,
  markFilter: FilterNode,
  filterDragNode: FilterDragNode,
  markFormProperties: MarkFormProperty[],
}

export const useQueryStore = defineStore('query', {
  state: (): QueryState => ({
    baseTable: BaseTable.Varieties,
    baseFilter: FilterNode.FilterRoot(FilterOperand.And, FilterType.Base),
    markFilter: FilterNode.FilterRoot(FilterOperand.And, FilterType.Mark),
    filterDragNode: false,
    markFormProperties: [],
  }),


  getters: {
    marksAvailable(state) {
      const s = state as QueryState;
      return s.baseTable === BaseTable.Batches
        || s.baseTable === BaseTable.Varieties
        || s.baseTable === BaseTable.Trees
    },

    async getMarkFormProperties(state) {
      const s = state as QueryState;
      if (! s.markFormProperties.length) {
        await useApi().get<MarkFormProperty[]>('mark-form-properties')
          .then(data => s.markFormProperties = data as MarkFormProperty[]);
      }
      return s.markFormProperties;
    },
  },


  actions: {},
});
