<template>
  <div class="row wrap justify-center content-center">
    <div>
      <q-btn
        v-if="!loading"
        color="primary"
        :title="t('general.save')"
        flat
        icon="save"
        round
        stack
        @click="save"
      >
        <q-tooltip
          v-model="savedFlashMsg"
          :transition-duration="2000"
          anchor="top middle"
          class="bg-transparent text-overline text-primary text-weight-bold text-uppercase"
          no-parent-event
          self="center middle"
          transition-show="none"
        >{{ t('general.saved') }}
        </q-tooltip>
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
        text-color="primary"
        class="query-action-buttons__fab-action"
        icon="save"
        @click="save"
      />
      <q-fab-action
        :disable="unsaved"
        :label="t('queries.duplicate')"
        :text-color="unsaved ? 'grey-8': 'primary'"
        class="query-action-buttons__fab-action"
        icon="content_copy"
        @click="duplicate"
      />
      <q-fab-action
        :disable="unsaved"
        :label="t('general.delete')"
        :text-color="unsaved ? 'grey-8':  'negative'"
        class="query-action-buttons__fab-action"
        icon="delete_outline"
        @click="deleteQuery"
      />
    </q-fab>
  </div>
</template>

<script lang="ts" setup>
import {computed, ref} from 'vue';
import {useI18n} from 'vue-i18n';
import {useRoute, useRouter} from 'vue-router';
import useQueryMenuActions from 'src/composables/queries/queryMenuActions';
import {Query} from 'src/models/query/query';
import type {AxiosError} from 'axios';
import {Notify} from 'quasar';
import {useQueryStore} from 'stores/query';

const emit = defineEmits<{
  (e: 'saved'): void
}>();

const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
const route = useRoute();
const router = useRouter();
const actions = useQueryMenuActions();
const store = useQueryStore();

const showMore = ref(false);
const loading = ref(false);
const savedFlashMsg = ref(false);
const unsaved = ref(route.params.id === 'new');

const queryId = computed<string>(() => route.params.id as string);

async function save() {
  loading.value = true;
  store.attemptedToSaveQuery = true;

  try {
    const query = await actions.saveQuery(queryId.value) as Query;
    if (route.params.id !== `${query.id}`) {
      await router.replace(`/queries/${query.id}`);
    }
    unsaved.value = false;
    emit('saved');
    showSavedFlashMsg();
  } catch (e) {
    const error = e as AxiosError<Query>;
    console.log(error.message);
    Notify.create({
      message: t('general.failedToSaveData'),
      type: 'negative',
      timeout: 15000,
      textColor: 'white',
      progress: true,
    });
  } finally {
    loading.value = false;
  }
}

function showSavedFlashMsg() {
  savedFlashMsg.value = true;
  window.setTimeout(() => savedFlashMsg.value = false, 800);
}

function duplicate() {
  loading.value = ! loading.value;
  // todo
}

async function deleteQuery() {
  loading.value = true;
  await actions.deleteQuery(queryId.value);
  await router.push('/queries');
}


</script>

<style scoped>
.query-action-buttons__fab-action {
  background: white !important;
  opacity: 1 !important;
}
</style>
