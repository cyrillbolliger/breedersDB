<template>
  <div
    class="filter-rule"
    :class="{'filter-rule--invalid': ruleIsInvalid}"
  >
    <div
      :class="{
        'filter-rule--and': operand === FilterOperand.And,
        'filter-rule--or': operand === FilterOperand.Or
      }"
      class="row items-center"
    >
      <q-icon
        class="drag-handle"
        name="drag_indicator"
        size="md"
        @mousedown="$emit('dragMouseDown')"
        @mouseup="$emit('dragMouseUp')"
      />
      <div class="col row q-col-gutter-sm">
        <FilterRuleColumn
          :model-value="column"
          :options="options"
          @update:model-value="updateColumn"
          @valid="columnIsValid = true"
          @invalid="columnIsValid = false"
        />
        <FilterRuleComparator
          :schema="column?.schema || null"
          :disabled="column === undefined"
          :model-value="comparator"
          @update:model-value="updateComparator"
          @valid="comparatorIsValid = true"
          @invalid="comparatorIsValid = false"
        />
        <FilterRuleCriteria
          :schema="column?.schema || null"
          :disabled="comparator === undefined"
          :hide="!hasInputCriteria"
          :model-value="criteria"
          @update:model-value="updateCriteria"
          @valid="criteriaInputIsValid = true"
          @invalid="criteriaInputIsValid = false"
        />
      </div>
      <q-icon
        :color="ruleIsValid ? 'positive' : 'negative'"
        :name="ruleIsValid ? 'check' : 'warning'"
        class="q-ml-sm"
        size="sm"
      />
      <q-btn
        class="delete-button"
        dense
        flat
        icon="delete_outline"
        rounded
        @click="deleteRule"
      />
    </div>
  </div>
</template>

<script lang="ts" setup>
import {computed, PropType, ref} from 'vue';
import {
  FilterComparator,
  FilterComparatorOption,
  FilterCriteria,
  FilterOperand,
  FilterOption
} from 'src/models/query/filterTypes';
import {FilterNode} from 'src/models/query/filterNode';
import {PropertySchema, PropertySchemaOptionType} from 'src/models/query/filterOptionSchema';
import FilterRuleCriteria from 'components/Query/FilterRuleCriteria.vue';
import FilterRuleColumn from 'components/Query/FilterRuleColumn.vue';
import FilterRuleComparator from 'components/Query/FilterRuleComparator.vue';

defineEmits(['dragMouseDown', 'dragMouseUp'])

const props = defineProps({
  options: {
    type: Object as PropType<PropertySchema[]>,
    required: true,
  },
  node: {
    type: Object as PropType<FilterNode>,
    required: true,
  },
  operand: {
    type: String as PropType<FilterOperand>,
    required: true,
  }
});

const columnIsValid = ref<boolean>();
const comparatorIsValid = ref<boolean>();
const criteriaInputIsValid = ref<boolean>();

// noinspection TypeScriptUnresolvedFunction
const filterRule = computed(() => props.node.getFilterRule())
const column = computed(() => filterRule.value?.column)
const comparator = computed(() => filterRule.value?.comparator)
const criteria = computed(() => filterRule.value?.criteria);

function updateColumn(value: FilterOption) {
  filterRule.value.column = value;
}

function updateComparator(value: FilterComparatorOption) {
  filterRule.value.comparator = value;
}

function updateCriteria(value: FilterCriteria) {
  filterRule.value.criteria = value;
}

function deleteRule() {
  // noinspection TypeScriptUnresolvedFunction
  props.node.remove();
}

const hasInputCriteria = computed<boolean>(() => {
  switch (column.value?.schema.options.type) {
    case PropertySchemaOptionType.Date:
    case PropertySchemaOptionType.Integer:
    case PropertySchemaOptionType.Float:
    case PropertySchemaOptionType.Enum:
      return true
    case PropertySchemaOptionType.String:
      return comparator.value?.value !== FilterComparator.Empty
        && comparator.value?.value !== FilterComparator.NotEmpty
    default:
      return false
  }
})

const ruleIsInvalid = computed<boolean>(() => {
  return ! ruleIsValid.value
    && column.value !== undefined
    && comparator.value !== undefined
    && (
      (
        column.value?.schema.options.type === PropertySchemaOptionType.Boolean
        || column.value?.schema.options.type === PropertySchemaOptionType.Photo
      )
      || criteria.value !== undefined
    )
})

const ruleIsValid = computed<boolean>(() => {
  return columnIsValid.value === true // may also be undefined
    && comparatorIsValid.value === true // may also be undefined
    && criteriaInputIsValid.value === true; // may also be undefined
})

</script>

<style scoped>
.filter-rule {
  padding: 4px 3px 4px 1px;
  background: #EFEFEF;
}

.filter-rule--invalid {
  background: #fad9dd;
}

.filter-rule--and {
  border-left-color: var(--q-primary);
}

.filter-rule--or {
  border-left-color: var(--q-accent);
}

.drag-handle {
  color: rgba(0, 0, 0, 0.6);
  cursor: grab;
}

.drag-handle:hover {
  color: var(--q-primary);
}

.delete-button {
  color: rgba(0, 0, 0, 0.6);
}

.delete-button:hover,
.delete-button:focus {
  color: var(--q-negative);
}
</style>
