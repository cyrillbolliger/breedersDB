<template>
  <div class="row the-query-menu">
    <QueryTitle
      v-model="titleCode"
      class="q-pt-sm"
      @update:model-value="changed = true"
    />
    <div class="row the-query-menu__actions">
      <template
        v-if="!showMore"
      >
        <div
          v-if="changed"
          class="text-grey-8 q-px-sm"
        >{{t('queries.unsavedChanges')}}</div>
        <q-btn
          :color="changed ? 'primary' : 'grey-8'"
          :disable="!changed"
          flat
          icon="save"
          stack
          round
          :title="t('general.save')"
        />
      </template>
      <q-fab
        v-model="showMore"
        :flat="!showMore"
        direction="down"
        icon="more_vert"
        padding="sm"
        vertical-actions-align="right"
        :title="t('general.more')"
      >
        <q-fab-action
          :disable="!changed"
          :label="t('general.save')"
          :text-color="changed ? 'primary' : 'grey-8'"
          class="the-query-menu__fab-action"
          icon="save"
        />
        <q-fab-action
          :disable="unsaved"
          :label="t('queries.duplicate')"
          :text-color="unsaved ? 'grey-8': 'primary'"
          class="the-query-menu__fab-action"
          icon="content_copy"
        />
        <q-fab-action
          :disable="unsaved"
          :label="t('general.delete')"
          :text-color="unsaved ? 'grey-8':  'negative'"
          class="the-query-menu__fab-action"
          icon="delete_outline"
        />
      </q-fab>
    </div>
  </div>
</template>

<script lang="ts" setup>

import {computed, ref} from 'vue';
import {useI18n} from 'vue-i18n';
import QueryTitle from 'components/Query/Menu/QueryTitle.vue';

const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
const titleCode = ref('');
const changed = ref(false);
const showMore = ref(false);

const unsaved = computed(() => !changed.value); // todo

</script>

<style scoped>
.the-query-menu {
  justify-content: space-between;
}

.the-query-menu__actions {
  align-items: center;
}

.the-query-menu__fab-action {
  background: white !important;
  opacity: 1 !important;
}
</style>
