<template>
  <q-chip
    :color="bgColor"
    :label="label"
    :outline="showTooltip && !autocloseToolbar"
    class="result-table-cell-mark__chip"
    clickable
    size="sm"
    style="box-shadow: none"
    @click="toggleToolbar"
    @mouseenter="displayToolbar"
    @mouseleave="maybeCloseToolbar"
  >
    <q-menu
      v-model="showTooltip"
      @hide="autocloseToolbar = true"
      :offset="[0, 8]"
      anchor="bottom middle"
      class="bg-grey-9 q-pa-sm"
      dark
      max-height="80vh"
      max-width="80vw"
      no-parent-event
      self="top middle"
    >
      <div
        v-if="'VARCHAR' === mark.field_type"
        class="result-table-cell-mark__text text-body2 text-bold"
      >{{ mark.value }}
      </div>
      <div v-if="'PHOTO' === mark.field_type"
           class="result-table-cell-mark__photo-block"
      >
        <div class="result-table-cell-mark__photo-wrapper">
          <img
            :alt="t('queries.altPhoto', {date: localizeDate(mark.date), author: mark.author})"
            :src="`${apiUrl}/photos/view/${mark.value}?h=400`"
            class="result-table-cell-mark__photo"
          />
        </div>
        <q-btn
          :href="`${apiUrl}/photos/view/${mark.value}`"
          :label="t('queries.downloadPhoto')"
          download
          outline
          size="xs"
          type="a"
        />
      </div>

      <q-icon name="person"/>&nbsp;{{ mark.author }}<br>
      <q-icon name="today"/>&nbsp;{{ localizeDate(mark.date) }}

      <q-separator class="q-my-sm" dark/>

      <template v-if="type === 'tree'">
        <div class="row no-wrap items-end">
          <IconTree
            color="white"
            size="lg"
          />
          <div>
            <strong>{{ mark.entity.name ?? mark.entity.publicid }}</strong><br>
            {{ mark.entity.convar }}
          </div>
        </div>
        <table>
          <tr>
            <th>{{ t('trees.datePlanted') }}</th>
            <td>{{ localizeDate(mark.entity.date_planted) }}</td>
          </tr>
          <tr>
            <th>{{ t('trees.dateEliminated') }}</th>
            <td>{{ localizeDate(mark.entity.date_eliminated) }}</td>
          </tr>
          <tr>
            <th>{{ t('trees.experimentSite') }}</th>
            <td>{{ mark.entity.experiment_site }}</td>
          </tr>
          <tr>
            <th>{{ t('trees.row') }}</th>
            <td>{{ mark.entity.row }}</td>
          </tr>
          <tr>
            <th>{{ t('trees.offset') }}</th>
            <td>{{ mark.entity.offset }}</td>
          </tr>
        </table>
        <div v-if="mark.entity.note" class="q-mt-sm">
          <strong>{{ t('trees.note') }}</strong><br>
          <span v-html="n2br(mark.entity.note)"/>
        </div>
      </template>

      <template v-if="type === 'variety'">
        <div class="row no-wrap items-end">
          <IconVariety
            color="white"
            size="lg"
          />
          <div>
            {{ mark.entity.convar }}
          </div>
        </div>
        <table>
          <tr>
            <th>{{ t('varieties.officialName') }}</th>
            <td>{{ mark.entity.official_name }}</td>
          </tr>
          <tr>
            <th>{{ t('varieties.acronym') }}</th>
            <td>{{ mark.entity.acronym }}</td>
          </tr>
          <tr>
            <th>{{ t('varieties.plantBreeder') }}</th>
            <td>{{ mark.entity.plant_breeder }}</td>
          </tr>
          <tr>
            <th>{{ t('varieties.registration') }}</th>
            <td>{{ mark.entity.registration }}</td>
          </tr>
        </table>
        <div v-if="mark.entity.description" class="q-mt-sm">
          <strong>{{ t('varieties.description') }}</strong><br>
          <span v-html="n2br(mark.entity.description)"/>
        </div>
      </template>

      <template v-if="type === 'batch'">
        <div class="row no-wrap items-end">
          <IconBatch
            color="white"
            size="lg"
          />
          <div>
            {{ mark.entity.crossing_batch }}
          </div>
        </div>
        <table>
          <tr>
            <th>{{ t('batches.dateSowed') }}</th>
            <td>{{ localizeDate(mark.entity.date_sowed) }}</td>
          </tr>
          <tr>
            <th>{{ t('batches.numbSeedsSowed') }}</th>
            <td>{{ mark.entity.numb_seeds_sowed }}</td>
          </tr>
          <tr>
            <th>{{ t('batches.numbSproutsGrown') }}</th>
            <td>{{ mark.entity.numb_sprouts_grown }}</td>
          </tr>
          <tr>
            <th>{{ t('batches.seedTray') }}</th>
            <td>{{ mark.entity.seed_tray }}</td>
          </tr>
          <tr>
            <th>{{ t('batches.datePlanted') }}</th>
            <td>{{ localizeDate(mark.entity.date_planted) }}</td>
          </tr>
          <tr>
            <th>{{ t('batches.numbSproutsPlanted') }}</th>
            <td>{{ mark.entity.numb_sprouts_planted }}</td>
          </tr>
          <tr>
            <th>{{ t('batches.patch') }}</th>
            <td>{{ mark.entity.patch }}</td>
          </tr>
        </table>
        <div v-if="mark.entity.note" class="q-mt-sm">
          <strong>{{ t('batches.note') }}</strong><br>
          <span v-html="n2br(mark.entity.note)"/>
        </div>
      </template>
    </q-menu>
  </q-chip>
</template>

<script lang="ts" setup>
import {computed, PropType, ref} from 'vue';
import {MarkCell} from 'src/models/query/query';
import IconTree from 'components/Util/Icons/IconTree.vue';
import IconBatch from 'components/Util/Icons/IconBatch.vue';
import IconVariety from 'components/Util/Icons/IconVariety.vue';
import {useI18n} from 'vue-i18n';
import useResultColumnConverter from 'src/composables/queries/resultTableColumnConverter';
import {PropertySchemaOptionType} from 'src/models/query/filterOptionSchema';

type MarkEntityType = 'tree' | 'batch' | 'variety';

declare const cake: {
  data: {
    apiUrl: string
  }
};

const {t} = useI18n(); // eslint-disable-line @typescript-eslint/unbound-method
const columnConverter = useResultColumnConverter();
const apiUrl = cake.data.apiUrl;

const props = defineProps({
  mark: {
    type: Object as PropType<MarkCell>,
  }
})

const showTooltip = ref(false);
const autocloseToolbar = ref(true);

const type = computed<MarkEntityType>(() => {
  // noinspection TypeScriptUnresolvedVariable
  if (props.mark.tree_id) {
    return 'tree';
  }
  // noinspection TypeScriptUnresolvedVariable
  if (props.mark.variety_id) {
    return 'variety';
  }
  return 'batch';
})

const label = computed(() => {
  // noinspection TypeScriptUnresolvedVariable
  switch (props.mark.field_type) {
    case 'PHOTO':
      return t('queries.photo');
    case 'BOOLEAN':
      // noinspection TypeScriptUnresolvedVariable
      return props.mark.value ? t('queries.yes') : t('queries.no');
    case 'DATE':
      // noinspection TypeScriptUnresolvedVariable
      return columnConverter.formatColumnValue(props.mark.value, PropertySchemaOptionType.Date);
    case 'INTEGER':
      // noinspection TypeScriptUnresolvedVariable
      return columnConverter.formatColumnValue(props.mark.value, PropertySchemaOptionType.Integer);
    case 'FLOAT':
      // noinspection TypeScriptUnresolvedVariable
      return columnConverter.formatColumnValue(props.mark.value, PropertySchemaOptionType.Float);
    default:
      // noinspection TypeScriptUnresolvedVariable
      return props.mark.value;
  }
});

const bgColor = computed(() => {
  if (showTooltip.value && ! autocloseToolbar.value) {
    return 'accent';
  }

  switch (type.value) {
    case 'tree':
      return 'green-2';
    case 'variety':
      return 'amber-2';
    default:
      return 'grey-2';
  }
})

function n2br(text: string | null) {
  if ( ! text) {
    return text;
  }

  return text.replace(/\r*\n/g, '<br>');
}

function localizeDate(strDate: string | null) {
  if ( ! strDate) {
    return strDate;
  }

  return (new Date(strDate)).toLocaleDateString();
}

function maybeCloseToolbar() {
  if (autocloseToolbar.value) {
    showTooltip.value = false;
  }
}

function displayToolbar() {
  showTooltip.value = true;
}

function toggleToolbar() {
  showTooltip.value = autocloseToolbar.value;
  autocloseToolbar.value = ! autocloseToolbar.value;
}

</script>

<style scoped>
.result-table-cell-mark__chip {
  max-width: 80px;
  cursor: pointer;
}

table {
  margin-top: 1em;
  border-spacing: 0;
}

tr {
  white-space: nowrap;
  line-height: 1.3em;
}

th {
  text-align: left;
  padding-right: 1em;
  padding-left: 0;
}

td {
  text-align: right;
  padding-left: 1em;
  padding-right: 0;
}

.result-table-cell-mark__photo-block {
  display: flex;
  justify-content: center;
  align-items: center;
  flex-direction: column;
  gap: 0.5rem;
  padding-bottom: 0.5rem;
}

.result-table-cell-mark__photo-wrapper {
  background: black;
  height: 200px;
  width: 100%;
  text-align: center;
}

img {
  max-width: 100%;
  height: 100%;
}
</style>
