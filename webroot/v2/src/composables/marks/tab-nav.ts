import {useStore} from 'src/store';
import {i18n} from 'src/boot/i18n'

import {computed} from 'vue';
import {LayoutTabsInterface} from 'src/models/layout';

export default function useMarkTabNav() {
  const store = useStore()
  const t = i18n.global.t // eslint-disable-line @typescript-eslint/unbound-method

  const disableSetTreeTab = computed<boolean>(() => {
    /* eslint-disable @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-return */
    return !store.getters['mark/selectedForm']
      || !store.getters['mark/author']
      || !store.getters['mark/date']
    /* eslint-enable @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-return */
  });

  const disableMarkTab = computed<boolean>(() => {
    return disableSetTreeTab.value
      || !store.getters['mark/tree'] // eslint-disable-line
  });

  const tabs: LayoutTabsInterface[] = [
    {label: t('marks.selectForm.tab'), to: '/marks/select-form'},
    {label: t('marks.setMeta.tab'), to: '/marks/set-meta'},
    {label: t('marks.selectTree.tab'), to: '/marks/select-tree', disable: disableSetTreeTab.value},
    {label: t('marks.markTree.tab'), to: '/marks/mark-tree', disable: disableMarkTab.value},
  ];

  return tabs
}

