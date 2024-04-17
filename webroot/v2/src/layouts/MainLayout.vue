<template>
  <q-ajax-bar
    ref="bar"
    position="top"
    color="accent"
    size="0.25rem"
  />

  <q-layout view="lHh Lpr lFf">
    <q-header
      elevated
      id="header"
    >

      <q-toolbar>
        <q-btn
          v-if="back"
          color="white"
          flat
          icon="arrow_back"
          round
        />

        <q-toolbar-title>
          {{ title }}
        </q-toolbar-title>

        <q-btn
          flat
          dense
          round
          icon="menu"
          aria-label="Menu"
          @click="toggleRightDrawer"
        />

      </q-toolbar>

      <q-toolbar v-if="breadcrumbs.length">
        <q-breadcrumbs active-color="white">
          <q-breadcrumbs-el
            v-for="(breadcrumb, index) in breadcrumbs"
            :key="index"
            :label="breadcrumb.label"
            :icon="breadcrumb.icon"
            :disable="breadcrumb.disable"
            :to="breadcrumb.to"
          />
        </q-breadcrumbs>
      </q-toolbar>

      <q-toolbar
        v-if="tabs.length"
      >
        <q-tabs
          outside-arrows
          style="max-width: calc(100vw - 24px)"
        >
          <q-route-tab
            v-for="(tab, index) in tabs"
            :key="index"
            :label="tab.label"
            :icon="tab.icon"
            :disable="tab.disable"
            :to="tab.to"
          />
        </q-tabs>
      </q-toolbar>

    </q-header>

    <q-page-container>
      <router-view/>
    </q-page-container>

    <q-drawer
      v-model="rightDrawerOpen"
      side="right"
      show-if-above
      bordered
      class="bg-grey-1"
    >
      <q-list>
        <q-item>
          <q-item-section>
            <q-item-label class="text-grey-8">
              {{ t('general.navigation') }}
            </q-item-label>
          </q-item-section>

          <q-item-section side top>
            <q-item-label>
              <q-btn
                icon="close"
                flat
                round
                @click="rightDrawerOpen = false"
              />
            </q-item-label>
          </q-item-section>
        </q-item>

        <NavLink
          v-for="link in navLinks"
          :key="link.title"
          v-bind="link"
        />
      </q-list>
    </q-drawer>
  </q-layout>
</template>

<script lang="ts">
import NavLink from 'components/Util/NavLink.vue'

import {computed, defineComponent, ref} from 'vue'
import {useI18n} from 'vue-i18n';
import {useLayoutStore} from 'stores/layout';

declare const webroot: string;

declare const cake: {
  data?: {
    user: {
      level: number
    }
  }
};

export default defineComponent({
  name: 'MainLayout',

  components: {
    NavLink
  },

  setup () {
    const store = useLayoutStore()
    const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
    const rightDrawerOpen = ref(false)

    const navLinks = [
      {
        title: t('navigation.trees.title'),
        caption: t('navigation.trees.caption'),
        icon: 'park',
        link: webroot + 'trees'
      },
      {
        title: t('navigation.markTrees.title'),
        caption: t('navigation.markTrees.caption'),
        icon: 'star',
        link: '/marks/tree/select-form'
      },
      {
        title: t('navigation.markVarieties.title'),
        caption: t('navigation.markVarieties.caption'),
        icon: 'star',
        link: '/marks/variety/select-form'
      },
      {
        title: t('navigation.markBatches.title'),
        caption: t('navigation.markBatches.caption'),
        icon: 'star',
        link: '/marks/batch/select-form'
      },
    ];

    if (cake.data?.user.level === 0) {
      navLinks.push(
        {
          title: t('navigation.queries.title'),
          caption: t('navigation.queries.caption'),
          icon: 'saved_search',
          link: '/queries'
        },
        {
          title: t('navigation.queries.titleLegacy'),
          caption: t('navigation.queries.captionLegacy'),
          icon: 'search_off',
          link: webroot + 'queries'
        }
      );
    }

    return {
      title: computed(() => store.title),
      back: computed(() => store.back),
      breadcrumbs: computed(() => store.breadcrumbs),
      tabs: computed(() => store.tabs),
      navLinks,
      rightDrawerOpen,
      toggleRightDrawer() {
        rightDrawerOpen.value = !rightDrawerOpen.value
      },
      t
    }
  }
})
</script>
