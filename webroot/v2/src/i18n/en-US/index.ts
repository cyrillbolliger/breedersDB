export default {
  marks: {
    title: 'Marks',

    selectForm: {
      title: 'Select Form',
      tab: 'Form',
    },

    setMeta: {
      title: 'Set Meta Data',
      tab: 'Meta',
      author: 'Author',
      authorHint: 'The name of the person who does the rating.',
      date: 'Date',
      dateHint: 'The date of the rating.'
    },

    selectTree: {
      title: 'Select Tree',
      tab: 'Tree',
      scanQrCode: 'scan QR-code',
      manualEntry: 'enter publicid'
    },

    markTree: {
      title: 'Mark Tree',
      tab: 'Mark',
      missingDataError: 'Missing data.',
      setMeta: 'Add meta data',
      selectTree: 'Select tree',
      selectForm: 'Select form',
      saved: 'Marks saved.',
      addProperty: 'Add property',
      selectProperty: 'Select Property',
      propertyAlreadyExists: 'Property {property} can not be added a second time.',
    },
  },


  trees: {
    publicid: 'Publicid',
    convar: 'Convar',
    datePlanted: 'Date planted',
    dateEliminated: 'Date eliminated',
    experimentSite: 'Experiment site',
    row: 'Row',
    offset: 'Offset',
    note: 'Note'
  },


  varieties: {
    officialName: 'Official name',
    acronym: 'Acronym',
    plantBreeder: 'Plant breeder',
    registration: 'Registration',
    description: 'Description',
  },


  batches: {
    dateSowed: 'Date sowed',
    numbSeedsSowed: 'Number of seeds sowed',
    numbSproutsGrown: 'Number of sprouts grown',
    seedTray: 'Seed tray',
    datePlanted: 'Date planted',
    numbSproutsPlanted: 'Number of sprouts planted',
    patch: 'Patch',
    note: 'Note',
  },


  queries: {
    title: 'Queries',
    new: 'New query',
    unsaved: 'Unsaved query',

    titleNotUnique: 'This name is already in use.',
    duplicate: 'Duplicate',
    unsavedChanges: 'unsaved changes',

    query: 'Query',

    baseTable: 'Base',

    crossings: 'crossings',
    batches: 'batches',
    varieties: 'varieties',
    trees: 'trees',
    motherTrees: 'mother trees',
    scionsBundles: 'scions bundles',

    marks: 'marks',
    Marks: 'Marks',

    defaultFilter: 'Filter criteria',
    batchFilter: 'Filter criteria to select the batches',
    varietyFilter: 'Filter criteria to select the varieties',
    treeFilter: 'Filter criteria to select the trees',
    markFilter: 'Filter criteria to select the marks',

    noFilter: 'No filter criteria defined. All {entity} will be selected. Click the plus button below to add filter criteria.',

    simplifiable: 'Unnecessary complexity detected.',
    simplify: 'Simplify filter',
    invalid: 'Invalid filter rules. Rectify or delete them.',
    valid: 'Congrats, all rules are valid.',

    filter: {
      column: 'Column',
      comparator: 'Comparator',
      criteria: 'Criteria',

      equals: 'equals',
      notEquals: 'not equals',
      less: 'less than',
      lessOrEqual: 'less or equals',
      greater: 'greater than',
      greaterOrEqual: 'greater or equals',
      startsWith: 'starts with',
      startsNotWith: 'starts not with',
      contains: 'contains',
      notContains: 'contains not',
      endsWith: 'ends with',
      notEndsWith: 'ends not with',
      empty: 'is empty',
      notEmpty: 'is not empty',
      hasPhoto: 'has photo',
      isTrue: 'is true',
      isFalse: 'is false',

      add: 'Add',
      andFilter: 'and criteria',
      orFilter: 'or criteria',

      and: 'and',
      or: 'or',

      noResults: 'No results.',
    },

    invalidNoResults: 'Invalid filter rules. Rectify or delete them to get results.',
    results: 'Results',
    addColumn: 'Add Column',
    showRowsWithoutMarks: 'Show rows without marks',

    debugShow: 'Show debug info',
    debugHide: 'Hide debug info',

    altPhoto: 'Photo taken {date} by {author}',
    photo: 'Photo',
    downloadPhoto: 'Download photo',

    countSuffix: 'count',
    maxSuffix: 'max',
    minSuffix: 'min',
    meanSuffix: 'mean',
    medianSuffix: 'median',
    stdDevSuffix: 'std. deviation',

    yes: 'yes',
    no: 'no',

    download: 'Download'
  },

  general: {
    search: 'Search',
    loading: 'Loading...',
    retry: 'Retry',
    failedToLoadData: 'Failed to load data.',
    failedToSaveData: 'Failed to save data.',
    refreshList: 'Refresh list',
    next: 'Next',
    dismiss: 'Dismiss',
    navigation: 'Navigation',
    selected: 'selected',
    more: 'More',
    save: 'Save',
    delete: 'Delete',

    form: {
      required: 'Field is required',
      max255chars: 'Max. 255 characters allowed',
      save: 'Save',
    },
  },


  components: {
    util: {
      errorBanner: {
        dismiss: 'dismiss'
      },
      treeCard: {
        scanBtnLabel: 'Scan',
        tree: 'Tree',
        printBtnLabel: 'Print',
        printTitle: 'Print tree label',
        printDesc: 'Select regular to print a label with the publicid and the convar or anonymous to hide the convar.',
        printRegular: 'Regular',
        printAnonymous: 'Anonymous',
        windowError: 'Failed to open window for printing. Are you blocking popups?',
        noTree: 'Please Scan Tree'
      },
      codeScanner: {
        permissionRequest: 'Unable to access video stream. Please confirm permission request.',
        loadingMessage: '⌛ Loading video...',
      },
      list: {
        listMetaFiltered: 'Filtered list. Showing {showing} out of {total} items.',
        listMetaUnfiltered: '{total} items',
        nothingFound: 'Nothing found'
      }
    },
  },


  navigation: {
    markTrees: {
      title: 'Mark Trees',
      caption: 'Scan trees and rate them.'
    },
    trees: {
      title: 'Trees',
      caption: 'List of all trees.'
    },
    queries: {
      title: 'Queries',
      caption: 'Search the database.',
      titleLegacy: 'Queries (old)',
      captionLegacy: 'Old search interface.',
    },
  }
};
