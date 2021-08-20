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

      <q-toolbar v-if="tabs.length">
        <q-tabs>
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
        <q-item-label
          header
          class="text-grey-8"
        >
          Essential Links
        </q-item-label>

        <EssentialLink
          v-for="link in essentialLinks"
          :key="link.title"
          v-bind="link"
        />
      </q-list>
    </q-drawer>
  </q-layout>
</template>

<script lang="ts">
import EssentialLink from 'components/EssentialLink.vue'

const linksList = [
  {
    title: 'Docs',
    caption: 'quasar.dev',
    icon: 'school',
    link: 'https://quasar.dev'
  },
  {
    title: 'Github',
    caption: 'github.com/quasarframework',
    icon: 'code',
    link: 'https://github.com/quasarframework'
  },
  {
    title: 'Discord Chat Channel',
    caption: 'chat.quasar.dev',
    icon: 'chat',
    link: 'https://chat.quasar.dev'
  },
  {
    title: 'Forum',
    caption: 'forum.quasar.dev',
    icon: 'record_voice_over',
    link: 'https://forum.quasar.dev'
  },
  {
    title: 'Twitter',
    caption: '@quasarframework',
    icon: 'rss_feed',
    link: 'https://twitter.quasar.dev'
  },
  {
    title: 'Facebook',
    caption: '@QuasarFramework',
    icon: 'public',
    link: 'https://facebook.quasar.dev'
  },
  {
    title: 'Quasar Awesome',
    caption: 'Community Quasar projects',
    icon: 'favorite',
    link: 'https://awesome.quasar.dev'
  }
];

import {computed, defineComponent, ref} from 'vue'
import {useStore} from 'src/store';
import {LayoutBreadcrumbsInterface} from 'src/store/module-layout/state';
import {LayoutTabsInterface} from 'src/store/module-layout/state';

export default defineComponent({
  name: 'MainLayout',

  components: {
    EssentialLink
  },

  setup () {
    const $store = useStore();
    const rightDrawerOpen = ref(false)

    return {
      /* eslint-disable @typescript-eslint/no-unsafe-member-access */
      /* eslint-disable @typescript-eslint/no-unsafe-return */
      title: computed<string>(() => $store.getters['layout/title']),
      back: computed<string|null>(() => $store.getters['layout/back']),
      breadcrumbs: computed<LayoutBreadcrumbsInterface[]>(() => $store.getters['layout/breadcrumbs']),
      tabs: computed<LayoutTabsInterface[]>(() => $store.getters['layout/tabs']),
      /* eslint-enable @typescript-eslint/no-unsafe-member-access */
      /* eslint-enable @typescript-eslint/no-unsafe-return */
      essentialLinks: linksList,
      rightDrawerOpen,
      toggleRightDrawer() {
        rightDrawerOpen.value = !rightDrawerOpen.value
      }
    }
  }
})
</script>
