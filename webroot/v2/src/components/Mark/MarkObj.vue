<template>
  <h5 class="q-mb-xs q-mt-sm">{{ title }}</h5>

  <div class="row q-gutter-x-sm q-mb-sm">
    <small>
      <q-icon name="list"/>&nbsp;{{ form?.name }}</small>
    <small>
      <q-icon name="person"/>&nbsp;{{ author }}</small>
    <small>
      <q-icon name="today"/>&nbsp;{{ date.toLocaleDateString() }}</small>
  </div>

  <slot name="obj-preview"/>

  <div class="q-mb-md">
    <mark-counter
      v-if="markTotalTarget > 1"
      :count="markCounter"
      :total="markTotalTarget"
      @reset="markCounter = 0"
    />
  </div>

  <q-list
    v-for="(property, idx) in properties"
    :key="idx"
  >
    <mark-input
      :id="property.id"
      :field-type="property.field_type"
      :model-value="markValues.get(property.id)?.value"
      :default-value="property.default_value || ''"
      :name="property.name"
      :note="property.note || undefined"
      :number-constraints="property.number_constraints"
      :progress="uploadProgress.get(property.id)"
      @update:modelValue="setMarkValue(property.id, $event)"
      @reset:modelValue="resetMarkValue(property.id)"
    />
    <q-separator inset spaced/>
  </q-list>
  <div class="row justify-center">
    <q-btn
      color="primary"
      flat
      @click="showPropertyDialog = !showPropertyDialog"
    >{{ t('marks.markObj.addProperty') }}
    </q-btn>
  </div>

  <q-page-sticky
    :offset="[18, 18]"
    position="bottom-right"
  >
    <q-btn
      :disabled="!savable"
      :loading="working"
      color="primary"
      fab
      icon="save"
      @click="save"
    />
  </q-page-sticky>

  <div
    style="height: 75px"
  ></div>

  <q-dialog v-model="showPropertyDialog">
    <q-card style="width: 90vw; max-width: 600px">
      <q-card-section>
        <div class="text-h6">{{ t('marks.markObj.selectProperty') }}</div>
        <mark-form-property-list
          @select="addProperty"
        />
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script lang="ts" setup>
import {computed, ref} from 'vue'
import {useI18n} from 'vue-i18n';
import {Mark, MarkFormProperty, MarkValue, MarkValueValue} from 'src/models/form';
import {useRouter} from 'vue-router'
import {useQuasar} from 'quasar';
import MarkInput from 'components/Mark/Input.vue'
import MarkCounter from 'components/Mark/MarkCounter.vue'
import useApi from 'src/composables/api';
import useUploader from 'src/composables/uploader';
import MarkFormPropertyList from 'src/components/Mark/MarkFormPropertyList.vue';
import {useMarkStore} from 'stores/mark';
import {Tree} from 'src/models/tree';
import {Batch} from 'src/models/batch';
import {Variety} from 'src/models/variety';
import useMarkCounter from 'src/composables/marks/markCounter';

const emit = defineEmits<{
  (e: 'saved'): void,
}>()

const props = defineProps<{
  title: string,
  tree?: Tree,
  batch?: Batch,
  variety?: Variety,
  markObjSelectUrl: string,
  markObjSelectLabel: string,
  formSelectUrl: string,
  metaSetUrl: string,
}>()

const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
const store = useMarkStore()
const router = useRouter()
const $q = useQuasar()
const api = useApi();
const fileUploader = useUploader();

const showPropertyDialog = ref(false)

const markValues = ref(new Map<number, MarkValue>());
const uploadProgress = ref(new Map<number, number>());
const uploading = ref(false);
const working = computed(() => api.working.value || uploading.value);

const author = computed(() => store.author)
const form = computed(() => store.selectedForm)
const date = computed(() => new Date(store.date))
const markTotalTarget = computed(() => store.markTotalTarget)

const markCounter = useMarkCounter({
  form_id: form.value?.id || -1,
  tree_id: props.tree?.id,
  variety_id: props.variety?.id,
  batch_id: props.batch?.id
})

const addedProperties = ref([] as MarkFormProperty[])
const properties = computed(() => {
  const all: MarkFormProperty[] = [];

  if (form.value?.mark_form_properties) {
    all.push(...form.value?.mark_form_properties)
  }

  all.push(...addedProperties.value)

  return all
})

const markObject = computed(() => {
  return props.tree || props.batch || props.variety
})

const savable = computed<boolean>(() => {
  if ( ! form.value || ! author.value || ! date.value || ! markObject.value) {
    return false
  }

  return markValues.value.size > 0
})

function addProperty(property: MarkFormProperty) {
  showPropertyDialog.value = false
  if (properties.value.filter(item => item.id === property.id).length) {
    $q.notify({
      message: t('marks.markObj.propertyAlreadyExists', {property: property.name}),
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

    warnIfFormMetaOrMarkObjMissing()
    if ( ! form.value || ! author.value || ! date.value || ! markObject.value) {
      return
    }

    // noinspection TypeScriptUnresolvedVariable
    const mark: Mark = {
      date: date.value,
      author: author.value,
      mark_form_id: form.value.id,
      tree_id: props.tree?.id || null,
      variety_id: props.variety?.id || null,
      batch_id: props.batch?.id || null,
      mark_values: values,
    }

    void api.post<Mark, number>('marks/add', mark)
      .then(() => {
        $q.notify({
          message: t('marks.markObj.saved'),
          color: 'success',
          badgeClass: 'hidden',
        });
        if (markTotalTarget.value > 1) {
          markCounter.value++;
          if (markCounter.value < markTotalTarget.value) {
            emit('saved');
          } else {
            void router.push(props.markObjSelectUrl)
          }
        } else {
          void router.push(props.markObjSelectUrl)
        }
      })
  });
}

function warnIfFormMetaOrMarkObjMissing() {
  if ( ! form.value) {
    notifyStateError(t('marks.markObj.selectForm'), props.formSelectUrl)
  }

  if ( ! author.value || ! date.value) {
    notifyStateError(t('marks.markObj.setMeta'), props.metaSetUrl)
  }

  if ( ! markObject.value) {
    notifyStateError(props.markObjSelectLabel, props.markObjSelectUrl)
  }
}

function notifyStateError(button: string, route: string) {
  $q.notify({
    message: t('marks.markObj.missingDataError'),
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

warnIfFormMetaOrMarkObjMissing()
</script>
