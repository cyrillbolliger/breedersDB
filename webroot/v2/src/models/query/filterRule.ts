import {FilterComparatorOption, FilterCriteria, FilterOption} from 'src/models/query/filterTypes';

export interface FilterRule {
  column: FilterOption | undefined,
  comparator: FilterComparatorOption | undefined,
  criteria: FilterCriteria | undefined,
}
