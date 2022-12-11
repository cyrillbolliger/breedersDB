import {useLayoutStore} from 'stores/layout';
import {LayoutBreadcrumbsInterface, LayoutTabsInterface} from 'src/models/layout';

export default function useLayout() {
  const store = useLayoutStore();

  function setToolbarTitle(title: string) {
    store.title = title
  }

  function setToolbarTabs(tabs: LayoutTabsInterface[]) {
    store.tabs = tabs;
  }

  function setToolbarBreadcrumbs(breadcrumbs: LayoutBreadcrumbsInterface[]) {
    store.breadcrumbs = breadcrumbs
  }

  return {
    setToolbarTitle,
    setToolbarTabs,
    setToolbarBreadcrumbs,
  }
}
