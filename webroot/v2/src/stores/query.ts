import {defineStore} from 'pinia';
import {BaseTable, FilterDragNode} from 'src/models/query/query';
import {FilterNode} from 'src/models/query/filterNode';
import {FilterOperand, FilterType} from 'src/models/query/filterTypes';
import {MarkFormProperty} from 'src/models/form';
import useApi from 'src/composables/api';
import useMarkFormPropertyConverter from 'src/composables/queries/markFormPropertyConverter';

const markFormPropertyConverter = useMarkFormPropertyConverter();

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

    markPropertySchema: (state) => (prefix: string) => {
      const s = state as QueryState;
      return s.markFormProperties
        .map(markFormPropertyConverter.toPropertySchema)
        .map(item => {
          if (!item.label.startsWith(prefix)){
            item.label = prefix + item.label
          }
          return item;
        });
    }
  },


  actions: {
    async maybeLoadMarkFormProperties() {
      if (! this.markFormProperties.length) {
        await useApi().get<MarkFormProperty[]>('mark-form-properties')
          .then(data => this.markFormProperties = data as MarkFormProperty[]);
      }
    }
  },
});
