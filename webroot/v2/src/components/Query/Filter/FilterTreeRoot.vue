<template>
  <div
    v-if="isEmpty"
  >
    <div class="filter-tree-root__dummy-filter">
      {{ t('queries.noFilter', {entity: entityName}) }}
    </div>
    <FilterRuleButtonAdd
      :node="filter"
      :operand="FilterOperand.And"
    />
  </div>

  <template v-else>
    <div
      v-if="isSimplifiable"
      class="filter-tree-root__notification--error"
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
    <div
      v-else-if="! isValid"
      class="filter-tree-root__notification--error"
    >
      <q-icon name="warning"/>
      {{ t('queries.invalid') }}
    </div>
    <div
      v-else
      class="filter-tree-root__notification--success"
    >
      <q-icon name="check"/>
      {{ t('queries.valid') }}
    </div>

    <FilterTree
      :node="filter"
      :operand="filter.getChildrensOperand()"
      :options="options"
    />
  </template>
</template>

<script lang="ts" setup>
import FilterTree from 'components/Query/Filter/FilterTree.vue';
import {FilterOperand, FilterType} from 'src/models/query/filterTypes';
import {computed, PropType} from 'vue';
import {FilterNode} from 'src/models/query/filterNode';
import {useI18n} from 'vue-i18n';
import FilterRuleButtonAdd from 'components/Query/Filter/FilterRuleButtonAdd.vue';
import {useQueryStore} from 'stores/query';
import {PropertySchema} from 'src/models/query/filterOptionSchema';

const {t} = useI18n(); // eslint-disable-line @typescript-eslint/unbound-method
const store = useQueryStore();

const props = defineProps({
  filter: {
    type: Object as PropType<FilterNode>,
    required: true
  },
  options: {
    type: Object as PropType<PropertySchema[]>,
    required: true,
  },
});

// noinspection TypeScriptUnresolvedFunction
const isSimplifiable = computed(() => props.filter.isSimplifiable());
// noinspection TypeScriptUnresolvedFunction
const isEmpty = computed(() => ! props.filter.hasChildren());
// noinspection TypeScriptUnresolvedFunction
const isValid = computed(() => props.filter.isValid());

const entityName = computed(() => {
  // noinspection TypeScriptUnresolvedFunction
  if (props.filter.getFilterType() === FilterType.Mark) {
    return t('queries.marks');
  }

  return t('queries.' + lowerCaseFirstChar(store.baseTable.toString()));
});

function simplify() {
  let maxIterations = 10;
  while (isSimplifiable.value && maxIterations--) {
    // noinspection TypeScriptUnresolvedFunction
    props.filter.simplify()
  }
}

function lowerCaseFirstChar(str: string) {
  return str[0].toLowerCase() + str.slice(1)
}

</script>

<style scoped>
.filter-tree-root__notification--error {
  color: var(--q-accent);
}

.filter-tree-root__notification--success {
  color: var(--q-primary);
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
