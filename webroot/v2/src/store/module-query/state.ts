export enum BaseTable {
  Crossings = 'crossings',
  Batches = 'batches',
  Varieties = 'varieties',
  Trees = 'trees',
  MotherTrees = 'motherTrees',
  ScionsBundles = 'scionsBundles',
}

export enum FilterOperand {
  And = 'and',
  Or = 'or',
}

export enum DataType {
  Integer = 'INTEGER',
  Float = 'FLOAT',
  String = 'VARCHAR',
  Boolean = 'BOOLEAN',
  Date = 'DATE',
  Photo = 'PHOTO',
}

export enum FilterType {
  Base = 'base',
  Mark = 'mark',
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

export type FilterCriteria = string;

export interface FilterRule {
  column: FilterOption | undefined,
  comparator: FilterComparatorOption | undefined,
  criteria: FilterCriteria | undefined,
}

export interface FilterBase {
  id: number,
  level: number,
  type: FilterType,
}

export interface FilterTreeRoot extends FilterBase {
  children: Array<FilterTree|FilterLeaf>,
  operand: FilterOperand,
}

export interface FilterChild {
  parentId: number, // don't point directly to the parent as we cant serialize circular references
}

export interface FilterTree extends FilterTreeRoot, FilterChild {
}

export interface FilterLeaf extends FilterBase, FilterChild {
  filter: FilterRule,
}

export interface QueryStateInterface {
  base: BaseTable,
  baseFilter: FilterTreeRoot,
  markFilter: FilterTreeRoot,
  lastFilterId: number,
}

function state(): QueryStateInterface {
  return {
    base: BaseTable.Varieties,
    baseFilter: {
      id: 0,
      children: [],
      level: 0,
      type: FilterType.Base,
      operand: FilterOperand.And,
    },
    markFilter: {
      id: 1,
      children: [],
      level: 0,
      type: FilterType.Mark,
      operand: FilterOperand.And,
    },
    lastFilterId: 1,
  };
}

export default state;
