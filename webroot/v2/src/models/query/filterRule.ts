import {FilterComparatorOption, FilterCriteria, FilterOption} from 'src/models/query/filterTypes';

export class FilterRule {
  column: FilterOption | undefined;
  comparator: FilterComparatorOption | undefined;
  criteria: FilterCriteria | undefined;
  isValid = false;

  // noinspection JSUnusedGlobalSymbols
  toJSON() {
    return {
      column: this.column?.value,
      comparator: this.comparator?.value,
      criteria: this.criteria
    }
  }
}
