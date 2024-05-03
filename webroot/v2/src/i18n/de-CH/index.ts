export default {
  marks: {
    title: {
      tree: "Baum bewerten",
      variety: "Sorte bewerten",
      batch: "Los bewerten"
    },
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
      dateHint: "Das Datum der Bewertung.",
      hasTarget: "Das gleiche Objekt mehrfach bewerten (statistische Bewertung).",
      markTotalTarget: "Bewertungen pro Objekt",
      markTotalTargetHint: "Wie viele Bewertungen willst du pro Objekt hinzufügen?"
    },
    selectTree: {
      title: "Baum auswählen",
      tab: "Baum",
      scanQrCode: "QR-Code scannen",
      manualEntry: "Publicid eingeben"
    },
    selectBatch: {
      title: "Los auswählen",
      tab: "Los",
      searchSelectLabel: "Los suchen"
    },
    selectVariety: {
      title: "Sorte auswählen",
      tab: "Sorte",
      searchSelect: "Suchen",
      searchSelectLabel: "Sorte suchen"
    },
    markTree: {
      title: "Baum bewerten",
      tab: "Bewerten",
      selectTree: "Baum auswählen"
    },
    markBatch: {
      title: "Los bewerten",
      tab: "Bewerten"
    },
    markVariety: {
      title: "Sorte bewerten",
      tab: "Bewerten",
      selectVariety: "Sorte auswählen"
    },
    markObj: {
      addProperty: "Eigenschaft hinzufügen",
      selectProperty: "Eigenschaft auswählen",
      propertyAlreadyExists: "Eigenschaft {property} kann kein zweites Mal hinzugefügt werden.",
      missingDataError: "Fehlende Daten.",
      selectForm: "Formular auswählen",
      saved: "Bewertungen gespeichert.",
      setMeta: "Meta-Daten hinzufügen"
    },
    markCounter: {
      tree: "Während der letzten 24 Stunden hast du diesen Baum {count} Mal mit diesem Formular bewerten (Ziel {total}).",
      variety: "Während der letzten 24 Stunden hast du diese Sorte {count} Mal mit diesem Formular bewerten (Ziel {total}).",
      batch: "Während der letzten 24 Stunden hast du dieses Los {count} Mal mit diesem Formular bewerten (Ziel {total}).",
      reset: "Zähler zurücksetzen"
    }
  },
  trees: {
    publicid: 'Publicid',
    convar: 'Convar',
    datePlanted: "Pflanzdatum",
    dateEliminated: "Eliminierungsdatum",
    experimentSite: "Versuchsort",
    row: "Zeile",
    offset: 'Offset',
    note: "Bemerkung"
  },
  varieties: {
    officialName: "Offizieller Name",
    acronym: "Kürzel",
    plantBreeder: "Züchter",
    registration: "Registrierung",
    description: "Beschreibung"
  },
  batches: {
    dateSowed: "Datum Aussaat",
    numbSeedsSowed: "Anzahl ausgesähter Samen",
    numbSproutsGrown: "Anzahl gekeimter Sprossen",
    seedTray: "Saatschale",
    datePlanted: "Pflanzdatum",
    numbSproutsPlanted: "Anzahl ausgepflanzter Sprossen",
    patch: "Beet",
    note: "Bemerkung"
  },
  queries: {
    title: "Abfragen",
    add: "Abfrage hinzufügen",
    unsaved: "Ungespeicherte Abfrage",
    group: "Gruppe",
    editGroups: "Gruppen bearbeiten",
    queryGroupSaveFailed: "Speichern fehlgeschlagen. Versuche es mit einem anderen Namen.",
    addQueryGroup: "Gruppe hinzufügen",
    queryGroupName: "Gruppe gespeichert",
    selectQueryGroup: "Gruppe wählen",
    description: "Beschreibung",
    titleNotUnique: "Dieser Name wird schon verwendet.",
    duplicate: "Duplizieren",
    query: "Abfrage",
    baseTable: "Basis",
    crossings: "Kreuzungen",
    batches: "Lose",
    varieties: "Sorten",
    trees: "Bäume",
    motherTrees: "Mutterbäume",
    scionsBundles: "Reiserbündel",
    marks: "Bewertungen",
    Marks: "Bewertungen",
    defaultFilter: "Filterkriterien",
    batchFilter: "Filterkriterien um die Lose auszuwählen",
    varietyFilter: "Filterkriterien um die Sorten auszuwählen",
    treeFilter: "Filterkreiterien um die Bäume auszuwählen",
    markFilter: "Filterkriterien um die Bewertungen auszuwählen",
    noFilter: "Keine Filterkriterien definiert. Alle {entity} werden ausgewählt. Klicke auf die Plus-Schaltfläche unten, um Filterkriterien hinzuzufügen.",
    simplifiable: "Unnötige Komplexität erkannt.",
    simplify: "Filter vereinfachen",
    invalid: "Ungültige Filterregeln. Korrigiere oder lösche sie.",
    valid: "Glückwunsch, alle Regeln sind gültig.",
    filter: {
      column: "Spalte",
      comparator: "Operation",
      criteria: "Kriterium",
      equals: "ist gleich",
      notEquals: "ist nicht gleich",
      less: "kleiner als",
      lessOrEqual: "kleiner oder gleich",
      greater: "grösser als",
      greaterOrEqual: "grösser oder gleich",
      startsWith: "beginnt mit",
      startsNotWith: "beginnt nicht mit",
      contains: "enthält",
      notContains: "enthält nicht",
      endsWith: "endet mit",
      notEndsWith: "endet nicht mit",
      empty: "ist leer",
      notEmpty: "ist nicht leer",
      hasPhoto: "hat Foto",
      isTrue: "ist wahr",
      isFalse: "ist falsch",
      add: "Hinzufügen",
      andFilter: "und Kriterien",
      orFilter: "oder Kriterien",
      and: "und",
      or: "oder",
      noResults: "Keine Ergebnisse."
    },
    invalidNoResults: "Ungültige Filterregeln. Korrigiere oder lösche sie um Resultate zu erhalten.",
    results: "Ergebnisse",
    addColumn: "Spalte hinzufügen",
    showRowsWithoutMarks: "Zeilen ohne Bewertungen anzeigen",
    debugShow: "Debug-Info anzeigen",
    debugHide: "Debug-Info ausblenden",
    altPhoto: "Foto aufgenommen am {date} von {author}",
    photo: "Foto",
    downloadPhoto: "Foto herunterladen",
    countSuffix: "Anzahl",
    maxSuffix: "Max",
    minSuffix: "Min",
    meanSuffix: "Durchschnitt",
    medianSuffix: "Median",
    stdDevSuffix: "Standardabweichung",
    yes: "Ja",
    no: "Nein",
    download: "Herunterladen"
  },
  general: {
    search: "Suchen",
    typeToSearch: "Tippen, um zu suchen",
    noResults: "Keine Ergebnisse",
    moreResults: "Es werden {limit} von {count} Ergebnissen angezeigt. Verfeinere die Suche, wenn das gewünschte Resultat nicht in der Liste erscheint.",
    loading: "Laden...",
    retry: "Wiederholen",
    failedToLoadData: "Fehler beim Laden der Daten.",
    failedToSaveData: "Fehler beim Speichern der Daten.",
    failedToDeleteData: "Fehler beim Löschen der Daten.",
    refreshList: "Liste aktualisieren",
    next: "Nächste",
    dismiss: "Verwerfen",
    navigation: 'Navigation',
    selected: "ausgewählt",
    more: "Mehr",
    save: "Speichern",
    saved: "Gespeichert",
    edit: "Bearbeiten",
    delete: "Löschen",
    close: "Schliessen",
    form: {
      required: "Feld ist erforderlich",
      max255chars: "Max. 255 Zeichen erlaubt",
      save: "Speichern"
    }
  },
  components: {
    treeCard: {
      scanBtnLabel: "Scannen",
      tree: "Baum",
      printBtnLabel: "Drucken",
      printTitle: "Etikette drucken",
      printDesc: "Wähle normal um ein Etikett mit Publicid und Convar zu drucken oder anonym um das Convar wegzulassen.",
      printRegular: "Normal",
      printAnonymous: "Anonymisiert",
      windowError: "Öffnen des Druckfensters fehlgeschlagen. Werden Popups blockiert?",
      noTree: "Bitte Baum scannen",
      noPrint: "Fehlende Druckdaten"
    },
    varietyCard: {
      noVariety: "Bitte Sorte auswählen"
    },
    batchCard: {
      noBatch: "Bitte Los auswählen"
    },
    util: {
      errorBanner: {
        dismiss: "verwerfen"
      },
      codeScanner: {
        permissionRequest: "Zugriff auf den Video-Stream nicht möglich. Bitte die Berechtigungsanfrage bestätigen.",
        loadingMessage: "⌛ Video wird geladen..."
      },
      list: {
        listMetaFiltered: "Gefilterte Liste. Zeige {showing} von {total} Elementen.",
        listMetaUnfiltered: "{total} Elemente",
        nothingFound: "Nichts gefunden"
      },
      search: "Suchen"
    }
  },
  navigation: {
    trees: {
      title: "Bäume",
      caption: "Liste aller Bäume."
    },
    markTrees: {
      title: "Baum bewerten",
      caption: ''
    },
    markVarieties: {
      title: "Sorten bewerten",
      caption: ''
    },
    markBatches: {
      title: "Lose bewerten",
      caption: ''
    },
    queries: {
      title: "Abfragen",
      caption: "Datenbank durchsuchen.",
      titleLegacy: "Abfragen (alt)",
      captionLegacy: "Alte Abfrageoberfläche."
    }
  }
};