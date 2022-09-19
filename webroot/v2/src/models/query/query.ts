import {FilterNode} from 'src/models/query/filterNode';

export enum BaseTable {
  Crossings = 'crossings',
  Batches = 'batches',
  Varieties = 'varieties',
  Trees = 'trees',
  MotherTrees = 'motherTrees',
  ScionsBundles = 'scionsBundles',
}

export type FilterDragNode = FilterNode | false;
