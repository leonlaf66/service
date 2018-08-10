import { select } from 'xpath'
import { DOMParser as dom } from 'xmldom'

export function xmlRender (xml) {
  return new dom().parseFromString(xml)
}

export const xpath = select