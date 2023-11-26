<template>
  <q-card bordered class="q-mb-md bg-grey-12" flat>
    <q-card-section class="q-p-xs" horizontal>
      <q-card-section class="col">
        <div class="text-h6">{{ variety?.convar ?? t('components.varietyCard.noVariety') }}</div>
      </q-card-section>
      <q-card-actions class="justify-around col-auto" vertical>
        <q-btn
          class="q-mt-xs"
          color="primary"
          flat
          icon="search"
          size="sm"
          stack
          @click="$emit('change', $event)"
        >{{ t('components.util.search') }}
        </q-btn>
      </q-card-actions>
    </q-card-section>
  </q-card>
</template>

<script lang="ts" setup>
import {useI18n} from 'vue-i18n';
import {Variety} from 'src/models/variety';

defineProps<{
  variety?: Variety
}>()

defineEmits<{
  change: (event: Event) => void
}>()

const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
</script>
