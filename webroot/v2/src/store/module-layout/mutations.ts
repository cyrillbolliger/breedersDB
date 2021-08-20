import { MutationTree } from 'vuex';
import {LayoutBreadcrumbsInterface, LayoutStateInterface, LayoutTabsInterface} from './state';

const mutation: MutationTree<LayoutStateInterface> = {
  title (state: LayoutStateInterface, title: string) {
    state.title = title;
  },

  back (state: LayoutStateInterface, back: string|null) {
    state.back = back;
  },

  breadcrumbs (state: LayoutStateInterface, breadcrumbs: LayoutBreadcrumbsInterface[]) {
    state.breadcrumbs = breadcrumbs;
  },

  tabs (state: LayoutStateInterface, tabs: LayoutTabsInterface[]) {
    state.tabs = tabs;
  }
};

export default mutation;
