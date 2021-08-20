import {i18n} from 'boot/i18n'
import {Notify} from 'quasar'
import {ref} from 'vue';
import {api as axios} from 'boot/axios';
import {AxiosError} from 'axios';


export default function useApi() {
  const t = i18n.global.t // eslint-disable-line @typescript-eslint/unbound-method

  const working = ref(false)

  function get(url: string) {
    return getInternal(url, () => null)
  }

  function getInternal(url: string, done: () => void) {
    working.value = true

    let data: any // eslint-disable-line

    return axios.get(url)
      .then(resp => data = resp.data.data) // eslint-disable-line
      .catch(error => handleLoadingError(url, error))
      .finally(() => {
        working.value = false
        done()
        return data // eslint-disable-line
      })
  }

  function handleLoadingError(url: string, error: Error | AxiosError) {
    console.log(error.message)
    Notify.create({
      message: t('general.failedToLoadData'),
      color: 'negative',
      actions: [
        {label: t('general.retry'), color: 'white', handler: (done: () => void) => getInternal(url, done)},
      ]
    })
  }

  return {
    working,
    get
  }
}
