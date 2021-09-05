import { Module } from 'vuex';
import { StateInterface } from '../index';
import state, { MarkStateInterface } from './state';
import actions from './actions';
import getters from './getters';
import mutations from './mutations';

const markModule: Module<MarkStateInterface, StateInterface> = {
  namespaced: true,
  actions,
  getters,
  mutations,
  state
};

export default markModule;
