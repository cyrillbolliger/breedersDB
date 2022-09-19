import {useQueryStore} from 'stores/query';
import {FilterNode} from 'src/models/query/filterNode';
import type {FilterOperand} from 'src/models/query/filterTypes';
import {FilterRule} from 'src/models/query/filterRule';

export default function useFilter() {
  const store = useQueryStore();

  function addLeaf(parent: FilterNode, operand: FilterOperand) {
    const rule: FilterRule = {
      column: undefined,
      comparator: undefined,
      criteria: undefined,
    };

    if (parent.getChildCount() <= 1) {
      parent.setChildrensOperand(operand);
    }

    if (parent.getChildrensOperand() === operand) {
      const leaf = FilterNode.FilterLeaf(parent, rule);
      parent.appendChild(leaf);

      return;
    }

    const leaf = FilterNode.FilterLeaf(parent, rule);
    const intermediateNode = FilterNode.FilterNode(
      parent.getChildrensOperand()!,
      parent
    );

    intermediateNode.setChildren(parent.getChildren());
    parent.setChildren([intermediateNode, leaf]);
    parent.setChildrensOperand(operand);

    return;
  }

  function moveNode(subject: FilterNode, target: FilterNode, position: 'before' | 'after') {
    if (target.isDescendantOf(subject)) {
      throw Error('Failed to move node. Target can\'t be descendant of subject.');
    }

    const targetParent = target.getParent();
    const subjectParent = subject.getParent();

    if ( ! targetParent || ! subjectParent) {
      const what = ! targetParent ? 'Target' : 'Subject';
      throw Error(`Failed to move node: ${what} node has no parent.`);
    }

    subject.remove();

    const targetParentsChildren = targetParent.getChildren();
    const targetIdx = targetParentsChildren.indexOf(target);
    const insertIdx = position === 'before' ? targetIdx : targetIdx + 1;
    targetParentsChildren.splice(insertIdx, 0, subject);
    targetParent.setChildren(targetParentsChildren);
  }

  function getBaseFilter() {
    return store.baseFilter;
  }

  function getMarkFilter() {
    return store.markFilter
  }

  return {
    getBaseFilter,
    getMarkFilter,
    addLeaf,
    moveNode,
  }
}
