import { GetterTree } from 'vuex';
import { StateInterface } from '../index';
import {LayoutBreadcrumbsInterface, LayoutStateInterface, LayoutTabsInterface} from './state';

const getters: GetterTree<LayoutStateInterface, StateInterface> = {
  title(state): string {
    return state.title;
  },

  back (state): string|null {
    return state.back;
  },

  breadcrumbs(state): LayoutBreadcrumbsInterface[]{
    return state.breadcrumbs;
  },

  tabs(state): LayoutTabsInterface[] {
    return state.tabs
  }
};

export default getters;
