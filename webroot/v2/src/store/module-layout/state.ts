export interface LayoutBreadcrumbsInterface {
  to? : any // eslint-disable-line @typescript-eslint/no-explicit-any
  disable? : boolean
  label? : string
  icon? : string
}

export interface LayoutTabsInterface {
  to? : any // eslint-disable-line @typescript-eslint/no-explicit-any
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
