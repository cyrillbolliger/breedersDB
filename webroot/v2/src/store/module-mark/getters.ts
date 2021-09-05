import { GetterTree } from 'vuex';
import { StateInterface } from '../index';
import { MarkStateInterface } from './state';

const getters: GetterTree<MarkStateInterface, StateInterface> = {
  selectedForm (state) {
    return state.selectedForm
  },
  author (state) {
    return state.author
  },
  date (state) {
    return state.date
  },
  tree (state) {
    return state.tree
  }
};

export default getters;
