import {PropertySchema, PropertySchemaOptionType} from 'src/models/query/filterOptionSchema';
import {QTableColumn} from 'quasar';
import {MarkFormProperty} from 'src/models/form';
import {MarkAggregation, MarkCell, ViewEntity} from 'src/models/query/query';


function schemaToColumn(schema: PropertySchema[]): QTableColumn[] {
  return schema.map((item: PropertySchema) => {
    const isNum = item.options.type === PropertySchemaOptionType.Integer
      || item.options.type === PropertySchemaOptionType.Float;

    return {
      name: item.name,
      label: item.label,
      field: item.name,
      align: isNum ? 'right' : 'left',
      sortable: true,
      format: (val: string | number | Date | null | undefined) => formatColumnValue(val || null, item.options.type)
    } as QTableColumn;
  });
}

function formatColumnValue(val: string | number | Date | null, type: PropertySchemaOptionType) {
  if (null === val) {
    return '';
  } else if (val instanceof Date) {
    return val.toLocaleDateString();
  } else if (typeof (val) === 'number') {
    return val.toLocaleString();
  } else if (type === PropertySchemaOptionType.Integer) {
    return parseInt(val).toLocaleString();
  } else if (type === PropertySchemaOptionType.Float) {
    return parseFloat(val).toLocaleString();
  } else if (type === PropertySchemaOptionType.Date) {
    return new Date(val).toLocaleDateString();
  }

  return val;
}


function markFormPropertiesToColumn(markFormProperties: MarkFormProperty[], namePrefix: string) {
  return markFormProperties.map((item: MarkFormProperty) => {
    return {
      name: `Mark.${item.id}`,
      label: namePrefix + item.name,
      field: (row: ViewEntity) => getMarkData(row, item),
      align: 'center',
      sortable: false,
    } as QTableColumn;
  });
}

function getMarkData(row: ViewEntity, property: MarkFormProperty) {
  if ( ! ('marks_view' in row)) {
    return [];
  }

  const marks: MarkCell[] = (row.marks_view as ViewEntity[])
    .filter((mark: ViewEntity) => mark.property_id === property.id)
    .map((mark: ViewEntity) => {
      const viewEntity = Object.assign({}, row);
      delete viewEntity.marks_view;
      delete viewEntity.trees_view;
      const markCellEntity = removePropertyPrefix(viewEntity) as MarkCell['entity'];

      const cell = (mark as unknown) as MarkCell;
      cell.entity = markCellEntity;
      cell.aggregation = MarkAggregation.None;
      return cell;
    });

  if ('trees_view' in row && Array.isArray(row.trees_view)) {
    for (const tree of row.trees_view) {
      marks.push(...getMarkData(tree, property))
    }
  }

  return marks;
}

function removePropertyPrefix(obj: Record<string, unknown>) {
  const prefixed = Object.keys(obj);
  const values: unknown[] = (<any>Object).values(obj); // eslint-disable-line

  if (0 === values.length) {
    return obj;
  }

  const prefixLen = prefixed[0].indexOf('.') + 1;

  if (0 >= prefixLen) {
    return obj;
  }

  const unprefixed = prefixed.map(key => key.substring(prefixLen));

  // noinspection TypeScriptValidateTypes
  return Object.assign( // eslint-disable-line
    {},
    ...unprefixed.map((k, i) => ({[k]: values[i]}))
  );
}


export default function useResultColumnConverter() {
  return {
    schemaToColumn,
    markFormPropertiesToColumn,
  }
}
