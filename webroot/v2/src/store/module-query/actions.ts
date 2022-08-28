import {ActionTree} from 'vuex';
import {StateInterface} from '../index';
import {BaseTable, QueryStateInterface} from './state';

const actions: ActionTree<QueryStateInterface, StateInterface> = {
  base({commit}, base: BaseTable) {
    commit('base', base)
  }
};

export default actions;
