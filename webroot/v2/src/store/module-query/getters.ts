import { GetterTree } from 'vuex';
import { StateInterface } from '../index';
import { QueryStateInterface } from './state';

const getters: GetterTree<QueryStateInterface, StateInterface> = {
  base (state: QueryStateInterface) {
    return state.base
  }
};

export default getters;
