import {FilterOperand, FilterType} from 'src/models/query/filterTypes';
import type {FilterRule} from 'src/models/query/filterRule';

export class FilterNode {
  private readonly id: number;
  private level = 0;
  private parent: FilterNode | null = null;
  private children: FilterNode[] = [];

  private readonly root: FilterNode;
  private static nextId = 0;

  private constructor(
    private readonly filterType: FilterType,
    parent: FilterNode | null,
    private childrensOperand: FilterOperand | null,
    private filterRule: FilterRule | null,
    root: FilterNode | null,
  ) {
    this.id = ++FilterNode.nextId;
    this.setParent(parent);

    this.root = root || this;
  }

  static FilterRoot(childrensOperand: FilterOperand, filterType: FilterType) {
    return new FilterNode(
      filterType,
      null,
      childrensOperand,
      null,
      null
    );
  }

  static FilterNode(childrensOperand: FilterOperand, parent: FilterNode) {
    return new FilterNode(
      parent.filterType,
      parent,
      childrensOperand,
      null,
      parent.root
    );
  }

  static FilterLeaf(parent: FilterNode, filterRule: FilterRule) {
    return new FilterNode(
      parent.filterType,
      parent,
      null,
      filterRule,
      parent.root
    );
  }

  getId() {
    return this.id;
  }

  getFilterType() {
    return this.filterType;
  }

  getLevel() {
    return this.level;
  }

  private setLevel(level: number | null = null) {
    if (null !== level) {
      this.level = level;
    } else if (this.isRoot()) {
      this.level = 0;
    } else {
      const parent = this.getParent();
      this.level = parent ? parent.level + 1 : 0;
    }

    this.children.forEach(child => child.setLevel(this.level + 1));
  }

  getParent(): FilterNode | null {
    return this.parent;
  }

  setParent(parent: FilterNode | null) {
    if (null === parent) {
      this.parent = null;
      this.setLevel(0);
    } else {
      this.parent = parent;
      this.setLevel(parent.getLevel() + 1);
    }
  }

  getChildren() {
    return this.children;
  }

  getChildCount() {
    return this.children.length;
  }

  hasChildren() {
    return this.getChildCount() > 0;
  }

  setChildren(children: FilterNode[]) {
    if (this.isLeaf()) {
      throw Error('Can not add children to filter leaf. Delete FilterRule first.');
    }

    this.children = children;
    this.children.forEach(child => {
      child.setParent(this);
    });
  }

  appendChild(child: FilterNode) {
    const children = this.getChildren();
    children.push(child);
    this.setChildren(children);
  }

  getChildrensOperand() {
    return this.childrensOperand;
  }

  setChildrensOperand(operand: FilterOperand) {
    this.childrensOperand = operand;
  }

  getFilterRule() {
    return this.filterRule;
  }

  setFilterRule(filterRule: FilterRule) {
    if (this.hasChildren()) {
      throw Error('Can not set filter rule on non leaf node. Remove children first.');
    }

    this.filterRule = filterRule;
  }

  isRoot() {
    return null === this.parent;
  }

  isLeaf() {
    return null !== this.filterRule;
  }

  isOnlyChild() {
    return this.getParent()?.getChildCount() === 1;
  }

  remove() {
    if (this.isRoot()) {
      throw Error('Can not remove root node.');
    }

    const parent = this.getParent();

    if ( ! parent) {
      throw Error('Can not remove node. Parent node not found.');
    }

    // remove this node
    const childrenOfParent = parent.getChildren();
    const currentChildIdx = childrenOfParent.indexOf(this);
    childrenOfParent.splice(currentChildIdx, 1);
    parent.setChildren(childrenOfParent);

    // remove parent node, if it isn't the root and
    // hasn't any children anymore
    if ( ! parent.isRoot() && ! parent.hasChildren()) {
      parent.remove();
    }
  }

  isDescendantOf(candidate: FilterNode): boolean {
    const parent = this.getParent();
    if ( ! parent) {
      return false;
    }

    if (parent === candidate) {
      return true;
    }

    return parent.isDescendantOf(candidate);
  }

  isSimplifiable() {
    if (this.mergeableIntoParent()) {
      return true;
    }

    for (const child of this.getChildren()) {
      if (child.isSimplifiable()) {
        return true;
      }
    }

    return false;
  }

  simplify() {
    for (const child of this.getChildren()) {
      if (child.isSimplifiable()) {
        child.simplify();
      }
    }

    if (this.mergeableIntoParent()) {
      this.mergeIntoParent();
    }
  }

  private mergeableIntoParent() {
    return this.hasSameOperandAsParent()
      || this.isMergeableOnlyChild();
  }

  private hasSameOperandAsParent() {
    return this.getChildrensOperand() === this.getParent()?.getChildrensOperand();
  }

  private isMergeableOnlyChild() {
    return ! this.isRoot()
      && this.isOnlyChild()
      && (
        (this.isLeaf() && !this.getParent()?.isRoot()) // mergeOnlyChildIntoGrandParent
        || ! this.isLeaf() // mergeOnlyChildsChildrenIntoParent
      );
  }

  private mergeIntoParentWithSameOperand() {
    const parent = this.getParent();

    if ( ! parent) {
      throw Error('Can not merge node with it\'s parent, as it hasn\'t got a parent.');
    }

    if (1 === this.getParent()?.getChildCount() && this.getChildrensOperand()) {
      this.getParent()?.setChildrensOperand(this.getChildrensOperand()!);
    }

    const parentsChildren = parent.getChildren();
    const nodeIdx = parentsChildren.indexOf(this);
    parentsChildren.splice(nodeIdx, 1, ...this.getChildren());
    parent.setChildren(parentsChildren);
  }

  private mergeIntoParent() {
    if (this.hasSameOperandAsParent()) {
      this.mergeIntoParentWithSameOperand();
    } else if (this.isMergeableOnlyChild()) {
      if ( ! this.isLeaf()) {
        this.mergeOnlyChildsChildrenIntoParent();
      } else {
        this.mergeOnlyChildIntoGrandParent();
      }
    }
  }

  private mergeOnlyChildsChildrenIntoParent() {
    if ( ! this.isOnlyChild() || this.isLeaf() || this.isRoot()) {
      throw Error('Can not merge only child\'s children into parent: Invalid only child.');
    }

    const parent = this.getParent();
    if ( ! parent) {
      throw Error('Can not merge only child\'s children into parent as there is no parent.');
    }

    const operand = this.getChildrensOperand();
    if ( ! operand) {
      throw Error('Can not merge only child\'s children into parent as only child is missing the childrensOperand.');
    }

    parent.setChildren(this.getChildren());
    parent.setChildrensOperand(operand);
  }

  private mergeOnlyChildIntoGrandParent() {
    if ( ! this.isOnlyChild() || ! this.isLeaf() || this.isRoot()) {
      throw Error('Can not merge only child into grand parent: Invalid only child.');
    }

    const parent = this.getParent();
    const grandParent = parent?.getParent();
    if ( ! parent || ! grandParent) {
      throw Error('Can not merge only child into grand parent as there is no grand parent.');
    }

    const gradParentsChildren = grandParent.getChildren();
    const parentIdx = gradParentsChildren.indexOf(parent);

    gradParentsChildren.splice(parentIdx, 1, this);
    grandParent.setChildren(gradParentsChildren);
  }
}
