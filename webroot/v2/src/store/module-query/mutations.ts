import {MutationTree} from 'vuex';
import {
  BaseTable,
  FilterComparatorOption,
  FilterCriteria,
  FilterDragObject,
  FilterLeaf,
  FilterOperand,
  FilterOption,
  FilterTree,
  FilterTreeRoot,
  QueryStateInterface
} from './state';
import filterHelper from 'src/store/module-query/filterHelper';

const mutation: MutationTree<QueryStateInterface> = {
  base(state: QueryStateInterface, base: BaseTable) {
    state.base = base
  },

  baseFilter(state: QueryStateInterface, baseFilter: FilterTreeRoot) {
    state.baseFilter = baseFilter
  },

  markFilter(state: QueryStateInterface, markFilter: FilterTreeRoot) {
    state.markFilter = markFilter
  },

  incrementFilterId(state: QueryStateInterface) {
    state.lastFilterId++
  },

  addRule(
    state: QueryStateInterface,
    payload: { node: FilterTree | FilterLeaf, operand: FilterOperand }
  ) {
    const parent = filterHelper.getFilterById(state, payload.node.parentId)

    if ( ! parent) {
      return false;
    }

    if ( ! ('children' in parent) || ! ('operand' in parent)) {
      // we cant attach anything to leafs
      return false;
    }

    if (1 >= parent.children.length) {
      // if there is none or only one child, the operand doesn't matter -> it can be changed
      parent.operand = payload.operand
    }

    if (parent.operand === payload.operand) {
      // attach the node, if the operand matches
      parent.children.push(payload.node);
      return true;
    }

    // there are at least two children and the operand differs
    // so we have to create a new parent, with the old parent
    // and the node of the payload as children. the new parent
    // can now have our operand.
    const oldParent = parent;
    const newParent = {
      id: ++state.lastFilterId,
      level: oldParent.level,
      type: oldParent.type,
      children: [oldParent],
      operand: payload.operand
    } as FilterTreeRoot | FilterTree;

    // replace the old parent with the new one
    filterHelper.replaceNode(state, oldParent, newParent);

    // make the old parent a child of the new parent
    (oldParent as FilterTree).parentId = newParent.id;
    oldParent.level = newParent.level + 1;
    filterHelper.regenerateChildLevels(oldParent);

    // add the node to the new parent
    payload.node.parentId = newParent.id;
    newParent.children.push(payload.node);
  },

  updateFilterColumn(
    state: QueryStateInterface,
    payload: { node: FilterLeaf, value: FilterOption }
  ) {
    payload.node.filter.column = payload.value;
  },

  updateFilterComparator(
    state: QueryStateInterface,
    payload: { node: FilterLeaf, value: FilterComparatorOption }
  ) {
    payload.node.filter.comparator = payload.value;
  },

  updateFilterCriteria(
    state: QueryStateInterface,
    payload: { node: FilterLeaf, value: FilterCriteria }
  ) {
    payload.node.filter.criteria = payload.value;
  },

  deleteFilter(
    state: QueryStateInterface,
    payload: { node: FilterLeaf }
  ) {
    // eslint-disable-line @typescript-eslint/no-unsafe-call
    filterHelper.deleteFilterFromTree(state, payload.node);
  },

  dragObject(
    state: QueryStateInterface,
    payload: FilterDragObject
  ) {
    state.dragObject = payload;
  },

  moveFilter(state: QueryStateInterface,
           payload: { subject: FilterTree | FilterLeaf, target: FilterTree | FilterLeaf, position: 'after' | 'before' }
  ) {
    const {subject, target, position} = payload
    // abort if subject === target
    if (subject.id === target.id) {
      return;
    }

    // abort if subject.type !== target.type
    // prevent mixing base and mark filter
    if (subject.type !== target.type) {
      return;
    }

    // abort if target is child of subject (circular)
    if (filterHelper.isDescendantOf(state, subject, target)) {
      return;
    }

    const targetParent = filterHelper.getFilterById(state, target.parentId);
    const subjectParent = filterHelper.getFilterById(state, subject.parentId);
    if ( ! targetParent
      || ! subjectParent
      || ! ('children' in targetParent)
      || ! ('children' in subjectParent)
    ) {
      return;
    }

    // delete subject from old location
    filterHelper.deleteFilterFromTree(state, subject);

    // insert subject in new location
    const targetIdx = targetParent.children.indexOf(target);
    const insertIdx = targetIdx + ('after' === position ? 1 : 0);
    targetParent.children.splice(insertIdx, 0, subject);

    // adapt subject
    subject.parentId = targetParent.id;
    subject.level = target.level;
    filterHelper.regenerateChildLevels(subject);
  },
};

export default mutation;
