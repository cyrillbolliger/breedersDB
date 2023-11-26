import {ref, watch} from 'vue';
import {useRoute} from 'vue-router';

export default function useMarkType() {
  const route = useRoute()
  const type = ref(typeof route.params.type === 'string' ? route.params.type : '' )
  watch(() => route.params.type, (newType) => {
    if (typeof newType === 'string') {
      type.value = newType
    }
  })
  return type
}
