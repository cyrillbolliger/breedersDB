import {i18n} from 'src/boot/i18n'

import {computed} from 'vue';
import {LayoutTabsInterface} from 'src/models/layout';
import {useMarkStore} from 'stores/mark';

export default function useMarkTabNav() {
  const store = useMarkStore()
  const t = i18n.global.t // eslint-disable-line @typescript-eslint/unbound-method

  const disableSetTreeTab = computed<boolean>(() => {
    return !store.selectedForm
      || !store.author
      || !store.date
  });

  const disableMarkTab = computed<boolean>(() => {
    return disableSetTreeTab.value
      || !store.tree
  });

  const tabs: LayoutTabsInterface[] = [
    {label: t('marks.selectForm.tab'), to: '/marks/select-form'},
    {label: t('marks.setMeta.tab'), to: '/marks/set-meta'},
    {label: t('marks.selectTree.tab'), to: '/marks/select-tree', disable: disableSetTreeTab.value},
    {label: t('marks.markTree.tab'), to: '/marks/mark-tree', disable: disableMarkTab.value},
  ];

  return tabs
}

