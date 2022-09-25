import {PropertySchema, PropertySchemaOptionType} from 'src/models/query/filterOptionSchema';

export enum FilterComparator {
  Equal = '===',
  NotEqual = '!==',
  Less = '<',
  LessOrEqual = '<=',
  Greater = '>',
  GreaterOrEqual = '>=',
  StartsWith = 'startsWith',
  StartsNotWith = 'startsNotWith',
  Contains = 'contains',
  NotContains = 'notContains',
  EndsWith = 'endsWith',
  NotEndsWith = 'notEndsWith',
  Empty = 'empty',
  NotEmpty = 'notEmpty'
}

export enum FilterOperand {
  And = 'and',
  Or = 'or',
}

export enum FilterType {
  Base = 'base',
  Mark = 'mark',
}

export interface FilterOption {
  label: string,
  value: string,
  schema: PropertySchema,
}

export interface FilterComparatorOption {
  label: string,
  value: string,
  type: PropertySchemaOptionType[],
}

export type FilterCriteria = string;
