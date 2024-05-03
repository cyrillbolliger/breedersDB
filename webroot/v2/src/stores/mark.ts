import { defineStore } from 'pinia';
import {MarkForm} from 'src/models/form';
import {Tree} from 'src/models/tree';
import {Variety} from 'src/models/variety';
import {Batch} from 'src/models/batch';

const localStorageAuthor = 'breedersdb_mark_author';

export interface MarkState {
  selectedForm: MarkForm | null
  author: string
  date: string
  tree: Tree | null
  variety: Variety | null
  batch: Batch | null
  markTotalTarget: number
}

export const useMarkStore = defineStore('mark', {
  state: (): MarkState => ({
    selectedForm: null,
    author: window.localStorage.getItem(localStorageAuthor) ?? '',
    date: (new Date()).toISOString().substring(0, 10),
    tree: null,
    variety: null,
    batch: null,
    markTotalTarget: 1,
  }),


  getters: {},


  actions: {
    setAuthor(author: string) {
      this.author = author;
      window.localStorage.setItem(localStorageAuthor, author);
    },
    setTree(tree: Tree) {
      this.variety = null
      this.batch = null
      this.tree = tree
    },
    setVariety(variety: Variety) {
      this.batch = null
      this.tree = null
      this.variety = variety
    },
    setBatch(batch: Batch) {
      this.variety = null
      this.tree = null
      this.batch = batch
    }
  },
});
