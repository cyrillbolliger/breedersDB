export enum DataType {
  Integer = 'INTEGER',
  Float = 'FLOAT',
  String = 'VARCHAR',
  Boolean = 'BOOLEAN',
  Date = 'DATE',
  Photo = 'PHOTO',
}

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

export interface FilterOption {
  label: string,
  value: string,
  type: DataType,
}

export interface FilterComparatorOption {
  label: string,
  value: string,
  type: DataType[],
}
