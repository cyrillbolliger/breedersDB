<template>
  <div
    v-if="isSimplifiable"
  >
    <q-icon name="warning" />
    {{t('queries.simplifiable')}}
    <button
      @click="simplify()"
    >
      {{t('queries.simplify')}}
    </button>
  </div>
  <FilterTree
    :node="filter"
    :options="options"
    :operand="filter.getChildrensOperand()"
  />
</template>

<script setup lang="ts">
import FilterTree from 'components/Query/FilterTree.vue';
import {FilterDataType} from 'src/models/query/filterTypes';
import {computed, PropType} from 'vue';
import {FilterNode} from 'src/models/query/filterNode';
import {useI18n} from 'vue-i18n';

const {t} = useI18n(); // eslint-disable-line @typescript-eslint/unbound-method

const props = defineProps({
  filter: {
    type: Object as PropType<FilterNode>,
    required: true
  }
});

// noinspection TypeScriptUnresolvedFunction
const isSimplifiable = computed(() => props.filter.isSimplifiable());

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
  div {
    color: var(--q-accent);
  }

  button {
    color: var(--q-accent);
    background: none;
    padding: 0;
    border: none;
    text-decoration: underline;
    cursor: pointer;
  }

  button:hover,
  button:focus {
    filter: brightness(125%);
    text-decoration: none;
  }
</style>
