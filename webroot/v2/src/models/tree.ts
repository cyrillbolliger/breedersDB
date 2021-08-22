export interface Tree {
  id: number
  publicid: string
  date_grafted: Date|null
  date_planted: Date|null
  date_eliminated: Date|null
  date_labeled: Date|null
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
  deleted: Date|null
  created: Date|null
  modified: Date|null
  convar: string
  // experiment_site?: ExperimentSite
  // row?: Row|null
  // grafting?: Grafting|null
  // rootstock?: Rootstock|null
  // variety?: Variety
}
