<template>
  <q-page padding>

    <q-input
      v-model="search"
      debounce="100"
      filled
      clearable
      dense
      type="search"
      :placeholder="t('general.search')"
    >
      <template v-slot:append>
        <q-icon name="search"/>
      </template>
    </q-input>

    <q-pull-to-refresh @refresh="loadForms">
      <template
        v-if="!loading"
      >
        <q-list
          bordered
          separator
        >

          <q-item
            v-if="filteredForms.length === 0"
          >
            <q-item-section>
              <q-item-label
                class="text-italic text-grey text-center"
              >{{ t('marks.selectForm.nothingFound') }}</q-item-label>
            </q-item-section>
          </q-item>

          <q-item
            clickable
            v-ripple
            v-for="item in filteredForms"
            :key="item.id"
            :active="item.id === selectedForm?.id"
            @click.stop="selectForm(item)"
          >
            <q-item-section>
              <q-item-label>{{ item.name }}</q-item-label>
              <q-item-label caption>{{ item.description }}</q-item-label>
            </q-item-section>

            <q-item-section side top v-if="item.id === selectedForm?.id">
              <q-item-label caption>{{ t('marks.selectForm.selected') }}</q-item-label>
            </q-item-section>
          </q-item>
        </q-list>
        <p
          class="text-caption text-grey"
        >{{ listMeta }}</p>
      </template>

      <Loader
        v-else
      />

    </q-pull-to-refresh>
  </q-page>
</template>

<script lang="ts">
import {computed, defineComponent, ref} from 'vue'
import {useI18n} from 'vue-i18n';
import {useStore} from 'src/store';
import {api} from 'boot/axios';
import {MarkForm} from 'components/models';
import Loader from 'components/Util/Loader.vue';
import {AxiosError} from 'axios';
import {useQuasar} from 'quasar'

export default defineComponent({
  name: 'MarksSelectForm',
  components: {Loader},

  setup() {

    const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
    const $store = useStore()
    const $q = useQuasar()

    const markForms = ref<MarkForm[]>([])
    const loading = ref(false)
    const search = ref('')

    const selectedForm = computed<MarkForm | null>(() => $store.getters['mark/selectedForm']) // eslint-disable-line
    const filteredForms = computed<MarkForm[]>(() => {
      if (!search.value) {
        return markForms.value
      }

      const s = search.value.toLowerCase()

      return markForms.value.filter(item => item.name.toLowerCase().indexOf(s) > -1)
    });
    const listMeta = computed<string>(() => {
      const total = markForms.value.length
      const showing = filteredForms.value.length

      if (total > showing) {
        return t('marks.selectForm.listMetaFiltered', {total, showing})
      } else {
        return t('marks.selectForm.listMetaUnfiltered', {total})
      }
    })

    function loadForms(done?: () => void) {
      loading.value = true
      markForms.value = []
      api.get('markForms/index')
        .then(data => markForms.value = data.data.data) // eslint-disable-line
        .catch(error => handleLoadingError(error))
        .finally(() => {
          if (done) {
            done()
          }
          loading.value = false
        })
    }

    function handleLoadingError(error: Error | AxiosError) {
      console.log(error.message)
      $q.notify({
        message: t('general.failedToLoadData'),
        color: 'negative',
        actions: [
          { label: t('general.retry'), color: 'white', handler: () => loadForms() },
        ]
      })
    }

    function selectForm(form: MarkForm) {
      void $store.dispatch('mark/selectForm', form)
      // todo routing
    }


    const breadcrumbs = [
      {label: t('marks.title')},
      {label: t('marks.selectForm.title')}
    ];
    void $store.dispatch('layout/breadcrumbs', breadcrumbs)
    void $store.dispatch('layout/title', t('marks.selectForm.title'))

    loadForms()

    return {
      t,
      filteredForms,
      selectedForm,
      selectForm,
      loadForms,
      loading,
      search,
      listMeta,
    }
  }
})
</script>
