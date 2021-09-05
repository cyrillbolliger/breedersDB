import { store } from 'quasar/wrappers'
import { InjectionKey } from 'vue'
import {
  createStore, Module,
  Store as VuexStore,
  useStore as vuexUseStore,
} from 'vuex'

import layout from './module-layout'
import mark from './module-mark'

import {LayoutStateInterface} from 'src/store/module-layout/state';
import {MarkStateInterface} from 'src/store/module-mark/state';

/*
 * If not building with SSR mode, you can
 * directly export the Store instantiation;
 *
 * The function below can be async too; either use
 * async/await or return a Promise which resolves
 * with the Store instance.
 */

export interface StateInterface {
  layout: Module<LayoutStateInterface, StateInterface>,
  mark: Module<MarkStateInterface, StateInterface>
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
      layout,
      mark
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
