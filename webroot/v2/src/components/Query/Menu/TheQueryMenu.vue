<template>
  <div class="row no-wrap the-query-menu">
    <div class="row the-query-menu__title-path">
      <QueryGroup
        v-model:group="queryGroup"
        v-model:changed="queryGroupChanged"
        @update:changed="changed = true"
      />
      <span class="text-grey-8 the-query-menu__title-path-separator">/</span>
      <QueryName
        v-model:code="queryCode"
        v-model:changed="queryCodeChanged"
        @update:changed="changed = true"
      />
    </div>
    <div class="row wrap justify-center content-center">
      <div>
        <q-btn
          v-if="!loading"
          :color="changed ? 'primary' : 'grey-8'"
          :title="t('general.save')"
          flat
          icon="save"
          round
          stack
          @click="save"
        >
          <q-tooltip
            v-model="savedFlashMsg"
            no-parent-event
            anchor="top middle"
            self="center middle"
            transition-show="none"
            :transition-duration="2000"
            class="bg-transparent text-overline text-primary text-weight-bold text-uppercase"
          >{{ t('general.saved') }}</q-tooltip>
        </q-btn>
        <q-spinner
          v-else
          class="q-ma-sm"
          color="primary"
          size="1.5rem"
        />
      </div>
      <q-fab
        v-model="showMore"
        :disable="loading"
        :flat="!showMore"
        :title="t('general.more')"
        direction="down"
        icon="more_vert"
        padding="sm"
        vertical-actions-align="right"
      >
        <q-fab-action
          :label="t('general.save')"
          :text-color="changed ? 'primary' : 'grey-8'"
          class="the-query-menu__fab-action"
          icon="save"
          @click="save"
        />
        <q-fab-action
          :disable="unsaved"
          :label="t('queries.duplicate')"
          :text-color="unsaved ? 'grey-8': 'primary'"
          class="the-query-menu__fab-action"
          icon="content_copy"
          @click="duplicate"
        />
        <q-fab-action
          :disable="unsaved"
          :label="t('general.delete')"
          :text-color="unsaved ? 'grey-8':  'negative'"
          class="the-query-menu__fab-action"
          icon="delete_outline"
          @click="deleteQuery"
        />
      </q-fab>
    </div>
  </div>
</template>

<script lang="ts" setup>

import {ref} from 'vue';
import {useI18n} from 'vue-i18n';
import QueryName from 'components/Query/Menu/QueryName.vue';
import QueryGroup from 'components/Query/Menu/QueryGroup.vue';
import {useRoute} from 'vue-router';

const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
const route = useRoute();

const isNew = ref(route.params.id === 'new');
const unsaved = ref(isNew.value);
const changed = ref(isNew.value);
const queryGroupChanged = ref(isNew.value);
const queryCodeChanged = ref(isNew.value);

const queryCode = ref('');
const showMore = ref(false);
const loading = ref(false);
const savedFlashMsg = ref(false);

const queryGroup = ref<QueryGroup>(); // todo

function save() {
  loading.value = true;

  // todo

  changed.value = false;
  queryGroupChanged.value = false;
  queryCodeChanged.value = false;
  unsaved.value = false;
  loading.value = false;
  showSavedFlashMsg();
}

function showSavedFlashMsg() {
  savedFlashMsg.value = true;
  window.setTimeout(() => savedFlashMsg.value = false, 800);
}

function duplicate() {
  loading.value = ! loading.value;
  // todo
}

function deleteQuery() {
  loading.value = ! loading.value;
  // todo
}

</script>

<style scoped>
.the-query-menu {
  justify-content: space-between;
}

.the-query-menu__title-path {
  align-items: center;
  font-size: 1.5rem;
}

.the-query-menu__title-path-separator {
  padding: 0 1rem;
  font-size: 2rem;
}

.the-query-menu__fab-action {
  background: white !important;
  opacity: 1 !important;
}
</style>
