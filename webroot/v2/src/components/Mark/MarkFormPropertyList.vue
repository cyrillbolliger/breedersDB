<template>
  <TabularList
    :items="properties"
    :loading="loading"
    :filter-function="filterFunction"
    @refresh="loadProperties"
    max-list-height="calc(100vh - 210px)"
    :item-height="35"
  >
    <template #default="slotProps">
      <q-item
        clickable
        v-ripple
        @click.stop="$emit('select', slotProps.item)"
      >
        <q-item-section>
          <q-item-label>{{ slotProps.item.name }}</q-item-label>
        </q-item-section>
      </q-item>
    </template>
  </TabularList>
</template>
<script lang="ts">
import {defineComponent, ref} from 'vue';
import {useI18n} from 'vue-i18n';
import useApi from 'src/composables/api';
import {MarkForm, MarkFormProperty} from 'src/models/form';
import TabularList from 'components/Util/TabularList.vue';

export default defineComponent({
  name: 'MarkFormPropertyList',
  components: {TabularList},
  emits: ['select'],

  setup() {
    const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
    const {working, get} = useApi()

    const properties = ref<MarkFormProperty[]>([])

    function loadProperties(done: () => void = () => null) {
      void get<MarkFormProperty[]>('markFormProperties/index', done)
        .then(data => {
          if (data) {
            properties.value = data
          } else {
            properties.value = []
          }
        })
    }

    function filterFunction(term: string, item: MarkForm) {
      if ( ! term) {
        return true
      }

      return item.name.toLowerCase().indexOf(term.toLowerCase()) > -1
    }

    loadProperties()

    return {
      t,
      loading: working,
      filterFunction,
      loadProperties,
      properties
    }
  }
})
</script>
