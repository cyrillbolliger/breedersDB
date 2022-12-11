<template>
  <q-item
    clickable
    tag="a"
    @click="navigateTo(routerLink)"
    :href="externalLink"
  >
    <q-item-section
      v-if="icon"
      avatar
    >
      <q-icon :name="icon" />
    </q-item-section>

    <q-item-section>
      <q-item-label>{{ title }}</q-item-label>
      <q-item-label caption>
        {{ caption }}
      </q-item-label>
    </q-item-section>
  </q-item>
</template>

<script lang="ts">
import {computed, defineComponent} from 'vue'
import {RouteLocationRaw, useRouter} from 'vue-router';

const absUrl = function(url: string) {
  return /^https?:\/\//.test(url);
}

export default defineComponent({
  name: 'NavLink',
  props: {
    title: {
      type: String,
      required: true
    },

    caption: {
      type: String,
      default: ''
    },

    link: {
      type: String,
      default: '#',
      required: true
    },

    icon: {
      type: String,
      default: ''
    }
  },

  setup(props) {
    const router = useRouter();

    const routerLink = computed(() => {
      return absUrl(props.link) ? null : props.link
    })
    const externalLink = computed(() => {
      return absUrl(props.link) ? props.link : null
    })

    function navigateTo(routerLink: string|null){
      if (routerLink) {
        void router
          .push(routerLink as RouteLocationRaw)
      }
    }

    return {
      routerLink,
      externalLink,
      navigateTo,
    }
  }
})
</script>
