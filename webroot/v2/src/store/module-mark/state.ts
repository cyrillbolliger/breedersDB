import {MarkForm} from 'components/models';

export interface MarkStateInterface {
  selectedForm: MarkForm|null;
}

function state(): MarkStateInterface {
  return {
    selectedForm: null,
  };
}

export default state;
