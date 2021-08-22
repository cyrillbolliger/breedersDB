import {MarkForm} from 'src/models/form';
import {Tree} from 'src/models/tree';

export interface MarkStateInterface {
  selectedForm: MarkForm | null
  author: string
  date: string
  tree: Tree | null
}

function state(): MarkStateInterface {
  return {
    selectedForm: null,
    author: '',
    date: '',
    tree: null,
  };
}

export default state;
