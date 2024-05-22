<template>
  <q-card bordered class="q-mb-md bg-grey-12" flat>
    <q-card-section class="q-p-xs" horizontal>
      <q-card-section class="col">
        <div class="text-h6">{{ tree?.convar ?? t('components.treeCard.noTree') }}</div>
        <div v-if="tree" class="text-subtitle1">
          {{ t('components.treeCard.tree') }}:
          <span class="text-grey-8">{{ (tree.publicid.match(/^0*/) || [''])[0] }}</span><strong>{{ (tree.publicid.match(/^0*(\d+)$/) || ['',''])[1] }}</strong>
        </div>
      </q-card-section>
      <q-card-actions class="justify-around col-auto" vertical>
        <q-btn
          class="q-mt-xs"
          color="primary"
          flat
          icon="qr_code_scanner"
          size="sm"
          stack
          @click="$emit('change', $event)"
        >{{ t('components.treeCard.scanBtnLabel') }}
        </q-btn>
        <q-btn
          v-if="printable"
          class="q-mt-xs"
          color="primary"
          flat
          icon="print"
          size="sm"
          stack
          @click="printDialog = true"
        >{{ t('components.treeCard.printBtnLabel') }}
        </q-btn>
      </q-card-actions>
    </q-card-section>
  </q-card>

  <q-dialog v-model="printDialog">
    <q-card>
      <q-card-section class="row items-center">
        <q-avatar class="q-mr-md" color="primary" icon="print" text-color="white"/>
        <div class="text-h6">{{ t('components.treeCard.printTitle') }}</div>
        <q-space/>
        <q-btn v-close-popup dense flat icon="close" round/>
      </q-card-section>

      <q-card-section class="q-pt-none">
        {{ t('components.treeCard.printDesc') }}
      </q-card-section>

      <q-card-actions align="right">
        <q-btn
          v-close-popup
          :label="t('components.treeCard.printAnonymous')"
          color="primary"
          flat
          @click="print(tree?.print?.anonymous)"
        />
        <q-btn
          v-close-popup
          :label="t('components.treeCard.printRegular')"
          color="primary"
          flat
          @click="print(tree?.print?.regular)"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script lang="ts" setup>
import {computed, ref} from 'vue'
import {useI18n} from 'vue-i18n';
import {Tree} from 'src/models/tree';
import {useQuasar} from 'quasar';
const $q = useQuasar()

const props = defineProps<{
  tree?: Tree
}>()

defineEmits<{
  change: (event: Event) => void
}>()

const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method

const printDialog = ref(false);
const printable = computed(() => undefined !== props?.tree?.print);

function print(zpl: string | undefined) {
  if ( ! zpl) {
    $q.notify({
      message: t('components.treeCard.noPrint'),
      multiLine: true,
      timeout: 30000,
      closeBtn: true,
    });
    return;
  }

  const printWindow = window.open();
  if ( ! printWindow) {
    alert(t('components.treeCard.windowError'))
    return;
  }
  printWindow.document.open('text/plain');
  printWindow.document.write(zpl);
  printWindow.document.close();
  printWindow.focus();
  printWindow.print();
  printWindow.close();
}
</script>
