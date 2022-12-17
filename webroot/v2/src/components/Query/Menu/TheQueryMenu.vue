<template>
  <div class="row no-wrap the-query-menu">
    <div class="row the-query-menu__title-path">
      <QueryGroup
        v-model:changed="queryGroupChanged"
      />
      <span class="text-grey-8 the-query-menu__title-path-separator">/</span>
      <QueryName
        v-model:changed="queryCodeChanged"
      />
    </div>
    <QueryActionButtons
      @saved="saved"
    />
  </div>
  <QueryDescription
    v-model:changed="queryDescriptionChanged"
  />
</template>

<script lang="ts" setup>

import {ref} from 'vue';
import QueryName from 'components/Query/Menu/QueryName.vue';
import QueryGroup from 'components/Query/Menu/QueryGroup.vue';
import {useRoute} from 'vue-router';
import QueryActionButtons from 'components/Query/Menu/QueryActionButtons.vue';
import QueryDescription from 'components/Query/Menu/QueryDescription.vue';

const route = useRoute();

const isNew = ref(route.params.id === 'new');
const queryGroupChanged = ref(isNew.value);
const queryCodeChanged = ref(isNew.value);
const queryDescriptionChanged = ref(isNew.value);

function saved() {
  queryGroupChanged.value = false;
  queryCodeChanged.value = false;
  queryDescriptionChanged.value = false;
}

</script>

<style scoped>
.the-query-menu {
  justify-content: space-between;
}

.the-query-menu__title-path {
  align-items: center;
  font-size: 1.5rem;
  max-width: calc(100% - 90px);
}

.the-query-menu__title-path-separator {
  padding: 0 1rem;
  font-size: 2rem;
}
</style>
