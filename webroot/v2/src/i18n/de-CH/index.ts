export default {
  marks: {
    title: "Bewertungen",
    selectForm: {
      title: "Formular auswählen",
      tab: "Formular"
    },
    setMeta: {
      title: "Meta-Daten festlegen",
      tab: 'Meta',
      author: "Autor",
      authorHint: "Der Name der Person, die bewertet.",
      date: "Datum",
      dateHint: "Das Datum der Bewertung."
    },
    selectTree: {
      title: "Baum auswählen",
      tab: "Baum",
      scanQrCode: "QR-Code scannen",
      manualEntry: "Publicid eingeben"
    },
    markTree: {
      title: "Baum bewerten",
      tab: "Bewerten",
      missingDataError: "Fehlende Daten.",
      setMeta: "Meta-Daten hinzufügen",
      selectTree: "Baum auswählen",
      selectForm: "Formular auswählen",
      saved: "Bewertungen gespeichert.",
      addProperty: "Eigenschaft hinzufügen",
      selectProperty: "Eigenschaft auswählen",
      propertyAlreadyExists: "Eigenschaft {property} kann kein zweites Mal hinzugefügt werden."
    }
  },
  trees: {
    publicid: 'Publicid'
  },
  queries: {
    title: 'Queries',
    query: 'Query',
    baseTable: 'Base',
    crossings: 'Crossings',
    batches: 'Batches',
    varieties: 'Varieties',
    trees: 'Trees',
    motherTrees: 'Mother Trees',
    scionsBundles: 'Scions Bundles',
    defaultFilter: 'Filter criteria',
    batchFilter: 'Filter criteria to select the batches',
    varietyFilter: 'Filter criteria to select the varieties',
    treeFilter: 'Filter criteria to select the trees',
    markFilter: 'Filter criteria to select the marks',
    simplifiable: 'Unnecessary complexity detected.',
    simplify: 'Simplify filter',
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
      or: 'or'
    },
    results: 'Results'
  },
  general: {
    search: "Suchen",
    loading: "Laden...",
    retry: "Wiederholen",
    failedToLoadData: "Fehler beim Laden der Daten.",
    failedToSaveData: "Fehler beim Speichern der Daten.",
    refreshList: "Liste aktualisieren",
    next: "Nächste",
    dismiss: "Verwerfen",
    navigation: 'Navigation',
    selected: "ausgewählt",
    form: {
      required: "Feld ist erforderlich",
      max255chars: "Max. 255 Zeichen erlaubt",
      save: "Speichern"
    }
  },
  components: {
    util: {
      errorBanner: {
        dismiss: "verwerfen"
      },
      treeCard: {
        scanBtnLabel: "Scannen",
        tree: "Baum",
        printBtnLabel: "Drucken",
        printTitle: "Etikette drucken",
        printDesc: "Wähle normal um ein Etikett mit Publicid und Convar zu drucken oder anonym um das Convar wegzulassen.",
        printRegular: "Normal",
        printAnonymous: "Anonymisiert",
        windowError: "Öffnen des Druckfensters fehlgeschlagen. Werden Popups blockiert?",
        noTree: "Bitte Baum scannen"
      },
      codeScanner: {
        permissionRequest: "Zugriff auf den Video-Stream nicht möglich. Bitte die Berechtigungsanfrage bestätigen.",
        loadingMessage: "⌛ Video wird geladen..."
      },
      list: {
        listMetaFiltered: "Gefilterte Liste. Zeige {showing} von {total} Elementen.",
        listMetaUnfiltered: "{total} Elemente",
        nothingFound: "Nichts gefunden"
      }
    }
  },
  navigation: {
    markTrees: {
      title: "Baum bewerten",
      caption: "Scanne Bäume und bewerte sie."
    },
    trees: {
      title: "Bäume",
      caption: "Liste aller Bäume."
    },
    queries: {
      title: 'Queries',
      caption: 'Search the database.'
    }
  }
};