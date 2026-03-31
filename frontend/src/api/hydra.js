/**
 * API Platform JSON-LD collection envelope.
 */
export function hydraMember(response) {
  const d = response?.data
  if (Array.isArray(d)) {
    return d
  }
  if (d && Array.isArray(d['hydra:member'])) {
    return d['hydra:member']
  }
  return []
}
