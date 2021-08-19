import {LooseDictionary} from 'quasar/dist/types/ts-helpers';

export interface LayoutBreadcrumbsInterface {
  to? : string | LooseDictionary
  // exact? : boolean
  // replace? : boolean
  // activeClass? : string
  // exactActiveClass? : string
  disable? : boolean
  label? : string
}

export interface LayoutStateInterface {
  title: string;
  back: string | null,
  breadcrumbs: Array<LayoutBreadcrumbsInterface>,
}

function state(): LayoutStateInterface {
  return {
    title: '',
    back: null,
    breadcrumbs: [],
  };
}

export default state;
