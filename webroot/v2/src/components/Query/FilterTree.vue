<template>
  <div
    v-if="isFilterTree"
    class="filter-tree"
  >
    <div class="row items-stretch">
      <div
        class="filter-tree__drag-bg row items-center"
        :class="{
          'filter-tree__drag-bg--and': operand === FilterOperand.And,
          'filter-tree__drag-bg--or': operand === FilterOperand.Or,
          'filter-tree__drag-bg--root': node.level === 0,
        }"
      >
        <q-icon
          name="drag_indicator"
          size="md"
          class="filter-tree__drag-handle"
        />
      </div>
      <div class="col">
        <template
          v-for="(tree, idx) in node.children"
          :key="tree.id"
        >
          <FilterTree
            :node="tree"
            :options="options"
            :operand="tree.operand || node.operand"
          />
          <div
            class="filter-tree__operand"
            :class="{
              'filter-tree__operand--and': operand === FilterOperand.And,
              'filter-tree__operand--or': operand === FilterOperand.Or
            }"
            v-if="idx+1 < node.children.length"
          >
            {{
              operand === FilterOperand.And
                ? t('queries.filter.and')
                : t('queries.filter.or')
            }}
          </div>
        </template>
      </div>
    </div>
    <q-fab
      :label="t('queries.filter.add')"
      :color="operand === FilterOperand.And ? 'primary' : 'accent'"
      icon="add"
      direction="down"
      v-model="actionsVisible"
      unelevated
      padding="xs"
      :hide-label="!actionButtonHover && !actionsVisible"
      @mouseenter="actionButtonHover = true"
      @mouseleave="actionButtonHover = false"
      class="filter-tree__action-btn"
      :class="{'filter-tree__action-btn--root': node.level === 0}"
      vertical-actions-align="left"
    >
      <q-fab-action
        v-if="hasAndButton"
        class=""
        :label="t('queries.filter.andFilter')"
        color="primary"
        @click="addRule(FilterOperand.And)"
        padding="xs"
      />
      <q-fab-action
        v-if="hasOrButton"
        class=""
        :label="t('queries.filter.orFilter')"
        color="accent"
        @click="addRule(FilterOperand.Or)"
        padding="xs"
      />
    </q-fab>
  </div>

  <FilterRule
    v-else
    :options="options"
    :node="node"
    :operand="operand"
  />
</template>

<script setup lang="ts">
import {computed, PropType, ref} from 'vue';
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

const actionsVisible = ref(false);
const actionButtonHover = ref(false);

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

const hasAndButton = computed<boolean>(() => {
  // noinspection TypeScriptUnresolvedVariable
  return isRoot.value || props.node.operand === FilterOperand.And
})

const hasOrButton = computed<boolean>(() => {
  // noinspection TypeScriptUnresolvedVariable
  return isRoot.value || props.node.operand === FilterOperand.Or
})

</script>

<style scoped>
.filter-tree__drag-bg {
  border-right-width: 3px;
  border-right-style: solid;
  background: #EFEFEF;
}

.filter-tree__drag-bg--and {
  border-color: var(--q-primary);
}

.filter-tree__drag-bg--or {
  border-color: var(--q-accent);
}

.filter-tree__drag-bg--root {
  width: 0;
  overflow: hidden;
}

.filter-tree__drag-handle {
  color: rgba(0, 0, 0, 0.6);
  cursor: grab;
}

.filter-tree__drag-handle:hover {
  color: var(--q-primary);
}

.filter-tree__action-btn {
  transform: translateX(18px);
}

.filter-tree__action-btn--root {
  transform: translateX(-15px);
}

.filter-tree__operand {
  text-transform: uppercase;
  font-size: 0.75rem;
  margin-left: 5px;
  font-weight: bold;
}

.filter-tree__operand--and {
  color: var(--q-primary);
}

.filter-tree__operand--or {
  color: var(--q-accent);
}
</style>
