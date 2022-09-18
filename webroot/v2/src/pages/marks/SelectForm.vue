<template>
  <q-page padding>

    <h5 class="q-mb-sm q-mt-sm">{{ t('marks.selectForm.title') }}</h5>

    <TabularList
      :items="markForms"
      :loading="loading"
      :filter-function="filterFunction"
      @refresh="loadForms"
      max-list-height="calc(100vh - 260px)"
      :item-height="35"
    >
      <template #default="slotProps">
        <q-item
          clickable
          v-ripple
          :active="slotProps.item.id === selectedForm?.id"
          @click.stop="selectForm(slotProps.item)"
        >
          <q-item-section>
            <q-item-label>{{ slotProps.item.name }}</q-item-label>
            <q-item-label caption>{{ slotProps.item.description }}</q-item-label>
          </q-item-section>

          <q-item-section side top v-if="slotProps.item.id === selectedForm?.id">
            <q-item-label caption>{{ t('general.selected') }}</q-item-label>
          </q-item-section>
        </q-item>
      </template>
    </TabularList>
  </q-page>
</template>

<script lang="ts">
import {computed, defineComponent, ref} from 'vue'
import {useI18n} from 'vue-i18n';
import {MarkForm} from 'src/models/form';
import {useRouter} from 'vue-router'
import useLayout from 'src/composables/layout'
import useMarkTabNav from 'src/composables/marks/tab-nav';
import useApi from 'src/composables/api'
import TabularList from 'components/Util/TabularList.vue';
import {useMarkStore} from 'stores/mark';

export default defineComponent({
  name: 'MarksSelectForm',
  components: {TabularList},

  setup() {
    const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
    const store = useMarkStore()
    const {working, get} = useApi()
    const router = useRouter()
    const {setToolbarTitle, setToolbarTabs} = useLayout()

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
        .then(() => void router.push('/marks/set-meta'))
    }

    function filterFunction(term: string, item: MarkForm) {
      if ( ! term) {
        return true
      }

      return item.name.toLowerCase().indexOf(term.toLowerCase()) > -1
    }

    setToolbarTabs(useMarkTabNav())
    setToolbarTitle(t('marks.title'))

    loadForms()

    return {
      t,
      selectedForm,
      loadForms,
      loading: working,
      markForms,
      selectForm,
      filterFunction
    }
  }
})
</script>
