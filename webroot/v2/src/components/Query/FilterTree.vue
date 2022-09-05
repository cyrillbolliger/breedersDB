<template>
  <div
    v-if="isFilterTree"
    class="filter-tree"
    :class="{
      'filter-tree--and': operand === FilterOperand.And,
      'filter-tree--or': operand === FilterOperand.Or
    }"
  >
    <FilterTree
      v-for="tree in node.children"
      :key="tree.id"
      :node="tree"
      :options="options"
      :operand="node.operand"
    />
    <q-btn
      v-if="hasAndButton"
      class="q-mt-md"
      :label="t('queries.filter.andFilter')"
      color="primary"
      outline
      @click="addRule(FilterOperand.And)"
    />
    <q-btn
      v-if="hasOrButton"
      class="q-mt-md q-ml-sm"
      :label="t('queries.filter.orFilter')"
      color="accent"
      outline
      @click="addRule(FilterOperand.Or)"
    />
  </div>

  <FilterRule
    v-else
    :options="options"
    :node="node"
    :operand="operand"
  />
</template>

<script setup lang="ts">
import {computed, PropType} from 'vue';
import {
  FilterLeaf,
  FilterOperand,
  FilterOption,
  FilterTree as FilterTreeType,
  FilterTreeRoot,
} from 'src/store/module-query/state';
import useFilter from 'src/composables/queries/filter';
import {useI18n} from 'vue-i18n';
import FilterRule from 'src/components/Query/FilterRule.vue'

const props = defineProps({
  node: {
    type: Object as PropType<FilterTreeType | FilterLeaf | FilterTreeRoot>,
    required: true,
  },
  options: {
    type: Object as PropType<Array<FilterOption>>,
    required: true,
  },
  operand: {
    type: String as PropType<FilterOperand>,
    required: true,
  },
});

const filter = useFilter();
const {t} = useI18n(); // eslint-disable-line @typescript-eslint/unbound-method

function addRule(operand: FilterOperand) {
  // noinspection TypeScriptValidateTypes
  filter.addRule(props.node, operand)
}

const isFilterTree = computed<boolean>(() => {
  return 'children' in props.node;
})

const isRoot = computed<boolean>(() => {
  // noinspection TypeScriptUnresolvedVariable
  return props.node.parentId === undefined;
})

const hasAndButton = computed<boolean>(()=> {
  // noinspection TypeScriptUnresolvedVariable
  return isRoot.value || props.node.operand === FilterOperand.And
})

const hasOrButton = computed<boolean>(()=> {
  // noinspection TypeScriptUnresolvedVariable
  return isRoot.value || props.node.operand === FilterOperand.Or
})

</script>

<style scoped>
.filter-tree {
  border-left-width: 3px;
  border-left-style: solid;
  box-shadow: 0 1px 5px rgb(0 0 0 / 20%), 0 2px 2px rgb(0 0 0 / 14%), 0 3px 1px -2px rgb(0 0 0 / 12%);
  border-radius: 3px;
  padding: 3px;
}

.filter-tree--and {
  border-left-color: var(--q-primary);
}

.filter-tree--or {
  border-left-color: var(--q-accent);
}
</style>
