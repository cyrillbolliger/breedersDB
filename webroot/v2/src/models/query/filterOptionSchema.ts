import {BaseTable} from 'src/models/query/query';

export enum PropertySchemaOptionType {
  String = 'string',
  Integer = 'integer',
  Float = 'double',
  Boolean = 'boolean',
  Enum = 'enum',
  Date = 'date',
  Datetime = 'datetime',
  Time = 'time',
  Photo = 'photo',
}

export interface PropertySchemaStringOptions {
  type: PropertySchemaOptionType.String,
  validation: {
    maxLen: number|null,
    pattern: string|null,
  }
}

export interface PropertySchemaIntegerOptions {
  type: PropertySchemaOptionType.Integer,
  validation: {
    min: number,
    max: number,
    step: number,
  },
}

export interface PropertySchemaDoubleOptions {
  type: PropertySchemaOptionType.Float,
  validation: {
    min: number,
    max: number,
    step: number,
  },
}

export interface PropertySchemaBooleanOptions {
  type: PropertySchemaOptionType.Boolean,
}

export interface PropertySchemaEnumOptions {
  type: PropertySchemaOptionType.Enum,
  validation: {
    options: string[]
  },
}

export interface PropertySchemaDateOptions {
  type: PropertySchemaOptionType.Date,
}

export interface PropertySchemaDatetimeOptions {
  type: PropertySchemaOptionType.Datetime,
}

export interface PropertySchemaTimeOptions {
  type: PropertySchemaOptionType.Time,
}

export interface PropertySchemaPhotoOptions {
  type: PropertySchemaOptionType.Photo,
}

export interface PropertySchemaEmptyOption {
  allowEmpty: boolean,
}

export type PropertySchemaOptions = PropertySchemaEmptyOption & (
  PropertySchemaStringOptions
  | PropertySchemaIntegerOptions
  | PropertySchemaDoubleOptions
  | PropertySchemaEnumOptions
  | PropertySchemaBooleanOptions
  | PropertySchemaDateOptions
  | PropertySchemaDatetimeOptions
  | PropertySchemaTimeOptions
  | PropertySchemaPhotoOptions
  )

export interface PropertySchema {
  name: string, // e.g. TreesView.publicid
  label: string // e.g. Tree -> Publicid
  options: PropertySchemaOptions
}

export interface FilterOptionSchemas {
  [BaseTable.Batches]: PropertySchema[],
  [BaseTable.Crossings]: PropertySchema[],
  [BaseTable.MotherTrees]: PropertySchema[],
  [BaseTable.ScionsBundles]: PropertySchema[],
  [BaseTable.Trees]: PropertySchema[],
  [BaseTable.Varieties]: PropertySchema[],
  Marks: PropertySchema[],
}
