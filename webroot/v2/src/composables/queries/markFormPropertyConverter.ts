import {MarkFormFieldType, MarkFormProperty} from 'src/models/form';
import {
  PropertySchema,
  PropertySchemaOptions,
  PropertySchemaOptionType,
} from 'src/models/query/filterOptionSchema';


export default function useMarkFormPropertyConverter() {
  return {
    toPropertySchema
  }
}


function toPropertySchema(item: MarkFormProperty) {
  return {
    name: `Mark.${item.id}`,
    label: item.name,
    options: convertMarkFormPropertyToSchemaOption(item),
  } as PropertySchema
}

function convertMarkFormPropertyToSchemaOption(property: MarkFormProperty): PropertySchemaOptions {
  const type = {
    [MarkFormFieldType.Integer]: 'integer',
    [MarkFormFieldType.Float]: 'double',
    [MarkFormFieldType.Boolean]: 'boolean',
    [MarkFormFieldType.Date]: 'date',
    [MarkFormFieldType.String]: 'string',
    [MarkFormFieldType.Photo]: 'photo',
  }[property.field_type] || undefined;

  if (undefined === type){
    throw Error(`Unknown mark form property field type: ${property.field_type}`);
  }

  switch (type) {
    case 'string':
      return {
        type: type as PropertySchemaOptionType,
        allowEmpty: false,
        validation: {
          maxLen: 255,
          pattern: null,
        },
      } as PropertySchemaOptions;
    case 'integer':
    case 'double':
      return {
        type: type as PropertySchemaOptionType,
        allowEmpty: false,
        validation: property.number_constraints,
      } as PropertySchemaOptions;
    default:
      return {
        type: type as PropertySchemaOptionType,
        allowEmpty: false,
      } as PropertySchemaOptions;
  }
}
