<template>
  <q-chip
    :color="bgColor"
    :label="mark.value"
    class="result-table-cell-mark__chip"
    size="sm"
  >
    <q-tooltip class="result-table-cell-mark__tooltip">
      <div
        v-if="'VARCHAR' === mark.field_type"
        class="result-table-cell-mark__text text-body2 text-bold"
      >{{ mark.value }}
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
            <strong>{{ mark.entity.publicid }}</strong><br>
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
    </q-tooltip>
  </q-chip>
</template>

<script lang="ts" setup>
import {computed, PropType} from 'vue';
import {MarkCell} from 'src/models/query/query';
import IconTree from 'components/Util/Icons/IconTree.vue';
import IconBatch from 'components/Util/Icons/IconBatch.vue';
import IconVariety from 'components/Util/Icons/IconVariety.vue';
import {useI18n} from 'vue-i18n';

const {t} = useI18n(); // eslint-disable-line @typescript-eslint/unbound-method

type MarkEntityType = 'tree' | 'batch' | 'variety';

const props = defineProps({
  mark: {
    type: Object as PropType<MarkCell>,
  }
})

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

const bgColor = computed(() => {
  switch (type.value) {
    case 'tree':
      return 'green-2';
    case 'variety':
      return 'amber-2';
    default:
      return 'grey-2';
  }
})

function n2br(text: string|null) {
  if (!text) {
    return text;
  }

  return text.replace(/\r*\n/g, '<br>');
}

function localizeDate(strDate: string|null) {
  if (!strDate) {
    return strDate;
  }

  return (new Date(strDate)).toLocaleDateString();
}

</script>

<style scoped>
.result-table-cell-mark__chip {
  max-width: 80px;
}

.result-table-cell-mark__tooltip {
  max-width: 250px;
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

</style>
