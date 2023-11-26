import {i18n} from 'src/boot/i18n'

import {computed} from 'vue';
import {LayoutTabsInterface} from 'src/models/layout';
import {useMarkStore} from 'stores/mark';
import useMarkType from 'src/composables/marks/type';

export default function useMarkTabNav() {
  const type = useMarkType()
  const store = useMarkStore()
  const t = i18n.global.t // eslint-disable-line @typescript-eslint/unbound-method

  const disableSetObjTab = computed<boolean>(() => {
    return ! store.selectedForm
      || ! store.author
      || ! store.date
  });

  const disableMarkTab = computed<boolean>(() => {
    return disableSetObjTab.value
      || ! store.tree // todo
  });

  return computed<LayoutTabsInterface[]>(() => {
    const tabs: LayoutTabsInterface[] =
      [
        {label: t('marks.selectForm.tab'), to: `/marks/${type.value}/select-form`},
        {label: t('marks.setMeta.tab'), to: `/marks/${type.value}/set-meta`},
      ]

    if (type.value === 'batch') {
      tabs.push(
        {label: t('marks.selectBatch.tab'), to: '/marks/batch/select-batch', disable: disableSetObjTab.value},
        {label: t('marks.markBatch.tab'), to: '/marks/batch/mark-batch', disable: disableMarkTab.value},
      );
    } else if (type.value === 'variety') {
      tabs.push(
        {label: t('marks.selectVariety.tab'), to: '/marks/variety/select-variety', disable: disableSetObjTab.value},
        {label: t('marks.markVariety.tab'), to: '/marks/variety/mark-variety', disable: disableMarkTab.value},
      );
    } else {
      tabs.push(
        {label: t('marks.selectTree.tab'), to: '/marks/tree/select-tree', disable: disableSetObjTab.value},
        {label: t('marks.markTree.tab'), to: '/marks/tree/mark-tree', disable: disableMarkTab.value},
      );
    }

    return tabs;
  })
}

