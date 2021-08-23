import {Tree} from 'src/models/tree';

export enum MarkFormFieldType {
  Integer = 'INTEGER',
  Float = 'FLOAT',
  Boolean = 'BOOLEAN',
  Date = 'DATE',
  String = 'VARCHAR',
}

export type MarkFormFieldNumberConstraint = null | {min: number, max: number, step: number}
export type MarkValueValue = string|boolean|number|Date

export interface MarkFromProperty {
  id: number
  name: string
  number_constraints: MarkFormFieldNumberConstraint
  field_type: MarkFormFieldType
  note: string | null
  mark_form_property_type_id: number
  created: Date|null
  modified: Date|null
  tree_property: boolean
  variety_property: boolean
  batch_property: boolean
  // mark_form_property_type?: MarkFormPropertyType
}

export interface MarkForm {
  id: number
  name: string
  description: string | null
  created: Date|null
  modified: Date|null
  mark_form_properties?: MarkFromProperty[] | null
}

export interface MarkValue {
  id?: number
  value: MarkValueValue
  exceptional_mark: boolean
  mark_form_property_id: number
  mark_id?: number
  created?: Date|null
  modified?: Date|null
  mark_form?: MarkForm
  mark_form_property?: MarkFromProperty
}

export interface Mark {
  id?: number
  date: Date|null
  author: string|null
  mark_form_id: number
  tree_id: number|null
  variety_id: number|null
  batch_id: number|null
  created?: Date|null
  modified?: Date|null
  mark_form?: MarkForm
  tree?: Tree
  // variety?: Variety
  // batch?: Batch
  mark_values?: MarkValue[]
}
