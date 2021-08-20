import {MarkForm, Tree} from 'components/models';

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
