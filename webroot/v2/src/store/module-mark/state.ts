import {MarkForm} from 'src/models/form';
import {Tree} from 'src/models/tree';

export const localStorageAuthor = 'breedersdb_mark_author';

export interface MarkStateInterface {
  selectedForm: MarkForm | null
  author: string
  date: string
  tree: Tree | null
}

function state(): MarkStateInterface {
  return {
    selectedForm: null,
    author: window.localStorage.getItem(localStorageAuthor) ?? '',
    date: (new Date()).toISOString().substr(0,10),
    tree: null,
  };
}

export default state;
