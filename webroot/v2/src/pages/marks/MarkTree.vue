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
      @change="$router.push('/marks/select-tree')"
    />

    <q-list
      v-for="(property, idx) in properties"
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
    <div class="row justify-center">
      <q-btn
        flat
        color="primary"
        @click="showPropertyDialog = !showPropertyDialog"
      >{{ t('marks.markTree.addProperty') }}
      </q-btn>
    </div>

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

    <q-dialog v-model="showPropertyDialog">
      <q-card style="width: 90vw; max-width: 600px">
        <q-card-section>
          <div class="text-h6">{{ t('marks.markTree.selectProperty') }}</div>
          <mark-form-property-list
            @select="addProperty"
          />
        </q-card-section>
      </q-card>
    </q-dialog>

  </q-page>
</template>

<script lang="ts">
import {computed, defineComponent, ref} from 'vue'
import {useI18n} from 'vue-i18n';
import useLayout from 'src/composables/layout';
import useMarkTabNav from 'src/composables/marks/tab-nav';
import {Mark, MarkFormFieldType, MarkFormProperty, MarkValue, MarkValueValue} from 'src/models/form';
import {useRouter} from 'vue-router'
import {useQuasar} from 'quasar';
import MarkInput from 'components/Mark/Input.vue'
import useApi from 'src/composables/api';
import TreeCard from 'components/Util/TreeCard.vue';
import useUploader from 'src/composables/uploader';
import MarkFormPropertyList from 'src/components/Mark/MarkFormPropertyList.vue';
import {useMarkStore} from 'stores/mark';

export default defineComponent({
  name: 'MarkTree',
  components: {MarkFormPropertyList, TreeCard, MarkInput},
  setup() {
    const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
    const store = useMarkStore()
    const router = useRouter()
    const $q = useQuasar()
    const api = useApi();
    const fileUploader = useUploader();

    const {setToolbarTabs, setToolbarTitle} = useLayout()
    setToolbarTabs(useMarkTabNav())
    setToolbarTitle(t('marks.title'))

    const showPropertyDialog = ref(false)

    const markValues = ref(new Map<number, MarkValue>());
    const uploadProgress = ref(new Map<number, number>());
    const uploading = ref(false);
    const working = computed(() => api.working.value || uploading.value);

    const tree = computed(() => store.tree)
    const author = computed(() => store.author)
    const form = computed(() => store.selectedForm)
    const date = computed(() => new Date(store.date))

    const addedProperties = ref([] as MarkFormProperty[])
    const properties = computed(() => {
      const all: MarkFormProperty[] = [];

      if (form.value?.mark_form_properties) {
        all.push(...form.value?.mark_form_properties)
      }

      all.push(...addedProperties.value)

      return all
    })

    const savable = computed<boolean>(() => {
      if ( ! form.value || ! author.value || ! date.value || ! tree.value) {
        return false
      }

      return markValues.value.size > 0
    })

    function addProperty(property: MarkFormProperty) {
      showPropertyDialog.value = false
      if (properties.value.filter(item => item.id === property.id).length) {
        $q.notify({
          message: t('marks.markTree.propertyAlreadyExists', {property: property.name}),
          multiLine: true,
          type: 'negative',
        });
        return
      }
      addedProperties.value.push(property)
    }

    function getMarkValues() {
      const values: MarkValue[] = []
      markValues.value.forEach(val => values.push(val))
      return values
    }

    function setMarkValue(mark_form_property_id: number, value: MarkValueValue) {
      markValues.value.set(mark_form_property_id, {
        value,
        exceptional_mark: isExceptionalMark(mark_form_property_id),
        mark_form_property_id,
      });
    }

    function isExceptionalMark(mark_form_property_id: number) {
      return addedProperties.value
        .filter(item => item.id === mark_form_property_id)
        .length > 0
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
      uploadProgress,
      showPropertyDialog,
      properties,
      addProperty
    }
  }
})
</script>
