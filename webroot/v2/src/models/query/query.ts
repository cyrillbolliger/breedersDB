import {FilterNode} from 'src/models/query/filterNode';
import {PropertySchema} from 'src/models/query/filterOptionSchema';

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
  debug: null | QueryResponseDebug,
  results: ViewEntity[],
  schema: QueryResponseSchemas,
}
