import {MutationTree} from 'vuex';
import {BaseTable, QueryStateInterface} from './state';

const mutation: MutationTree<QueryStateInterface> = {
  base(state: QueryStateInterface, base: BaseTable) {
    state.base = base
  }
};

export default mutation;
