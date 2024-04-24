## Base Filter Options

```ts
type PropertySchema = {
    name: string;
    label: string;
    options: {
        // PropertySchemaOptions
        allowEmpty: boolean;
        type: "integer" | "string"; // ... PropertySchemaOptionType
        validation:
            | {
                  // integer, float
                  max: number;
                  min: number;
                  step: number;
              }
            | {
                  // string
                  maxLen: number | null;
                  pattern: string | null;
              }
            | {
                  // enum
                  options: string[];
              };
    };
};

const baseFilterOptions: ComputedRef<PropertySchema[]> = [
    {
        name: "VarietiesView.id",
        label: "Sorten > Id",
        options: {
            type: "integer",
            allowEmpty: false,
            validation: {
                min: -9223372036854776000,
                max: 9223372036854776000,
                step: 1,
            },
        },
    },
    // ... more variety columns
    {
        name: "Mark.238",
        label: "Bewertungen > A_OS_Basale Langtriebe",
        options: {
            type: "integer",
            allowEmpty: false,
            validation: {
                min: 1,
                max: 9,
                step: 1,
            },
        },
    },
    // more mark columns
];
```

## Base Filter

```ts
const baseFilter: ComputedRef<FilterNode> = {
    children: [
        {
            children: [],
            childrensOperand: null,
            filterRule: {
                isValid: true,
                column: {
                    label: "Marks > D_2_Mehltau Blatt",
                    value: "Mark.7",
                    schema: {
                        name: "Marks > D_2_Mehltau Blatt",
                        label: "Mark.7",
                        options: {
                            type: "integer",
                            allowEmpty: false,
                            validation: {
                                min: 1,
                                max: 9,
                                step: 1,
                            },
                        },
                    },
                },
                comparator: {
                    label: "less or equals",
                    value: "<=",
                    type: ["integer", "double", "date"],
                },
                criteria: "1",
            },
            filterType: "base",
            id: 4,
            level: 1,
            parent: FilterNode, // pointer to the parent in this tree
            root: FilterNode, // pointer to the root in this tree
        },
    ],
    childrensOperand: "and",
    filterRule: null,
    filterType: "base",
    id: 3,
    level: 0,
    parent: null,
    root: FilterNode, // pointer to self
};
```

## Base Filter JSON

```ts
type FilterNodeJson = {
    id: number;
    level: number;
    children: FilterNodeJson[];
    childrensOperand: FilterOperand | null;
    filterRule: FilterRule | null;
    filterType: FilterType;
};

enum FilterOperand {
    And = "and",
    Or = "or",
}

interface FilterRule {
    column: FilterOption | undefined;
    comparator: FilterComparatorOption | undefined;
    criteria: FilterCriteria | undefined; // type FilterCriteria = string
    isValid: boolean;
}

enum FilterType {
    Base = "base",
    Mark = "mark",
}

interface FilterOption {
    label: string;
    value: string;
    schema: PropertySchema;
}

interface FilterComparatorOption {
    label: string;
    value: string;
    type: PropertySchemaOptionType[]; // see Base Filter Options above
}

interface PropertySchema {
    name: string; // e.g. TreesView.publicid
    label: string; // e.g. Tree -> Publicid
    options: PropertySchemaOptions; // see Base Filter Options above
}

const filterNodeJSON = {
    id: 6,
    level: 0,
    children: [
        {
            id: 7,
            level: 1,
            children: [],
            childrensOperand: null,
            filterRule: {
                isValid: true,
                column: {
                    label: "Marks > D_2_Mehltau Blatt",
                    value: "Mark.7",
                    schema: {
                        name: "Mark.7",
                        label: "Marks > D_2_Mehltau Blatt",
                        options: {
                            type: "integer",
                            allowEmpty: false,
                            validation: {
                                min: 1,
                                max: 9,
                                step: 1,
                            },
                        },
                    },
                },
                comparator: {
                    label: "less or equals",
                    value: "<=",
                    type: ["integer", "double", "date"],
                },
                criteria: "1",
            },
            filterType: "base",
        },
    ],
    childrensOperand: "and",
    filterRule: null,
    filterType: "base",
};
```
