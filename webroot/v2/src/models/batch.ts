export interface BatchView {
  id: number
  crossing_batch: string
  date_sowed: string|null
  numb_seeds_sowed: number|null
  numb_sprouts_grown: number|null
  seed_tray: number|null
  date_planted: number|null
  numb_sprouts_planted: number|null
  patch: string|null
  note: string|null
  crossing_id: number
}
