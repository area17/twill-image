let intersectionObserver

const ioEntryMap = new WeakMap()

const connection =
  navigator.connection || navigator.mozConnection || navigator.webkitConnection

// These match the thresholds used in Chrome's native lazy loading
// @see https://web.dev/browser-level-image-lazy-loading/#distance-from-viewport-thresholds
const FAST_CONNECTION_THRESHOLD = `1250px`
const SLOW_CONNECTION_THRESHOLD = `2500px`

export function createIntersectionObserver(callback) {
  const connectionType = connection && connection.effectiveType

  // if we don't support intersectionObserver we don't lazy load (Sorry IE 11).
  if (!(`IntersectionObserver` in window)) {
    return function observe() {
      callback()
      return function unobserve() {}
    }
  }

  if (!intersectionObserver) {
    intersectionObserver = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            // Get the matching entry's callback and call it
            const callback = ioEntryMap.get(entry.target)
            callback && callback()
            // We only need to call it once
            ioEntryMap.delete(entry.target)
          }
        })
      },
      {
        rootMargin:
          connectionType === `4g` && !(connection && connection.saveData)
            ? FAST_CONNECTION_THRESHOLD
            : SLOW_CONNECTION_THRESHOLD,
      }
    )
  }

  return function observe(element) {
    if (element) {
      // Store a reference to the callback mapped to the element being watched
      ioEntryMap.set(element, callback)
      intersectionObserver.observe(element)
    }

    return function unobserve() {
      if (intersectionObserver && element) {
        ioEntryMap.delete(element)
        intersectionObserver.unobserve(element)
      }
    }
  }
}
