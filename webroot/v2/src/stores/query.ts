import {defineStore} from 'pinia';
import {BaseTable, FilterDragNode} from 'src/models/query/query';
import {FilterNode} from 'src/models/query/filterNode';
import {FilterOperand, FilterType} from 'src/models/query/filterTypes';
import {MarkFormProperty} from 'src/models/form';
import useApi from 'src/composables/api';
import useMarkFormPropertyConverter from 'src/composables/queries/markFormPropertyConverter';
import useQueryLocalStorageHelper from 'src/composables/queries/queryLocalStorageHelper';
import {FilterOptionSchemas, PropertySchema} from 'src/models/query/filterOptionSchema';

const markFormPropertyConverter = useMarkFormPropertyConverter();
const localStorageHelper = useQueryLocalStorageHelper();

const defaultBaseFilter = FilterNode.FilterRoot(FilterOperand.And, FilterType.Base);
const defaultMarkFilter = FilterNode.FilterRoot(FilterOperand.And, FilterType.Mark);

export interface QueryState {
  baseTable: BaseTable,
  baseFilter: FilterNode,
  markFilter: FilterNode,
  filterDragNode: FilterDragNode,
  markFormProperties: MarkFormProperty[],
  filterOptionSchemas: FilterOptionSchemas | undefined,
}

export const useQueryStore = defineStore('query', {
  state: (): QueryState => ({
    baseTable: localStorageHelper.getBaseTable(BaseTable.Varieties),
    baseFilter: localStorageHelper.getBaseFilter(defaultBaseFilter), // use getters and actions
    markFilter: localStorageHelper.getMarkFilter(defaultMarkFilter), // use getters and actions
    filterDragNode: false,
    markFormProperties: [],
    filterOptionSchemas: undefined,
  }),


  getters: {
    marksAvailable(state) {
      const s = state as QueryState;
      return s.baseTable === BaseTable.Batches
        || s.baseTable === BaseTable.Varieties
        || s.baseTable === BaseTable.Trees
    },

    markPropertySchema(state) {
      const s = state as QueryState;
      return s.markFormProperties
        .map(markFormPropertyConverter.toPropertySchema)
    },

    baseFilterOptions(state) {
      const s = state as QueryState;
      if ( ! s.filterOptionSchemas || ! s.baseFilter) {
        return [];
      }

      const options: PropertySchema[] = [...s.filterOptionSchemas[s.baseTable]] || [];

      if (this.marksAvailable) {
        options.push(...this.markPropertySchema);
      }

      return options;
    },

    markFilterOptions(state) {
      const s = state as QueryState;
      if ( ! s.filterOptionSchemas) {
        return [];
      }

      return s.filterOptionSchemas['Marks'] || [];
    },

    getBaseFilter(state) {
      if ( ! this.baseFilterOptions) {
        return defaultBaseFilter;
      }

      return (state as QueryState).baseFilter;
    },

    getMarkFilter(state) {
      if ( ! this.markFilterOptions) {
        return defaultMarkFilter;
      }

      return (state as QueryState).markFilter;
    },
  },


  actions: {
    async maybeLoadMarkFormProperties() {
      if ( ! this.markFormProperties.length) {
        await useApi().get<MarkFormProperty[]>('mark-form-properties')
          .then(data => this.markFormProperties = data as MarkFormProperty[]);
      }
    },

    async maybeLoadFilterOptionSchemas() {
      if (undefined === this.filterOptionSchemas) {
        await useApi().get<FilterOptionSchemas>('queries/get-filter-schemas')
          .then(data => this.filterOptionSchemas = data as FilterOptionSchemas)
      }
    },

    async ensureSchemasLoaded() {
      const base = this.maybeLoadFilterOptionSchemas();
      const mark = this.maybeLoadMarkFormProperties();

      await Promise.all([base, mark])
    },

    setBaseFilter(filter: FilterNode) {
      // use object assign to maintain reactivity
      Object.assign(this.baseFilter, filter);
    },

    setMarkFilter(filter: FilterNode) {
      // use object assign to maintain reactivity
      Object.assign(this.markFilter, filter);
    },
  },
});
