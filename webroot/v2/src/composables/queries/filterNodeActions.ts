import {FilterNode} from 'src/models/query/filterNode';
import {FilterOperand} from 'src/models/query/filterTypes';
import {FilterRule} from 'src/models/query/filterRule';

export default function useFilterNodeActions() {
  function addLeaf(parent: FilterNode, operand: FilterOperand) {
    const rule = new FilterRule();

    if (parent.getChildCount() <= 1) {
      parent.setChildrensOperand(operand);
    }

    if (parent.getChildrensOperand() === operand) {
      const leaf = FilterNode.FilterLeaf(parent, rule);
      parent.appendChild(leaf);

      return;
    }

    const parentsOperand = parent.getChildrensOperand();
    if (!parentsOperand) {
      throw Error('Failed to add leaf: Missing childrensOperand on parent node.');
    }

    const leaf = FilterNode.FilterLeaf(parent, rule);
    const intermediateNode = FilterNode.FilterNode(
      parentsOperand,
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

  return {
    addLeaf,
    moveNode,
  }
}
