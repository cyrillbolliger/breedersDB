import {ActionTree} from 'vuex';
import {StateInterface} from '../index';
import {BaseTable, FilterDragObject, FilterTreeRoot, QueryStateInterface} from './state';

const actions: ActionTree<QueryStateInterface, StateInterface> = {
  base({commit}, base: BaseTable) {
    commit('base', base)
  },

  baseFilter({commit}, baseFilter: FilterTreeRoot) {
    commit('baseFilter', baseFilter)
  },

  markFilter({commit}, markFilter: FilterTreeRoot) {
    commit('markFilter', markFilter)
  },

  incrementFilterId({commit}) {
    commit('incrementFilterId')
  },

  dragObject({commit}, state: FilterDragObject) {
    commit('dragObject', state)
  }
};

export default actions;
