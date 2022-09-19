<template>
  <div
    v-if="isEmpty"
  >
    <div class="filter-tree-root__dummy-filter">
      {{ t('queries.noFilter', {entity: entityName}) }}
    </div>
    <FilterRuleButtonAdd
      :operand="FilterOperand.And"
      :node="filter"
    />
  </div>

  <template v-else>
    <div
      class="filter-tree-root__notification"
      v-if="isSimplifiable"
    >
      <q-icon name="warning"/>
      {{ t('queries.simplifiable') }}
      <button
        class="filter-tree-root__simplify"
        @click="simplify()"
      >
        {{ t('queries.simplify') }}
      </button>
    </div>
    <FilterTree
      :node="filter"
      :options="options"
      :operand="filter.getChildrensOperand()"
    />
  </template>
</template>

<script setup lang="ts">
import FilterTree from 'components/Query/FilterTree.vue';
import {FilterDataType, FilterOperand, FilterType} from 'src/models/query/filterTypes';
import {computed, PropType} from 'vue';
import {FilterNode} from 'src/models/query/filterNode';
import {useI18n} from 'vue-i18n';
import FilterRuleButtonAdd from 'components/Query/FilterRuleButtonAdd.vue';
import {useQueryStore} from 'stores/query';

const {t} = useI18n(); // eslint-disable-line @typescript-eslint/unbound-method
const store = useQueryStore();

const props = defineProps({
  filter: {
    type: Object as PropType<FilterNode>,
    required: true
  }
});

// noinspection TypeScriptUnresolvedFunction
const isSimplifiable = computed(() => props.filter.isSimplifiable());
// noinspection TypeScriptUnresolvedFunction
const isEmpty = computed(() => ! props.filter.hasChildren());

const entityName = computed(() => {
  // noinspection TypeScriptUnresolvedFunction
  if (props.filter.getFilterType() === FilterType.Mark) {
    return t('queries.marks');
  }

  return t('queries.' + store.baseTable.toString());
});

function simplify() {
  let maxIterations = 10;
  while (isSimplifiable.value && maxIterations--) {
    // noinspection TypeScriptUnresolvedFunction
    props.filter.simplify()
  }
}

// todo: replace stubs
const options = [
  {label: 'Trees -> ID', value: 'trees_id', type: FilterDataType.Integer},
  {label: 'Trees -> Row', value: 'trees_row', type: FilterDataType.Float},
  {label: 'Marks -> Note', value: 'marks_note', type: FilterDataType.String},
  {label: 'Marks -> Photo', value: 'marks_photo', type: FilterDataType.Photo},
  {label: 'Marks -> Date', value: 'marks_date', type: FilterDataType.Date},
  {label: 'Marks -> Original', value: 'marks_original', type: FilterDataType.Boolean},
];

</script>

<style scoped>
.filter-tree-root__notification {
  color: var(--q-accent);
}

.filter-tree-root__simplify {
  color: var(--q-accent);
  background: none;
  padding: 0;
  border: none;
  text-decoration: underline;
  cursor: pointer;
}

.filter-tree-root__simplify:hover,
.filter-tree-root__simplify:focus {
  filter: brightness(125%);
  text-decoration: none;
}

.filter-tree-root__dummy-filter {
  background: #EFEFEF;
  width: 100%;
  min-height: 48px;
  border-left: solid 3px var(--q-primary);
  display: flex;
  align-items: center;
  padding: 4px 12px;
}
</style>
