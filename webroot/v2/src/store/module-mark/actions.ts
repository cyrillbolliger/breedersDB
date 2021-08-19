import { ActionTree } from 'vuex';
import { StateInterface } from '../index';
import { MarkStateInterface } from './state';
import {MarkForm} from 'components/models';

const actions: ActionTree<MarkStateInterface, StateInterface> = {
  selectForm ({commit}, form: MarkForm) {
    commit('selectForm', form);
  }
};

export default actions;
