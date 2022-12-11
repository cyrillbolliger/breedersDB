// Mocks all files ending in `.vue` showing them as plain Vue instances
declare module '*.vue' {
  import { ComponentOptions } from 'vue'
  const component: ComponentOptions
  // noinspection JSUnusedGlobalSymbols
  export default component
}
