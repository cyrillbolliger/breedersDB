<template>
  <h5 class="q-mb-sm q-mt-sm">{{ t('marks.selectForm.title') }}</h5>

  <TabularList
    :filter-function="filterFunction"
    :item-height="35"
    :items="markForms"
    :loading="working"
    max-list-height="calc(100vh - 260px)"
    @refresh="loadForms"
  >
    <template #default="slotProps">
      <q-item
        v-ripple
        :active="slotProps.item.id === selectedForm?.id"
        clickable
        @click.stop="selectForm(slotProps.item)"
      >
        <q-item-section>
          <q-item-label>{{ slotProps.item.name }}</q-item-label>
          <q-item-label caption>{{ slotProps.item.description }}</q-item-label>
        </q-item-section>

        <q-item-section v-if="slotProps.item.id === selectedForm?.id" side top>
          <q-item-label caption>{{ t('general.selected') }}</q-item-label>
        </q-item-section>
      </q-item>
    </template>
  </TabularList>
</template>

<script lang="ts" setup>
import {computed, ref} from 'vue'
import {useI18n} from 'vue-i18n';
import {MarkForm} from 'src/models/form';
import {useRouter} from 'vue-router'
import useApi from 'src/composables/api'
import TabularList from 'components/Util/TabularList.vue';
import {useMarkStore} from 'stores/mark';
import useMarkType from 'src/composables/marks/type';

const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
const store = useMarkStore()
const {working, get} = useApi()
const router = useRouter()
const markType = useMarkType()

const markForms = ref<MarkForm[]>([])

const selectedForm = computed(() => store.selectedForm)

function loadForms(done: () => void = () => null) {
  void get<MarkForm[]>('markForms/index', done)
    .then(data => {
      if (data) {
        markForms.value = data
      } else {
        markForms.value = []
      }
    })
}

function selectForm(form: MarkForm) {
  void get<MarkForm>(`markForms/view/${form.id}`)
    .then(data => store.selectedForm = data as MarkForm)
    .then(() => void router.push(`/marks/${markType.value}/set-meta`))
}

function filterFunction(term: string, item: MarkForm) {
  if ( ! term) {
    return true
  }

  return item.name.toLowerCase().indexOf(term.toLowerCase()) > -1
}

loadForms()
</script>
