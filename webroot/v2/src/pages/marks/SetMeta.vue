<template>
  <q-page padding>

    <h5 class="q-mb-lg q-mt-sm">{{ t('marks.setMeta.title') }}</h5>

    <div class="q-gutter-md">
      <q-input
        outlined
        v-model="author"
        :label="t('marks.setMeta.author')"
        :hint="t('marks.setMeta.authorHint')"
        :rules="[val => !!val || t('general.form.required')]"
        type="text"
        maxlength="45"
        ref="authorField"
      />

      <q-input
        outlined
        v-model="date"
        :rules="[val => !!val || t('general.form.required')]"
        type="date"
        :label="t('marks.setMeta.date')"
        :hint="t('marks.setMeta.dateHint')"
        ref="dateField"
      />

      <q-btn
        color="primary"
        :label="t('general.next')"
        @click="goToNext"
      />

    </div>

  </q-page>
</template>

<script lang="ts">
import {computed, defineComponent, ref} from 'vue'
import {useRouter} from 'vue-router'
import {useI18n} from 'vue-i18n';
import {useStore} from 'src/store';
import useMarkTabNav from 'src/composables/marks/tab-nav';
import useLayout from 'src/composables/layout';

export default defineComponent({
  name: 'SetMeta',

  setup() {
    const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
    const store = useStore()
    const router = useRouter()


    const {setToolbarTabs, setToolbarTitle} = useLayout()
    setToolbarTabs(useMarkTabNav())
    setToolbarTitle(t('marks.title'))


    const authorField = ref(null)
    const dateField = ref(null)


    const author = computed<string>({
      get: () => store.getters['mark/author'], // eslint-disable-line
      set: (val: string) => store.dispatch('mark/author', val)
    });
    const date = computed<string>({
      get: () => store.getters['mark/date'], // eslint-disable-line
      set: (val: string) => store.dispatch('mark/date', val)
    });


    function isValid() {
      const author = authorField.value?.validate()
      const date = dateField.value?.validate()
      return author && date
    }

    function goToNext() {
      if (isValid()) {
        router.push('/marks/select-tree')
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
