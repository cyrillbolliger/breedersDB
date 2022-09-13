import {GetterTree} from 'vuex';
import {StateInterface} from '../index';
import {QueryStateInterface} from './state';

const getters: GetterTree<QueryStateInterface, StateInterface> = {
  base(state: QueryStateInterface) {
    return state.base
  },

  baseFilter(state: QueryStateInterface) {
    return state.baseFilter
  },

  markFilter(state: QueryStateInterface) {
    return state.markFilter
  },

  lastFilterId(state: QueryStateInterface) {
    return state.lastFilterId
  },

  dragObject(state: QueryStateInterface) {
    return state.dragObject
  },
};

export default getters;
