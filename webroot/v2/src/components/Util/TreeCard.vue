<template>
  <q-card class="bg-grey-4 q-mb-md">
    <q-card-section class="q-pt-xs">
      <div class="row">
        <div class="col">
          <div class="text-overline text-weight-regular">{{ t('components.util.treeCard.selected') }}</div>
          <div class="text-h6">{{ tree?.convar }}</div>
          <div class="text-caption"><q-icon name="tag"/>&nbsp;{{ tree?.publicid }}</div>
        </div>
        <div class="col-auto">
          <q-btn
            class="q-pa-sm"
            size="sm"
            flat
            color="primary"
            dense
            @click="$emit('click', $event)"
          >{{ t('components.util.treeCard.change') }}</q-btn>
        </div>
      </div>
    </q-card-section>
  </q-card>
</template>

<script lang="ts">
import {defineComponent, PropType} from 'vue'
import {useI18n} from "vue-i18n";
import {Tree} from "src/models/tree";

export default defineComponent({
  name: 'TreeCard',

  emits: ['change'],

  props: {
    tree: {
      type: Object as PropType<Tree>
    }
  },

  setup() {
    const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method

    return {
      t
    }
  }
})
</script>
