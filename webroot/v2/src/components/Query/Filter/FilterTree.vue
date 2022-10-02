<template>
  <div
    class="filter-tree"
    :class="{'filter-tree--dragging': !!dragging}"
    :draggable="!!dragging"
    @dragstart="dragStart"
    @dragend="dragEnd"
  >
    <FilterRuleDropZone
      :active="dragActive && canBeTarget"
      :color="dropOperand === FilterOperand.And ? 'primary' : 'accent'"
      :dragging="!!dragObj"
      @drop.prevent="onDrop('before')"
      class="filter-rule__drop--before"
    />

    <FilterRule
      v-if="node.isLeaf()"
      :options="options"
      :node="node"
      :operand="operand"
      @drag-mouse-down="setDragObj(node)"
      @drag-mouse-up="setDragObj(false)"
    />

    <div
      v-else
    >
      <div class="row items-stretch">
        <div
          class="filter-tree__drag-bg row items-center"
          :class="{
          'filter-tree__drag-bg--and': operand === FilterOperand.And,
          'filter-tree__drag-bg--or': operand === FilterOperand.Or,
          'filter-tree__drag-bg--root': node.isRoot(),
        }"
        >
          <q-icon
            name="drag_indicator"
            size="md"
            class="filter-tree__drag-handle"
            @mousedown="setDragObj(node)"
            @mouseup="setDragObj(false)"
          />
        </div>
        <div class="col">
          <template
            v-for="(tree, idx) in node.getChildren()"
            :key="tree.getId()"
          >
            <FilterTree
              :node="tree"
              :options="options"
              :operand="tree.getChildrensOperand() || node.getChildrensOperand()"
            />
            <div
              class="filter-tree__operand"
              :class="{
              'filter-tree__operand--and': operand === FilterOperand.And,
              'filter-tree__operand--or': operand === FilterOperand.Or
            }"
              v-if="idx+1 < node.getChildCount()"
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
      <FilterRuleButtonAdd
        :operand="operand"
        :node="node"
      />
    </div>

    <FilterRuleDropZone
      :active="dragActive && canBeTarget"
      :color="dropOperand === FilterOperand.And ? 'primary' : 'accent'"
      :dragging="!!dragObj"
      @drop.prevent="onDrop('after')"
      class="filter-rule__drop--after"
    />
  </div>
</template>

<script setup lang="ts">
import {computed, PropType, ref} from 'vue';
import useFilterNodeActions from 'src/composables/queries/filterNodeActions';
import {useI18n} from 'vue-i18n';
import FilterRule from 'components/Query/Filter/FilterRule.vue'
import FilterRuleButtonAdd from 'components/Query/Filter/FilterRuleButtonAdd.vue';
import {FilterNode} from 'src/models/query/filterNode';
import {FilterOperand} from 'src/models/query/filterTypes';
import {useQueryStore} from 'stores/query';
import {FilterDragNode} from 'src/models/query/query';
import FilterRuleDropZone from 'components/Query/Filter/FilterRuleDropZone.vue'
import {PropertySchema} from 'src/models/query/filterOptionSchema';

const props = defineProps({
  node: {
    type: Object as PropType<FilterNode>,
    required: true,
  },
  options: {
    type: Object as PropType<PropertySchema[]>,
    required: true,
  },
  operand: {
    type: String as PropType<FilterOperand>,
    required: true,
  },
});

const filter = useFilterNodeActions();
const {t} = useI18n(); // eslint-disable-line @typescript-eslint/unbound-method
const store = useQueryStore();

const dragging = ref<FilterDragNode>(false);

const dragObj = computed(() => {
  return store.filterDragNode
})

const dragActive = computed(() => {
  // noinspection TypeScriptUnresolvedFunction
  const currentNodeType = props.node.getFilterType();
  // noinspection TypeScriptUnresolvedFunction
  const draggedNodeType = dragObj.value ? dragObj.value.getFilterType() : false;
  return currentNodeType === draggedNodeType;
});

const canBeTarget = computed(() => {
  const subject = dragObj.value;
  const target = props.node;

  // noinspection TypeScriptUnresolvedFunction
  return ! target.isDescendantOf(subject) && subject !== target && ! target.isRoot();
});

const dropOperand = computed(() => {
  // noinspection TypeScriptUnresolvedFunction
  if (props.node.isLeaf()) {
    return props.operand;
  }

  // noinspection TypeScriptUnresolvedFunction
  return props.node.getParent()?.getChildrensOperand();
});

function setDragObj(node: FilterDragNode) {
  dragging.value = node;
  store.filterDragNode = node;
}

function dragStart(event: DragEvent) {
  event.dataTransfer.effectAllowed = 'move';
  event.dataTransfer.dropEffect = 'move';
}

function dragEnd() {
  setDragObj(false);
}

function onDrop(position: 'before' | 'after') {
  if (canBeTarget.value) {
    // noinspection TypeScriptValidateTypes
    filter.moveNode(
      dragObj.value,
      props.node,
      position
    );
  }

  dragEnd();
}

</script>

<style scoped>
.filter-tree {
  position: relative;
}

.filter-tree--dragging {
  opacity: 0.4;
}

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

.filter-rule__drop--before {
  top: -18px;
}

.filter-rule__drop--after {
  bottom: -18px;
}
</style>
