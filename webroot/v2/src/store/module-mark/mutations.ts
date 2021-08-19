import { MutationTree } from 'vuex';
import { MarkStateInterface } from './state';
import {MarkForm} from 'components/models';

const mutation: MutationTree<MarkStateInterface> = {
  selectForm (state: MarkStateInterface, form: MarkForm) {
    state.selectedForm = form;
  }
};

export default mutation;
