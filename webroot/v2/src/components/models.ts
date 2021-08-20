export interface MarkForm {
  id: number
  name: string
  description: string
}

export interface Tree {
  id: number
  publicid: string
  // date_grafted: date|null
  // date_planted: date|null
  // date_eliminated: date|null
  // date_labeled: date|null
  genuine_seedling: boolean
  migrated_tree: boolean
  offset: number | null
  dont_eliminate: boolean | null
  note: string | null
  variety_id: number
  rootstock_id: number | null
  grafting_id: number | null
  row_id: number | null
  experiment_site_id: number
  // deleted: date|null
  // created: date|null
  // modified: date|null
  convar: string
  // experiment_site?: ExperimentSite
  // row?: Row|null
  // grafting?: Grafting|null
  // rootstock?: Rootstock|null
  // variety?: Variety
}
