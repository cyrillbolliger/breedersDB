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
        v-if="filteredItems.length === 0"
        bordered
        separator
      >
        <q-item>
          <q-item-section>
            <q-item-label
              class="text-italic text-grey text-center"
            >{{ t('components.util.list.nothingFound') }}
            </q-item-label>
          </q-item-section>
        </q-item>
      </q-list>

      <q-virtual-scroll
        v-else
        :items="filteredItems"
        separator
        bordered
        :style="`max-height: ${maxListHeight}`"
        :virtual-scroll-item-size="itemHeight"
      >
        <template v-slot="{ item, index }">
          <slot :item="item" :key="index"/>
        </template>
      </q-virtual-scroll>
      <p
        class="text-caption text-grey"
      >{{ listMeta }}</p>
    </template>

    <SpinLoader
      v-else
    />

  </q-pull-to-refresh>
</template>
<script lang="ts">
import SpinLoader from 'components/Util/SpinLoader.vue'
import {useI18n} from 'vue-i18n';
import {computed, defineComponent, PropType, ref} from 'vue';

export default defineComponent({
  name: 'List',
  components: {SpinLoader},
  emits: ['refresh'],
  props: {
    items: {
      type: Array,
      required: true
    },
    loading: {
      type: Boolean,
      required: true
    },
    filterFunction: {
      type: Function as PropType<(term: string, item: unknown) => boolean>,
    },
    maxListHeight: {
      type: String,
      default: '250px'
    },
    itemHeight: {
      type: Number,
      default: 24
    }
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
        return t('components.util.list.listMetaFiltered', {total, showing})
      } else {
        return t('components.util.list.listMetaUnfiltered', {total})
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
