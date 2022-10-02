<template>
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
    :class="{'filter-tree__action-btn--root': node.isRoot()}"
    vertical-actions-align="left"
  >
    <q-fab-action
      :label="t('queries.filter.andFilter')"
      color="primary"
      @click="addLeaf(FilterOperand.And)"
      padding="xs"
    />
    <q-fab-action
      :label="t('queries.filter.orFilter')"
      color="accent"
      @click="addLeaf(FilterOperand.Or)"
      padding="xs"
    />
  </q-fab>
</template>

<script setup lang="ts">
import {PropType, ref} from 'vue';
import {useI18n} from 'vue-i18n';
import {FilterOperand} from 'src/models/query/filterTypes';
import {FilterNode} from 'src/models/query/filterNode';
import useFilterNodeActions from 'src/composables/queries/filterNodeActions';

const props = defineProps({
  operand: {
    type: String as PropType<FilterOperand>,
    required: true,
  },
  node: {
    type: Object as PropType<FilterNode>,
    required: true,
  }
});

const {t} = useI18n(); // eslint-disable-line @typescript-eslint/unbound-method
const filter = useFilterNodeActions();

const actionsVisible = ref(false);
const actionButtonHover = ref(false);


function addLeaf(operand: FilterOperand) {
  // noinspection TypeScriptValidateTypes
  filter.addLeaf(props.node, operand)
}
</script>

<style scoped>
.filter-tree__action-btn {
  transform: translateX(18px);
}

.filter-tree__action-btn--root {
  transform: translateX(-15px);
}
</style>
