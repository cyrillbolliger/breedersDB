import {PropertySchema, PropertySchemaOptionType} from 'src/models/query/filterOptionSchema';
import {QTableColumn} from 'quasar';
import {MarkFormFieldType, MarkFormProperty} from 'src/models/form';
import {AggregatedMarkCell, MarkCell, ViewEntity} from 'src/models/query/query';
import {Composer} from 'vue-i18n';


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


function markFormPropertiesToColumn(markFormProperties: MarkFormProperty[], t: Composer['t']) {
  const columns: QTableColumn[] = [];
  const labelPrefix = t('queries.Marks') + ' > '
  const nonSuffix = '';
  const countSuffix = ' (' + t('queries.countSuffix') + ')';
  const maxSuffix = ' (' + t('queries.maxSuffix') + ')';
  const minSuffix = ' (' + t('queries.minSuffix') + ')';
  const meanSuffix = ' (' + t('queries.meanSuffix') + ')';
  const medianSuffix = ' (' + t('queries.medianSuffix') + ')';
  const stdDevSuffix = ' (' + t('queries.stdDevSuffix') + ')';

  markFormProperties.forEach(item => {
    const notAggregated = makeMarkColItem(item, 'none', labelPrefix, nonSuffix, getMarkData);
    columns.push(notAggregated);

    if (item.field_type === MarkFormFieldType.Integer || item.field_type === MarkFormFieldType.Float) {
      const count = makeMarkColItem(item, 'count', labelPrefix, countSuffix, getCount);
      const max = makeMarkColItem(item, 'max', labelPrefix, maxSuffix, getMax);
      const min = makeMarkColItem(item, 'min', labelPrefix, minSuffix, getMin);
      const mean = makeMarkColItem(item, 'mean', labelPrefix, meanSuffix, getMean);
      const median = makeMarkColItem(item, 'median', labelPrefix, medianSuffix, getMedian);
      const stdDev = makeMarkColItem(item, 'stdDev', labelPrefix, stdDevSuffix, getStdDev);

      columns.push(count, max, min, mean, median, stdDev);
    }
  });

  return columns;
}

function makeMarkColItem(
  item: MarkFormProperty,
  nameSuffix: string,
  labelPrefix: string,
  labelSuffix: string,
  extractorFn: (row: ViewEntity, item: MarkFormProperty) => MarkCell[] | AggregatedMarkCell | null,
): QTableColumn {
  return {
    name: `Mark.${item.id}-${nameSuffix}`,
    label: labelPrefix + item.name + labelSuffix,
    field: (row: ViewEntity) => extractorFn(row, item),
    align: 'center',
    sortable: false,
  } as QTableColumn;
}

function getCount(row: ViewEntity, property: MarkFormProperty): AggregatedMarkCell | null {
  return getAggregatedMarkCell(
    row,
    property,
    rawValues => rawValues.length
  )
}

function getMax(row: ViewEntity, property: MarkFormProperty): AggregatedMarkCell | null {
  return getAggregatedMarkCell(
    row,
    property,
    rawValues => Math.max(...rawValues.map(cell => parseFloat(cell.value.toString())))
  )
}

function getMin(row: ViewEntity, property: MarkFormProperty): AggregatedMarkCell | null {
  return getAggregatedMarkCell(
    row,
    property,
    rawValues => Math.min(...rawValues.map(cell => parseFloat(cell.value.toString())))
  )
}

function getMean(row: ViewEntity, property: MarkFormProperty): AggregatedMarkCell | null {
  return getAggregatedMarkCell(
    row,
    property,
    rawValues => {
      const sum = rawValues
        .map(cell => parseFloat(cell.value.toString()))
        .reduce((previousVal, currentVal) => currentVal + previousVal, 0);
      return sum / rawValues.length;
    }
  )
}

function getMedian(row: ViewEntity, property: MarkFormProperty): AggregatedMarkCell | null {
  return getAggregatedMarkCell(
    row,
    property,
    rawValues => {
      const values = rawValues.map(cell => parseFloat(cell.value.toString())).sort();
      if (values.length % 2 === 0) {
        const val1 = values[values.length / 2];
        const val2 = values[(values.length / 2) - 1]; // -1 not +1 because the array is zero indexed
        return (val1 + val2) / 2;
      }

      // it's floor not ceil because the array is zero indexed
      return values[Math.floor(values.length / 2)];
    }
  )
}

function getStdDev(row: ViewEntity, property: MarkFormProperty): AggregatedMarkCell | null {
  return getAggregatedMarkCell(
    row,
    property,
    rawValues => {
      const values = rawValues.map(cell => parseFloat(cell.value.toString()));
      const mean = values.reduce((previousVal, currentVal) => currentVal + previousVal, 0) / values.length;
      const variance = values.map(val => (val - mean) ** 2).reduce((previousVal, currentVal) => currentVal + previousVal, 0) / values.length;
      return Math.sqrt(variance);
    }
  )
}

function getAggregatedMarkCell(row: ViewEntity, property: MarkFormProperty, extractorFn: (rawValues: MarkCell[]) => number): AggregatedMarkCell | null {
  const rawValues = getMarkData(row, property);

  if ( ! rawValues.length) {
    return null;
  }

  return {
    property_id: rawValues[0].property_id,
    name: rawValues[0].name,
    value: formatColumnValue(extractorFn(rawValues), PropertySchemaOptionType.Float),
    rawValues: rawValues,
  };
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
    formatColumnValue,
  }
}
