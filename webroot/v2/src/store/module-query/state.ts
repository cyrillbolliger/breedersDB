export enum BaseTable {
  Crossings = 'crossings',
  Batches = 'batches',
  Varieties = 'varieties',
  Trees = 'trees',
  MotherTrees = 'motherTrees',
  ScionsBundles = 'scionsBundles',
}

export interface QueryStateInterface {
  base: BaseTable
}

function state(): QueryStateInterface {
  return {
    base: BaseTable.Varieties,
  };
}

export default state;
