import {useStore} from 'src/store';
import {FilterLeaf, FilterOperand, FilterTreeRoot} from 'src/store/module-query/state';

export default function useFilter() {
  const store = useStore();

  function addRule(parent: FilterTreeRoot, operand: FilterOperand) {
    store.commit('query/incrementFilterId');

    const node: FilterLeaf = {
      id: (store.getters['query/lastFilterId'] as number), // eslint-disable-line @typescript-eslint/no-unsafe-member-access
      level: parent.level + 1,
      type: parent.type,
      parentId: parent.id,
      filter: {
        column: undefined,
        comparator: undefined,
        criteria: undefined,
      }
    }

    store.commit('query/addRule', {node, operand});
  }

  function getBaseFilter(): FilterTreeRoot {
    return store.getters['query/baseFilter'] as FilterTreeRoot // eslint-disable-line @typescript-eslint/no-unsafe-member-access
  }

  function getMarkFilter(): FilterTreeRoot {
    return store.getters['query/markFilter'] as FilterTreeRoot // eslint-disable-line @typescript-eslint/no-unsafe-member-access
  }

  return {
    getBaseFilter,
    getMarkFilter,
    addRule,
  }
}
