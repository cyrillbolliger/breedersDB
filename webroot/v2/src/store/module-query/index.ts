import { Module } from 'vuex';
import { StateInterface } from '../index';
import state, { QueryStateInterface } from './state';
import actions from './actions';
import getters from './getters';
import mutations from './mutations';

const queryModule: Module<QueryStateInterface, StateInterface> = {
  namespaced: true,
  actions,
  getters,
  mutations,
  state
};

export default queryModule;
