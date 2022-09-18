import { store } from 'quasar/wrappers'
import { InjectionKey } from 'vue'
import {
  createStore, Module,
  Store as VuexStore,
  useStore as vuexUseStore,
} from 'vuex'

import mark from './module-mark'
import query from './module-query'

import {MarkStateInterface} from 'src/store/module-mark/state';
import {QueryStateInterface} from 'src/store/module-query/state';

/*
 * If not building with SSR mode, you can
 * directly export the Store instantiation;
 *
 * The function below can be async too; either use
 * async/await or return a Promise which resolves
 * with the Store instance.
 */

export interface StateInterface {
  mark: Module<MarkStateInterface, StateInterface>,
  query: Module<QueryStateInterface, StateInterface>,
}

// provide typings for `this.$store`
declare module '@vue/runtime-core' {
  interface ComponentCustomProperties {
    $store: VuexStore<StateInterface>
  }
}

// provide typings for `useStore` helper
export const storeKey: InjectionKey<VuexStore<StateInterface>> = Symbol('vuex-key')

export default store(function (/* { ssrContext } */) {
  const Store = createStore<StateInterface>({
    modules: {
      mark,
      query,
    },

    // enable strict mode (adds overhead!)
    // for dev mode and --debug builds only
    strict: !!process.env.DEBUGGING
  })

  return Store;
})

export function useStore() {
  return vuexUseStore(storeKey)
}
