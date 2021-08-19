import { GetterTree } from 'vuex';
import { StateInterface } from '../index';
import { MarkStateInterface } from './state';
import {MarkForm} from 'components/models';

const getters: GetterTree<MarkStateInterface, StateInterface> = {
  selectedForm (state): MarkForm|null {
    return state.selectedForm
  }
};

export default getters;
