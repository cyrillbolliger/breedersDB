import {
  FilterChild,
  FilterLeaf,
  FilterTree,
  FilterTreeRoot,
  FilterType,
  QueryStateInterface
} from 'src/store/module-query/state';

function getFilterById(state: QueryStateInterface, id: number): (FilterTreeRoot | FilterTree | FilterLeaf | false) {
  const candidate = findById(state.baseFilter, id);
  if (candidate) {
    return candidate;
  }

  return findById(state.markFilter, id);
}

function findById(subject: FilterTreeRoot | FilterLeaf, id: number): (FilterTreeRoot | FilterTree | FilterLeaf | false) {
  if (subject.id === id) {
    return subject;
  }

  if ('children' in subject) {
    for (const child of subject.children) {
      const candidate = findById(child, id)
      if (candidate) {
        return candidate;
      }
    }
  }

  return false;
}

function regenerateChildLevels(node: FilterTreeRoot | FilterLeaf) {
  if ( ! ('children' in node)) {
    return;
  }

  for (const child of node.children) {
    child.level = node.level + 1
    regenerateChildLevels(child);
  }
}

function deleteFilterFromTree(
  state: QueryStateInterface,
  node: FilterLeaf | FilterTree | FilterTreeRoot
) {
  let parent: FilterTreeRoot | FilterTree | FilterLeaf | false = false
  if ('parentId' in node) {
    parent = getFilterById(state, node.parentId);
  }
  if ( ! parent || ! ('children' in parent)) {
    return;
  }

  const idx = parent.children.indexOf((node as FilterTree | FilterLeaf))
  parent.children.splice(idx, 1);

  if (parent.children.length === 0) {
    deleteFilterFromTree(state, parent);
  }

  if (parent.children.length === 1) {
    const child = parent.children[0];
    if ('children' in child) {
      // the only child left is not a FilterTree
      // so replace the current FilterTree (node) with it
      replaceNode(state, parent, child);
    }

    // if the child and it's parent have the same operand, merge them
    if ( ! ('parentId' in child)) {
      return;
    }
    parent = getFilterById(state, child.parentId);
    if ( ! parent || ! ('operand' in parent) || ! ('operand' in child)) {
      return;
    }
    if (parent.operand !== child.operand) {
      return;
    }
    const childIdx = parent.children.indexOf(child);
    parent.children.splice(childIdx, 1);
    child.children
      .reverse()
      .forEach(node => {
          node.parentId = (parent as FilterTreeRoot | FilterTree).id;
          (parent as FilterTreeRoot | FilterTree)
            .children.splice(childIdx, 0, node);
        }
      );
    regenerateChildLevels(parent);
  }
}

function replaceNode(
  state: QueryStateInterface,
  currentNode: FilterLeaf | FilterTree | FilterTreeRoot,
  newNode: FilterLeaf | FilterTree | FilterTreeRoot,
) {
  newNode.level = currentNode.level
  regenerateChildLevels(newNode)

  if ( ! ('parentId' in currentNode)) {
    // the currentNode is the rootNode
    if ( ! ('children' in newNode)) {
      console.error('Can not replace FilterTreeRoot with FilterLeaf.');
      return;
    }

    // reconstruct newNode as newRootNode to be sure there is no parentId
    const newRootNode: FilterTreeRoot = {
      id: newNode.id,
      level: newNode.level,
      type: newNode.type,
      children: newNode.children,
      operand: newNode.operand,
    }

    if (currentNode.type === FilterType.Base) {
      state.baseFilter = newRootNode;
    } else {
      state.markFilter = newRootNode;
    }

    return;
  }

  (newNode as FilterLeaf | FilterTree).parentId = currentNode.parentId;

  // insert the new node in the position of the current node
  const currentNodeParent = getFilterById(state, (currentNode as FilterChild).parentId);

  if ( ! currentNodeParent || ! ('children' in currentNodeParent)) {
    console.error('Current node\'s parent not found.', currentNode);
    return;
  }

  const currentNodeIdx = (currentNodeParent as FilterTree).children.indexOf(currentNode);
  currentNodeParent.children[currentNodeIdx] = newNode as FilterTree | FilterLeaf;
}

export default {
  regenerateChildLevels,
  getFilterById,
  deleteFilterFromTree,
  replaceNode,
}
