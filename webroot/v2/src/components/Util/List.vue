<template>
  <!--suppress RequiredAttributes -->
  <q-input
    v-if="filterFunction"
    v-model="search"
    debounce="100"
    filled
    clearable
    type="search"
    :placeholder="t('general.search')"
  >
    <template v-slot:append>
      <q-icon name="search"/>
    </template>
  </q-input>

  <q-pull-to-refresh @refresh="$emit('refresh', $event)">
    <template
      v-if="!loading"
    >
      <q-list
        bordered
        separator
      >

        <q-item
          v-if="items.length === 0"
        >
          <q-item-section>
            <q-item-label
              class="text-italic text-grey text-center"
            >{{ t('components.util.searchableList.nothingFound') }}
            </q-item-label>
          </q-item-section>
        </q-item>

        <q-item
          clickable
          v-ripple
          v-for="item in filteredItems"
          :key="idGetter(item)"
          :active="selectedItem && idGetter(item) === idGetter(selectedItem)"
          @click.stop="$emit('select', item)"
        >
          <q-item-section>
            <slot :item="item"/>
          </q-item-section>

          <q-item-section side top v-if="selectedItem && idGetter(item) === idGetter(selectedItem)">
            <q-item-label caption>{{ t('components.util.searchableList.selected') }}</q-item-label>
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
</template>
<script lang="ts">
import Loader from 'components/Util/Loader.vue'
import {useI18n} from 'vue-i18n';
import {computed, defineComponent, PropType, ref} from 'vue';

export default defineComponent({
  name: 'List',
  components: {Loader},
  emits: ['refresh', 'select'],
  props: {
    items: {
      type: Array,
      required: true
    },
    loading: {
      type: Boolean,
      required: true
    },
    selectedItem: {
      type: Object as PropType<unknown>,
      default: null
    },
    filterFunction: {
      type: Function as PropType<(term: string, item: unknown) => boolean>,
    },
    idGetter: {
      type: Function as PropType<(item: unknown) => string|number>,
      required: true
    },
  },

  setup(props) {
    const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method

    const search = ref('');

    const filteredItems = computed<Array<unknown>>(() => {
      if ( ! search.value || ! props.filterFunction) {
        return props.items
      }

      return props.items.filter(item => props.filterFunction!(search.value, item)) // eslint-disable-line @typescript-eslint/no-non-null-assertion
    });

    const listMeta = computed<string>(() => {
      const total = props.items.length
      const showing = filteredItems.value.length

      if (total > showing) {
        return t('components.util.searchableList.listMetaFiltered', {total, showing})
      } else {
        return t('components.util.searchableList.listMetaUnfiltered', {total})
      }
    })

    return {
      t,
      listMeta,
      search,
      filteredItems
    }
  }
})
</script>
