import { ActionTree } from 'vuex';
import { StateInterface } from '../index';
import {LayoutBreadcrumbsInterface, LayoutStateInterface, LayoutTabsInterface} from './state';

const actions: ActionTree<LayoutStateInterface, StateInterface> = {
  title ({commit}, title: string) {
    commit('title', title);
  },

  back ({commit}, back: string|null) {
    commit('back', back);
  },

  breadcrumbs ({commit}, breadcrumbs: LayoutBreadcrumbsInterface[]) {
    commit('breadcrumbs', breadcrumbs);
  },

  tabs( {commit}, tabs: LayoutTabsInterface[]) {
    commit('tabs', tabs)
  },
};

export default actions;
