import { defineStore } from 'pinia';
import {MarkForm} from 'src/models/form';
import {Tree} from 'src/models/tree';

const localStorageAuthor = 'breedersdb_mark_author';

export interface MarkState {
  selectedForm: MarkForm | null
  author: string
  date: string
  tree: Tree | null
}

export const useMarkStore = defineStore('mark', {
  state: (): MarkState => ({
    selectedForm: null,
    author: window.localStorage.getItem(localStorageAuthor) ?? '',
    date: (new Date()).toISOString().substring(0, 10),
    tree: null,
  }),


  getters: {},


  actions: {
    setAuthor(author: string) {
      this.author = author;
      window.localStorage.setItem(localStorageAuthor, author);
    }
  },
});
