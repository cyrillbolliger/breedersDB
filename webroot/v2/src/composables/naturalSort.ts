export default function naturalSort(array: string[]): string[] {
  const locale = navigator.languages[0] || navigator.language;
  return array.sort(
    (a, b) => a.localeCompare(b, locale, {numeric: true, ignorePunctuation: true})
  )
}
