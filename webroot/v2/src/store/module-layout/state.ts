import {LooseDictionary} from 'quasar/dist/types/ts-helpers';

export interface LayoutBreadcrumbsInterface {
  to? : string | LooseDictionary
  disable? : boolean
  label? : string
  icon? : string
}

export interface LayoutTabsInterface {
  to? : string | LooseDictionary
  disable? : boolean
  label? : string
  icon? : string
}

export interface LayoutStateInterface {
  title: string;
  back: string | null,
  breadcrumbs: LayoutBreadcrumbsInterface[],
  tabs: LayoutTabsInterface[],
}

function state(): LayoutStateInterface {
  return {
    title: '',
    back: null,
    breadcrumbs: [],
    tabs: [],
  };
}

export default state;
