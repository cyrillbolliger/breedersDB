<template>
  <div
    class="filter-tree"
    :class="{'filter-tree--dragging': !!dragging}"
    :draggable="!!dragging"
    @dragstart="dragStart"
    @dragend="dragEnd"
  >
    <div
      class="filter-rule__drop filter-rule__drop--before"
      @dragleave="mouseInDropZoneAbove = false"
      @dragenter="mouseInDropZoneAbove = true"
      @dragover.prevent="$event.dataTransfer.dropEffect = 'move'"
      @drop.prevent="onDrop('before')"
      :class="{
        'filter-rule__drop--hover': canDropAbove,
        'filter-rule__drop--active': dragActive && canBeTarget,
        'filter-rule__drop--and': dropOperand === FilterOperand.And,
        'filter-rule__drop--or': dropOperand === FilterOperand.Or,
      }"
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

    <div
      class="filter-rule__drop filter-rule__drop--after"
      @dragleave="mouseInDropZoneBelow = false"
      @dragenter="mouseInDropZoneBelow = true"
      @dragover.prevent="$event.dataTransfer.dropEffect = 'move'"
      @drop.prevent="onDrop('after')"
      :class="{
        'filter-rule__drop--hover': canDropBelow,
        'filter-rule__drop--active': dragActive && canBeTarget,
        'filter-rule__drop--and': dropOperand === FilterOperand.And,
        'filter-rule__drop--or': dropOperand === FilterOperand.Or,
      }"
    />
  </div>
</template>

<script setup lang="ts">
import {computed, PropType, ref, watch} from 'vue';
import useFilter from 'src/composables/queries/filter';
import {useI18n} from 'vue-i18n';
import FilterRule from 'src/components/Query/FilterRule.vue'
import FilterRuleButtonAdd from 'src/components/Query/FilterRuleButtonAdd.vue';
import {FilterNode} from 'src/models/query/filterNode';
import {FilterOperand, FilterOption} from 'src/models/query/filterTypes';
import {useQueryStore} from 'stores/query';
import {FilterDragNode} from 'src/models/query/query';

const props = defineProps({
  node: {
    type: Object as PropType<FilterNode>,
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
const store = useQueryStore();

const mouseInDropZoneAbove = ref(false);
const mouseInDropZoneBelow = ref(false);
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

const canDropAbove = computed(() => {
  return mouseInDropZoneAbove.value && canBeTarget.value
})

const canDropBelow = computed(() => {
  return mouseInDropZoneBelow.value && canBeTarget.value
})

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

  setDragObj(false);
}

watch(dragObj, dragObj => {
  if (false === dragObj) {
    mouseInDropZoneAbove.value = false;
    mouseInDropZoneBelow.value = false;
  }
})

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

.filter-rule__drop {
  height: 18px;
  width: 100%;
  opacity: 0.25;
  display: none;
  position: absolute;
  left: 0;
  z-index: -1;
}

.filter-rule__drop--before {
  top: -18px;
}

.filter-rule__drop--after {
  bottom: -18px;
}

.filter-rule__drop--active {
  display: block;
  z-index: 10;
}

.filter-rule__drop--and {
  background-color: var(--q-primary);
}

.filter-rule__drop--or {
  background-color: var(--q-accent);
}

.filter-rule__drop--hover {
  opacity: 1;
}
</style>
