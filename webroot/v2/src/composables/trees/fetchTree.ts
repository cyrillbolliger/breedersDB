import {Tree} from 'src/models/tree';
import useApi from 'src/composables/api';

export function useFetchTree() {
  const {working, get} = useApi()
  const fetchTreeByPublicId = (publicid: string, after?: () => void) => {
    const params = new URLSearchParams()
    params.append('fields[]', 'publicid')
    params.append('term', publicid)
    const url = 'trees/get-tree?' + params.toString()

    return {
      data: get<Tree>(url, after),
      loading: working
    }
  }

  return {fetchTreeByPublicId}
}
