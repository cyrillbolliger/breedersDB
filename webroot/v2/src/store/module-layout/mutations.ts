import { MutationTree } from 'vuex';
import {LayoutBreadcrumbsInterface, LayoutStateInterface} from './state';

const mutation: MutationTree<LayoutStateInterface> = {
  title (state: LayoutStateInterface, title: string) {
    state.title = title;
  },

  back (state: LayoutStateInterface, back: string|null) {
    state.back = back;
  },

  breadcrumbs (state: LayoutStateInterface, breadcrumbs: Array<LayoutBreadcrumbsInterface>) {
    state.breadcrumbs = breadcrumbs;
  },
};

export default mutation;
