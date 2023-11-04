<template>
  <MarkLayout>
    <q-page padding>

      <h5 class="q-mb-lg q-mt-sm">{{ t('marks.setMeta.title') }}</h5>

      <div class="q-gutter-md">
        <!--suppress RequiredAttributes -->
        <q-input
          ref="authorField"
          v-model="author"
          :hint="t('marks.setMeta.authorHint')"
          :label="t('marks.setMeta.author')"
          :rules="[val => !!val || t('general.form.required')]"
          maxlength="45"
          outlined
          type="text"
        />

        <!--suppress RequiredAttributes -->
        <q-input
          ref="dateField"
          v-model="date"
          :hint="t('marks.setMeta.dateHint')"
          :label="t('marks.setMeta.date')"
          :rules="[val => !!val || t('general.form.required')]"
          outlined
          type="date"
        />

        <q-btn
          :label="t('general.next')"
          color="primary"
          @click="goToNext"
        />

      </div>

    </q-page>
  </MarkLayout>
</template>

<script lang="ts">
import {computed, defineComponent, ref} from 'vue'
import {useRouter} from 'vue-router'
import {useI18n} from 'vue-i18n';
import {QInput} from 'quasar';
import {useMarkStore} from 'stores/mark';
import MarkLayout from 'components/Mark/MarkLayout.vue';
import useMarkType from 'src/composables/marks/type';

export default defineComponent({
  name: 'SetMeta',
  components: {MarkLayout},

  setup() {
    const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
    const store = useMarkStore()
    const router = useRouter()
    const markType = useMarkType()


    const authorField = ref<typeof QInput>()
    const dateField = ref<typeof QInput>()


    const author = computed<string>({
      get: () => store.author,
      set: (val: string) => store.setAuthor(val)
    });
    const date = computed<string>({
      get: () => store.date,
      set: (val: string) => store.date = val
    });


    function isValid() {
      const author = authorField.value?.validate() as null | boolean // eslint-disable-line @typescript-eslint/no-unsafe-call
      const date = dateField.value?.validate() as null | boolean // eslint-disable-line @typescript-eslint/no-unsafe-call
      return !! (author && date)
    }

    function goToNext() {
      if (isValid()) {
        const route = markType.value === 'variety'
          ? '/marks/variety/select-variety'
          : markType.value === 'batch'
            ? '/marks/batch/select-batch'
            : '/marks/tree/select-tree'
        void router.push(route)
      }
    }

    return {
      t,
      author,
      date,
      authorField,
      dateField,
      goToNext
    }
  }
})
</script>
