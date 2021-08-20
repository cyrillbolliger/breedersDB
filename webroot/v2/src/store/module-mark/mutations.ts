import { MutationTree } from 'vuex';
import { MarkStateInterface } from './state';
import {MarkForm} from 'components/models';

const mutation: MutationTree<MarkStateInterface> = {
  selectForm (state: MarkStateInterface, form: MarkForm) {
    state.selectedForm = form;
  },
  author (state: MarkStateInterface, author: string) {
    state.author = author
  },
  date (state: MarkStateInterface, date: string) {
    state.date = date
  }
};

export default mutation;
