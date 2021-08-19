import { GetterTree } from 'vuex';
import { StateInterface } from '../index';
import {LayoutBreadcrumbsInterface, LayoutStateInterface} from './state';

const getters: GetterTree<LayoutStateInterface, StateInterface> = {
  title(state): string {
    return state.title;
  },

  back (state): string|null {
    return state.back;
  },

  breadcrumbs(state): Array<LayoutBreadcrumbsInterface>{
    return state.breadcrumbs;
  },
};

export default getters;
