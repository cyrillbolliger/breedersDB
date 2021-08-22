<template>
  <q-page padding>

    <h5 class="q-mb-xs q-mt-sm">{{ t('marks.markTree.title') }}</h5>

    <div class="row q-gutter-x-sm q-mb-sm">
      <small><q-icon name="list"/>&nbsp;{{ form?.name }}</small>
      <small><q-icon name="person"/>&nbsp;{{ author }}</small>
      <small><q-icon name="today"/>&nbsp;{{ date }}</small>
    </div>

    <q-card class="bg-grey-4 q-mb-md">
      <q-card-section class="q-pt-xs">
        <div class="text-overline text-weight-regular">Selected Tree</div>
        <div class="text-h6">{{ tree?.convar }}</div>
          <div class="text-caption"><q-icon name="tag"/>&nbsp;{{ tree?.publicid }}</div>
      </q-card-section>
    </q-card>

    <q-list
      v-for="(property, idx) in form?.mark_form_properties"
      :key="idx"
    >
      <mark-input
        :id="property.id"
        :name="property.name"
        :number-constraints="property.number_constraints"
        :field-type="property.field_type"
        :note="property.note"
      />
      <q-separator spaced inset />
    </q-list>

  </q-page>
</template>

<script lang="ts">
import {computed, defineComponent} from 'vue'
import {useI18n} from 'vue-i18n';
import useLayout from 'src/composables/layout';
import useMarkTabNav from 'src/composables/marks/tab-nav';
import {useStore} from 'src/store';
import {MarkForm, Tree, MarkFormFieldTypes} from 'components/models';
import {useRouter} from 'vue-router'
import {useQuasar} from 'quasar';
import MarkInput from 'components/Mark/Input.vue'

export default defineComponent({
  name: 'MarkTree',
  components: {MarkInput},
  setup() {
    const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
    const store = useStore()
    const router = useRouter()
    const $q = useQuasar()

    const {setToolbarTabs, setToolbarTitle} = useLayout()
    setToolbarTabs(useMarkTabNav())
    setToolbarTitle(t('marks.title'))

    /* eslint-disable @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-return */
    const tree = computed<Tree|null>(() => store.getters['mark/tree'])
    const author = computed<string>(() => store.getters['mark/author'])
    const date = computed<string>(() => store.getters['mark/date'])
    const form = computed<MarkForm|null>(() => store.getters['mark/selectedForm'])
    /* eslint-enable @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-return */

    if (! form.value) {
      notifyStateError(t('marks.markTree.selectForm'), '/marks/select-form')
    }

    if (! author.value || ! date.value) {
      notifyStateError(t('marks.markTree.setMeta'), '/marks/set-meta')
    }

    if (! tree.value) {
      notifyStateError(t('marks.markTree.selectTree'), '/marks/select-tree')
    }

    function notifyStateError( button: string, route: string) {
      $q.notify({
        message: t('marks.markTree.missingDataError'),
        multiLine: true,
        timeout: 30000,
        actions: [
          {
            label: button,
            color: 'white',
            handler: () => router.push(route)
          }
        ]
      });
    }

    return {
      t,
      tree,
      form,
      author,
      date,
      MarkFormFieldTypes
    }
  }
})
</script>
