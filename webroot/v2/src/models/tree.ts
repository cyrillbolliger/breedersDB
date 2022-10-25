export interface Tree {
  id: number
  publicid: string
  date_grafted: string | null
  date_planted: string | null
  date_eliminated: string | null
  date_labeled: string | null
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
  deleted: Date | null
  created: Date | null
  modified: Date | null
  convar: string
  print?: {
    regular: string,
    anonymous: string
  }
  // experiment_site?: ExperimentSite
  // row?: Row|null
  // grafting?: Grafting|null
  // rootstock?: Rootstock|null
  // variety?: Variety
}

export interface TreeView {
  id: number
  publicid: string
  convar: string
  date_grafted: string | null
  date_planted: string | null
  date_eliminated: string | null
  date_labeled: string | null
  genuine_seedling: boolean
  offset: number | null
  row: string | null
  dont_eliminate: boolean | null
  note: string | null
  variety_id: number
  grafting: string | null
  rootstock: string | null
  experiment_site: string
}
