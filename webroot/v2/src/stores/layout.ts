import { defineStore } from 'pinia';
import {LayoutBreadcrumbsInterface, LayoutTabsInterface} from 'src/models/layout';

export interface LayoutState {
  title: string;
  back: string | null,
  breadcrumbs: LayoutBreadcrumbsInterface[],
  tabs: LayoutTabsInterface[],
}

export const useLayoutStore = defineStore('layout', {
  state: (): LayoutState => ({
    title: '',
    back: null,
    breadcrumbs: [],
    tabs: [],
  }),
});
