<template>
  <q-card class="q-mb-md bg-grey-12" flat bordered>
    <q-card-section horizontal class="q-p-xs">
      <q-card-section class="col">
        <div class="text-h6">{{ tree?.convar ?? t('components.util.treeCard.noTree') }}</div>
        <div class="text-caption" v-if="tree">
          {{ t('components.util.treeCard.tree') }}: {{ tree.publicid }}
        </div>
      </q-card-section>
      <q-card-actions vertical class="justify-around col-auto">
        <q-btn
          size="sm"
          class="q-mt-xs"
          flat
          color="primary"
          icon="qr_code_scanner"
          stack
          @click="$emit('change', $event)"
        >{{ t('components.util.treeCard.scanBtnLabel') }}
        </q-btn>
        <q-btn
          v-if="printable"
          size="sm"
          class="q-mt-xs"
          flat
          color="primary"
          icon="print"
          stack
          @click="printDialog = true"
        >{{ t('components.util.treeCard.printBtnLabel') }}
        </q-btn>
      </q-card-actions>
    </q-card-section>
  </q-card>

  <q-dialog v-model="printDialog" persistent>
    <q-card>
      <q-card-section class="row items-center">
        <q-avatar icon="print" color="primary" text-color="white" class="q-mr-md"/>
        <div class="text-h6">{{t('components.util.treeCard.printTitle')}}</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>

      <q-card-section class="q-pt-none">
        {{t('components.util.treeCard.printDesc')}}
      </q-card-section>

      <q-card-actions align="right">
        <q-btn
          flat
          :label="t('components.util.treeCard.printAnonymous')"
          color="primary"
          v-close-popup
          @click="print(tree.print.anonymous)"
        />
        <q-btn
          flat
          :label="t('components.util.treeCard.printRegular')"
          color="primary"
          v-close-popup
          @click="print(tree.print.regular)"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script lang="ts">
import {computed, defineComponent, PropType, ref} from 'vue'
import {useI18n} from 'vue-i18n';
import {Tree} from 'src/models/tree';

export default defineComponent({
  name: 'TreeCard',

  emits: ['change'],

  props: {
    tree: {
      type: Object as PropType<Tree>
    }
  },

  setup(props) {
    const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method

    const printDialog = ref(false);
    const printable = computed(() => undefined !== props?.tree?.print);

    function print(zpl: string) {
      const printWindow = window.open();
      if (! printWindow) {
        alert(t('components.util.treeCard.windowError'))
        return;
      }
      printWindow.document.open('text/plain');
      printWindow.document.write(zpl);
      printWindow.document.close();
      printWindow.focus();
      printWindow.print();
      printWindow.close();
    }

    return {
      t,
      printDialog,
      printable,
      print
    }
  }
})
</script>
