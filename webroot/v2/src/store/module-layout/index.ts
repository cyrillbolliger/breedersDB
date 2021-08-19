import { Module } from 'vuex';
import { StateInterface } from '../index';
import state, { LayoutStateInterface } from './state';
import actions from './actions';
import getters from './getters';
import mutations from './mutations';

const layoutModule: Module<LayoutStateInterface, StateInterface> = {
  namespaced: true,
  actions,
  getters,
  mutations,
  state
};

export default layoutModule;
