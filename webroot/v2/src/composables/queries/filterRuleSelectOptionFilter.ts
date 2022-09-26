import {QSelect} from 'quasar';
import {Ref} from 'vue';

export type FilterUpdateFn = (filterFn: () => void, selectFn: (ref: QSelect) => void) => void;

export function filterOptions<T>(
  value: string,
  update: FilterUpdateFn,
  allOptions: Array<T>,
  filteredOptions: Ref<Array<T>>,
  valueExtractorFn: (item: T) => string
) {
  update(
    () => setFilteredOptions(value, allOptions, filteredOptions, valueExtractorFn),
    ref => selectFirstOption(ref, value)
  );
}

function setFilteredOptions<T>(
  value: string,
  allOptions: Array<T>,
  filteredOptions: Ref<Array<T>>,
  valueExtractorFn: (item: T) => string
) {
  if (value === '') {
    filteredOptions.value = allOptions;
  } else {
    const locale = navigator.languages[0] || navigator.language;
    const needle = value.toLocaleLowerCase(locale);

    filteredOptions.value = allOptions.filter(
      v => valueExtractorFn(v).toLocaleLowerCase(locale).indexOf(needle) > -1
    );
  }
}

function selectFirstOption(ref: QSelect, value: string) {
  if (value !== '' && ref && ref.options && ref.options.length > 0) {
    ref.setOptionIndex(-1) // reset optionIndex in case there is something selected
    ref.moveOptionSelection(1, true) // focus the first selectable option and do not update the input-value
  }
}
