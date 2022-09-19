import {i18n} from 'boot/i18n'
import {Notify, QNotifyCreateOptions} from 'quasar'
import {ref} from 'vue';
import {api as axios} from 'boot/axios';
import {AxiosError} from 'axios';

interface ApiResponse<T> {
  data: T
}

export default function useApi() {
  const t = i18n.global.t // eslint-disable-line @typescript-eslint/unbound-method

  const working = ref(false)

  function get<T>(url: string, cb: () => void = () => null): Promise<void | T> {
    working.value = true

    return axios.get<void | ApiResponse<T>>(url)
      .then(resp => {
        cb()
        working.value = false
        if (resp.data) {
          return resp.data.data
        }
      })
      .catch(error => {
        working.value = false
        cb()
        return handleError<T>(error as AxiosError<T>, t('general.failedToLoadData'), () => get<T>(url, cb))
      })
  }

  function post<T, R>(url: string, data: T, cb: () => void = () => null): Promise<void | R> {
    working.value = true

    let payload: T | { data: T }
    if (data instanceof FormData) {
      payload = data
    } else {
      payload = {data}
    }

    return axios.post<T | { data: T }, ApiResponse<{data: R}>>(url, payload)
      .then(resp => {
        if (resp.data) {
          cb()
          return resp.data.data
        }
      })
      .catch(error => {
        working.value = false
        cb()
        return handleError<R>(error as AxiosError<R>, t('general.failedToSaveData'), () => post<T, R>(url, data, cb))
      })
  }


  function handleError<T>(error: AxiosError<T>, message: string, cb: () => Promise<void | T>) {
    console.log(error.message)

    return new Promise<void>(resolve => {
      Notify.create({
        message,
        color: 'negative',
        actions: [
          {label: t('general.retry'), color: 'white', handler: resolve},
        ]
      } as QNotifyCreateOptions)
    })
      .then(() => cb());
  }


  return {
    working,
    get,
    post
  }
}
