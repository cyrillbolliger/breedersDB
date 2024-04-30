import {defineStore} from 'pinia';
import {BaseTable, FilterDragNode} from 'src/models/query/query';
import {FilterNode} from 'src/models/query/filterNode';
import {FilterOperand, FilterType} from 'src/models/query/filterTypes';
import {MarkFormProperty} from 'src/models/form';
import useApi from 'src/composables/api';
import useMarkFormPropertyConverter from 'src/composables/queries/markFormPropertyConverter';
import useQueryLocalStorageHelper from 'src/composables/queries/queryLocalStorageHelper';
import {FilterOptionSchemas, PropertySchema} from 'src/models/query/filterOptionSchema';
import {QueryGroup} from 'src/models/queryGroup';

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
  visibleColumns: string[],
  showRowsWithoutMarks: boolean,
  queryGroups: QueryGroup[],
  queryGroup: QueryGroup|null,
  queryCode: string,
  queryDescription: string,
  attemptedToSaveQuery: boolean,
}

export const useQueryStore = defineStore('query', {
  state: (): QueryState => ({
    baseTable: localStorageHelper.getBaseTable(BaseTable.Varieties),
    baseFilter: localStorageHelper.getBaseFilter(defaultBaseFilter), // use getters and actions
    markFilter: localStorageHelper.getMarkFilter(defaultMarkFilter), // use getters and actions
    filterDragNode: false,
    markFormProperties: [],
    filterOptionSchemas: undefined,
    visibleColumns: localStorageHelper.getVisibleColumns(),
    showRowsWithoutMarks: localStorageHelper.getShowRowsWithoutMarks(true),
    queryGroups: [],
    queryGroup: null,
    queryCode: '',
    queryDescription: '',
    attemptedToSaveQuery: false,
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

    getVisibleColumns(state) {
      const columns = (state as QueryState).visibleColumns;
      const baseTable = (state as QueryState).baseTable;
      const marksAvailable = this.marksAvailable;

      return columns.filter((col: string): boolean => {
        if (col.startsWith(`${baseTable}View.`)) {
          return true;
        }

        return marksAvailable && col.startsWith('Mark.');
      });
    },

    hasVisibleMarkColumns(): boolean {
      // noinspection JSIncompatibleTypesComparison
      return this.getVisibleColumns
        .find((col: string): boolean => col.startsWith('Mark.')) !== undefined;
    },

    getMarkTooltipColumns(state) {
      if ( ! this.hasVisibleMarkColumns) {
        return [];
      }

      const baseTable = (state as QueryState).baseTable;

      if (baseTable === BaseTable.Batches) {
        return [
          'BatchesView.crossing_batch',
          'BatchesView.date_sowed',
          'BatchesView.numb_seeds_sowed',
          'BatchesView.numb_sprouts_grown',
          'BatchesView.seed_tray',
          'BatchesView.date_planted',
          'BatchesView.numb_sprouts_planted',
          'BatchesView.patch',
          'BatchesView.note',
        ];
      }

      if (baseTable === BaseTable.Varieties) {
        return [
          'VarietiesView.convar',
          'VarietiesView.official_name',
          'VarietiesView.acronym',
          'VarietiesView.plant_breeder',
          'VarietiesView.registration',
          'VarietiesView.description',
        ];
      }

      if (baseTable === BaseTable.Trees) {
        return [
          'TreesView.publicid',
          'TreesView.convar',
          'TreesView.date_planted',
          'TreesView.date_eliminated',
          'TreesView.experiment_site',
          'TreesView.row',
          'TreesView.offset',
          'TreesView.note',
        ];
      }

      return [];
    },

    rowsWithMarksOnly(state) {
      if ( ! this.marksAvailable || ! this.hasVisibleMarkColumns) {
        return false;
      }

      return ! (state as QueryState).showRowsWithoutMarks
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

    async maybeLoadQueryGroups() {
      if ( ! this.queryGroups.length) {
        await this.forceLoadQueryGroups();
      }
    },

    async forceLoadQueryGroups() {
      await useApi().get<QueryGroup[]>('query-groups')
          .then(data => this.queryGroups = data as QueryGroup[]);
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

    setVisibleColumns(columns: string[]) {
      this.visibleColumns = columns;
      localStorageHelper.setVisibleColumns(columns);
    },

    setShowRowsWithoutMarks(show: boolean) {
      this.showRowsWithoutMarks = show;
      localStorageHelper.setShowRowsWithoutMarks(show);
    },

    async setQueryGroupById(id: number) {
      await this.maybeLoadQueryGroups();
      const candidates = this.queryGroups
        .filter((item: QueryGroup) => item.id === id);
      if (!candidates.length) {
        this.queryGroup = this.queryGroups[0];
      }
      this.queryGroup = candidates[0];
    }
  },
});
