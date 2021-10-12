import {ActionTree} from 'vuex';
import {StateInterface} from '../index';
import {MarkStateInterface, localStorageAuthor} from './state';
import {MarkForm} from 'src/models/form';
import {Tree} from 'src/models/tree';

const actions: ActionTree<MarkStateInterface, StateInterface> = {
  selectForm({commit}, form: MarkForm) {
    commit('selectForm', form);
  },

  author({commit}, author: string) {
    commit('author', author);
    window.localStorage.setItem(localStorageAuthor, author);
  },

  date({commit}, date: string) {
    commit('date', date)
  },

  tree({commit}, tree: Tree) {
    commit('tree', tree)
  }
};

export default actions;
