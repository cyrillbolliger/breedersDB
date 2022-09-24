import {FilterNode} from 'src/models/query/filterNode';

export enum BaseTable {
  Crossings = 'Crossings',
  Batches = 'Batches',
  Varieties = 'Varieties',
  Trees = 'Trees',
  MotherTrees = 'MotherTrees',
  ScionsBundles = 'ScionsBundles',
}

export type FilterDragNode = FilterNode | false;
