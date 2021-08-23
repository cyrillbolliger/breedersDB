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
import {useStore} from 'src/store';
import {LayoutBreadcrumbsInterface} from 'src/store/module-layout/state';
import {LayoutTabsInterface} from 'src/store/module-layout/state';
import {useI18n} from 'vue-i18n';

export default defineComponent({
  name: 'MainLayout',

  components: {
    NavLink
  },

  setup () {
    const store = useStore()
    const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
    const rightDrawerOpen = ref(false)

    const navLinks = [
      {
        title: t('navigation.markTrees.title'),
        caption: t('navigation.markTrees.caption'),
        icon: 'star',
        link: '/marks/select-form'
      },
    ];



    return {
      /* eslint-disable @typescript-eslint/no-unsafe-member-access */
      /* eslint-disable @typescript-eslint/no-unsafe-return */
      title: computed<string>(() => store.getters['layout/title']),
      back: computed<string|null>(() => store.getters['layout/back']),
      breadcrumbs: computed<LayoutBreadcrumbsInterface[]>(() => store.getters['layout/breadcrumbs']),
      tabs: computed<LayoutTabsInterface[]>(() => store.getters['layout/tabs']),
      /* eslint-enable @typescript-eslint/no-unsafe-member-access */
      /* eslint-enable @typescript-eslint/no-unsafe-return */
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
