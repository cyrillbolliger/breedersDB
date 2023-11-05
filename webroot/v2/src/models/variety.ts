export interface VarietyView {
  id: number
  convar: string
  official_name: string | null
  acronym: string | null
  plant_breeder: string | null
  registration: string | null
  description: string | null
  batch_id: number
}

export interface Variety {
  id: number
  code: string
  official_name: string | null
  acronym: string | null
  plant_breeder: string | null
  registration: string | null
  description: string | null
  batch_id: number
  deleted: Date | null
  created: Date | null
  modified: Date | null
  convar: string
}
