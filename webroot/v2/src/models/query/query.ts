import {FilterNode} from 'src/models/query/filterNode';
import {PropertySchema} from 'src/models/query/filterOptionSchema';
import {TreeView} from 'src/models/tree';
import {VarietyView} from 'src/models/variety';
import {BatchView} from 'src/models/batch';

export enum BaseTable {
  Crossings = 'Crossings',
  Batches = 'Batches',
  Varieties = 'Varieties',
  Trees = 'Trees',
  MotherTrees = 'MotherTrees',
  ScionsBundles = 'ScionsBundles',
}

export type FilterDragNode = FilterNode | false;

export type QueryResponseDebug = {
  sql: string,
  params: {
    [key: string]: {
      value: string,
      type: string,
      placeholder: string,
    }
  }
};

export type ViewEntity = {[key: string]: null | number | string | ViewEntity[] }
export type QueryResponseSchemas = {[key: string]: PropertySchema[]};

export interface QueryResponse {
  count: number,
  offset: number,
  sortBy: string,
  order: 'asc' | 'desc',
  limit: number,
  debug: null | QueryResponseDebug,
  results: ViewEntity[],
  schema: QueryResponseSchemas,
}

export interface MarkCell {
  id: number,
  property_id: number,
  name: string,
  author: string,
  batch_id: number|null,
  variety_id: number|null,
  tree_id: number|null,
  date: string,
  exceptional_mark: boolean,
  field_type: 'INTEGER' | 'FLOAT' | 'BOOLEAN' | 'DATE' | 'VARCHAR' | 'PHOTO',
  property_type: string,
  value: string,
  entity: TreeView | VarietyView | BatchView
}
