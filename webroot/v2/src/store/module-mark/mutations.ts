import {MutationTree} from 'vuex';
import {MarkStateInterface} from './state';
import {MarkForm, Tree} from 'components/models';

const mutation: MutationTree<MarkStateInterface> = {
  selectForm(state: MarkStateInterface, form: MarkForm) {
    state.selectedForm = form;
  },
  author(state: MarkStateInterface, author: string) {
    state.author = author
  },
  date(state: MarkStateInterface, date: string) {
    state.date = date
  },
  tree(state: MarkStateInterface, tree: Tree) {
    state.tree = tree
  }
};

export default mutation;
