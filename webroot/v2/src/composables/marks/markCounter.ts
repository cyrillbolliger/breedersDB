import {computed, ref} from 'vue';

const storageKey = 'breedersdb_mark_counter';

type Counters = {
  lastModified: string, // ISO date
  form_id: number,
  tree_id?: number,
  variety_id?: number,
  batch_id?: number,
  count: number,
}[];

type useMarkCounterProps = {
  form_id: number,
  tree_id?: number,
  variety_id?: number,
  batch_id?: number
};

function getCounters() {
  const storage = localStorage.getItem(storageKey);
  return storage ? JSON.parse(storage) as Counters : [];
}

function garbageCollect() {
  const validCounters = getCounters().filter(counter => {
    const date = new Date(counter.lastModified);
    const today = new Date();
    const diff = today.getTime() - date.getTime();
    return diff < 1000 * 60 * 60 * 24; // 24 hours
  });
  localStorage.setItem(storageKey, JSON.stringify(validCounters));
}

export default function useMarkCounter({form_id, tree_id, variety_id, batch_id}: useMarkCounterProps) {
  const count = ref(0);

  function get() {
    garbageCollect();
    const counter = getCounters().find(counter => {
      return counter.form_id === form_id
        && counter.tree_id === tree_id
        && counter.variety_id === variety_id
        && counter.batch_id === batch_id;
    });
    count.value = counter?.count || 0;
  }

  function set(value: number) {
    const counter = {
      lastModified: new Date().toISOString(),
      form_id,
      tree_id,
      variety_id,
      batch_id,
      count: value,
    };

    const otherCounters = getCounters().filter(counter => {
      return counter.form_id !== form_id
        || counter.tree_id !== tree_id
        || counter.variety_id !== variety_id
        || counter.batch_id !== batch_id;
    });

    const newCounters = [
      counter,
      ...otherCounters,
    ];

    localStorage.setItem(storageKey, JSON.stringify(newCounters));
    get();
  }

  return computed<number>({
    get: () => {get(); return count.value},
    set: (value: number) => set(value)
  });
}
