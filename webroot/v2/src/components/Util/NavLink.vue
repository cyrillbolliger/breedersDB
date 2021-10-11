<template>
  <q-item
    clickable
    tag="a"
    :to="routerLink"
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
      default: '#'
    },

    icon: {
      type: String,
      default: ''
    }
  },

  setup(props) {
    const routerLink = computed(() => {
      return absUrl(props.link) ? null : props.link
    })
    const externalLink = computed(() => {
      return absUrl(props.link) ? props.link : null
    })

    return {
      routerLink,
      externalLink
    }
  }
})
</script>
