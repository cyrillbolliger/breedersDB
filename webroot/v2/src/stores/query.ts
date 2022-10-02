import {defineStore} from 'pinia';
import {BaseTable, FilterDragNode} from 'src/models/query/query';
import {FilterNode} from 'src/models/query/filterNode';
import {FilterOperand, FilterType} from 'src/models/query/filterTypes';

export interface QueryState {
  baseTable: BaseTable,
  baseFilter: FilterNode,
  markFilter: FilterNode,
  filterDragNode: FilterDragNode,
}

export const useQueryStore = defineStore('query', {
  state: (): QueryState => ({
    baseTable: BaseTable.Varieties,
    baseFilter: FilterNode.FilterRoot(FilterOperand.And, FilterType.Base),
    markFilter: FilterNode.FilterRoot(FilterOperand.And, FilterType.Mark),
    filterDragNode: false,
  }),


  getters: {
    marksAvailable(state) {
      const s = state as QueryState;
      return s.baseTable === BaseTable.Batches
        || s.baseTable === BaseTable.Varieties
        || s.baseTable === BaseTable.Trees
    },
  },


  actions: {},
});
