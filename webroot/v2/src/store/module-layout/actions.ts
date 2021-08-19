import { ActionTree } from 'vuex';
import { StateInterface } from '../index';
import {LayoutBreadcrumbsInterface, LayoutStateInterface} from './state';

const actions: ActionTree<LayoutStateInterface, StateInterface> = {
  title ({commit}, title: string): void {
    commit('title', title);
  },

  back ({commit}, back: string|null): void {
    commit('back', back);
  },

  breadcrumbs ({commit}, breadcrumbs: Array<LayoutBreadcrumbsInterface>): void {
    commit('breadcrumbs', breadcrumbs);
  }
};

export default actions;
