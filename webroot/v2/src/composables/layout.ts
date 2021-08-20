import {LayoutBreadcrumbsInterface, LayoutTabsInterface} from 'src/store/module-layout/state';
import {useStore} from 'src/store';

export default function useLayout() {
  const store = useStore();

  function setToolbarTitle(title: string) {
    void store.dispatch('layout/title', title)
  }

  function setToolbarTabs(tabs: LayoutTabsInterface[]) {
    void store.dispatch('layout/tabs', tabs)
  }

  function setToolbarBreadcrumbs(breadcrumbs: LayoutBreadcrumbsInterface[]) {
    void store.dispatch('layout/breadcrumbs', breadcrumbs)
  }

  return {
    setToolbarTitle,
    setToolbarTabs,
    setToolbarBreadcrumbs,
  }
}
