<template>
  <q-page padding>

    <h5 class="q-mb-xs q-mt-sm">{{ t('marks.markTree.title') }}</h5>

    <div class="row q-gutter-x-sm q-mb-sm">
      <small>
        <q-icon name="list"/>&nbsp;{{ form?.name }}</small>
      <small>
        <q-icon name="person"/>&nbsp;{{ author }}</small>
      <small>
        <q-icon name="today"/>&nbsp;{{ date.toLocaleDateString() }}</small>
    </div>

    <tree-card
      :tree="tree"
      @click="$router.push('/marks/select-tree')"
    />

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
        :model-value="markValues.get(property.id)?.value"
        @update:modelValue="setMarkValue(property.id, $event)"
        @reset:modelValue="resetMarkValue(property.id)"
        :progress="uploadProgress.get(property.id)"
      />
      <q-separator spaced inset/>
    </q-list>

    <q-page-sticky
      position="bottom-right"
      :offset="[18, 18]"
    >
      <q-btn
        fab
        icon="save"
        @click="save"
        :loading="working"
        :disabled="!savable"
        color="primary"
      />
    </q-page-sticky>

    <div
      style="height: 75px"
    ></div>

  </q-page>
</template>

<script lang="ts">
import {computed, defineComponent, ref} from 'vue'
import {useI18n} from 'vue-i18n';
import useLayout from 'src/composables/layout';
import useMarkTabNav from 'src/composables/marks/tab-nav';
import {useStore} from 'src/store';
import {MarkForm, MarkFormFieldType, MarkValue, Mark, MarkValueValue} from 'src/models/form';
import {Tree} from 'src/models/tree';
import {useRouter} from 'vue-router'
import {useQuasar} from 'quasar';
import MarkInput from 'components/Mark/Input.vue'
import useApi from 'src/composables/api';
import TreeCard from 'components/Util/TreeCard.vue';
import useUploader from 'src/composables/uploader';

export default defineComponent({
  name: 'MarkTree',
  components: {TreeCard, MarkInput},
  setup() {
    const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
    const store = useStore()
    const router = useRouter()
    const $q = useQuasar()
    const api = useApi();
    const fileUploader = useUploader();

    const {setToolbarTabs, setToolbarTitle} = useLayout()
    setToolbarTabs(useMarkTabNav())
    setToolbarTitle(t('marks.title'))

    const markValues = ref(new Map<number, MarkValue>());
    const uploadProgress = ref(new Map<number, number>());
    const uploading = ref(false);
    const working = computed(() => api.working.value || uploading.value);

    /* eslint-disable @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-return */
    const tree = computed<Tree | null>(() => store.getters['mark/tree'])
    const author = computed<string>(() => store.getters['mark/author'])
    const form = computed<MarkForm | null>(() => store.getters['mark/selectedForm'])
    const date = computed<Date>(() => {
      return new Date(store.getters['mark/date'] as Date|string)
    })
    /* eslint-enable @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-return */

    const savable = computed<boolean>(() => {
      if ( ! form.value || ! author.value || ! date.value || ! tree.value) {
        return false
      }

      return markValues.value.size > 0
    })

    function getMarkValues() {
      const values: MarkValue[] = []
      markValues.value.forEach(val => values.push(val))
      return values
    }

    function setMarkValue(mark_form_property_id: number, value: MarkValueValue) {
      markValues.value.set(mark_form_property_id, {
        value,
        exceptional_mark: false,
        mark_form_property_id,
      });
    }

    function resetMarkValue(mark_form_property_id: number) {
      markValues.value.delete(mark_form_property_id)
    }

    function upload(file: File, mark_form_property_id: number) {
      return fileUploader.upload(
        'photos/add',
        file,
        progress => uploadProgress.value.set(
          mark_form_property_id,
          progress
        )
      )
    }

    function save() {
      const values = getMarkValues();
      const uploads: Promise<string | void>[] = [];

      values.forEach(val => {
        if (val.value instanceof File) {
          uploading.value = true
          uploadProgress.value.set(val.mark_form_property_id, 0.01)
          uploads.push(
            upload(val.value, val.mark_form_property_id)
              .then(resp => val.value = resp ? resp.filename : '')
          );
        }
      })

      void Promise.all(uploads).then(() => {
        uploading.value = false;

        if ( ! tree.value) {
          notifyStateError(t('marks.markTree.selectTree'), '/marks/select-tree')
          return;
        }

        if ( ! form.value) {
          notifyStateError(t('marks.markTree.selectForm'), '/marks/select-form')
          return;
        }

        const mark: Mark = {
          date: date.value,
          author: author.value,
          mark_form_id: form.value.id,
          tree_id: tree.value.id,
          variety_id: null,
          batch_id: null,
          mark_values: values,
        }

        void api.post<Mark, number>('marks/add', mark)
          .then(() => {
            $q.notify({
              message: t('marks.markTree.saved'),
              color: 'success',
            });
            void router.push('/marks/select-tree')
          })
      });
    }


    if ( ! form.value) {
      notifyStateError(t('marks.markTree.selectForm'), '/marks/select-form')
    }

    if ( ! author.value || ! date.value) {
      notifyStateError(t('marks.markTree.setMeta'), '/marks/set-meta')
    }

    if ( ! tree.value) {
      notifyStateError(t('marks.markTree.selectTree'), '/marks/select-tree')
    }

    function notifyStateError(button: string, route: string) {
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
      MarkFormFieldTypes: MarkFormFieldType,
      savable,
      save,
      markValues,
      setMarkValue,
      resetMarkValue,
      working,
      uploadProgress
    }
  }
})
</script>
