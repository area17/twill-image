'use strict';

Object.defineProperty(exports, '__esModule', { value: true });

let intersectionObserver;

const ioEntryMap = new WeakMap();

const connection =
  navigator.connection || navigator.mozConnection || navigator.webkitConnection;

// These match the thresholds used in Chrome's native lazy loading
// @see https://web.dev/browser-level-image-lazy-loading/#distance-from-viewport-thresholds
const FAST_CONNECTION_THRESHOLD = `1250px`;
const SLOW_CONNECTION_THRESHOLD = `2500px`;

function createIntersectionObserver(callback) {
  const connectionType = connection && connection.effectiveType;

  // if we don't support intersectionObserver we don't lazy load (Sorry IE 11).
  if (!(`IntersectionObserver` in window)) {
    return function observe() {
      callback();
      return function unobserve() {}
    }
  }

  if (!intersectionObserver) {
    intersectionObserver = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            // Get the matching entry's callback and call it
            const callback = ioEntryMap.get(entry.target);
            callback && callback();
            // We only need to call it once
            ioEntryMap.delete(entry.target);
          }
        });
      },
      {
        rootMargin:
          connectionType === `4g` && !(connection && connection.saveData)
            ? FAST_CONNECTION_THRESHOLD
            : SLOW_CONNECTION_THRESHOLD,
      }
    );
  }

  return function observe(element) {
    if (element) {
      // Store a reference to the callback mapped to the element being watched
      ioEntryMap.set(element, callback);
      intersectionObserver.observe(element);
    }

    return function unobserve() {
      if (intersectionObserver && element) {
        ioEntryMap.delete(element);
        intersectionObserver.unobserve(element);
      }
    }
  }
}

// https://davidwalsh.name/javascript-debounce-function
// Returns a function, that, as long as it continues to be invoked, will not
// be triggered. The function will be called after it stops being called for
// N milliseconds. If `immediate` is passed, trigger the function on the
// leading edge, instead of the trailing.
function debounce(func, wait, immediate) {
  let timeout;
  return function () {
    const context = this;
    const args = arguments;
    const later = function () {
      timeout = null;
      if (!immediate) func.apply(context, args);
    };
    const callNow = immediate && !timeout;
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
    if (callNow) func.apply(context, args);
  }
}

const imageCache = new Set();

const storeImageloaded = (cacheKey) => {
  if (cacheKey) {
    imageCache.add(cacheKey);
  }
};

const hasImageLoaded = (cacheKey) => {
  return imageCache.has(cacheKey)
};

const hasNativeLazyLoadSupport = () =>
  typeof HTMLImageElement !== 'undefined' &&
  'loading' in HTMLImageElement.prototype;

class Wrapper {
  _unobserve() {}

  constructor(el) {
    this.el = el;
    this.main = el.querySelector('[data-main-image]');
    this.placeholder = el.querySelector('[data-placeholder-image]');
    this.isLoading = hasNativeLazyLoadSupport();
    this.isLoaded = false;
    this.onload = this.onload.bind(this);
    this.reveal = this.reveal.bind(this);
    this.cacheKey = JSON.stringify(this.main.src || this.main.dataset.src);

    this.main.onload = this.onload;

    if (this.isLoading) {
      this.reveal();
    }
  }

  set isLoaded(state) {
    this._isLoaded = state;
    this.main.style.opacity = state ? 1 : 0;
    state && this.unobserve();
  }

  get isLoaded() {
    return this._isLoaded
  }

  set unobserve(callback) {
    this._unobserve = callback;
  }

  get unobserve() {
    return this._unobserve
  }

  get isCached() {
    return hasImageLoaded(this.cacheKey)
  }

  reveal() {
    const sources = this.main.parentElement.querySelectorAll('source');
    if (sources) {
      sources.forEach((source) => {
        if (source.dataset.src) {
          source.setAttribute('src', source.dataset.src);
          delete source.dataset.src;
        }
        if (source.dataset.srcset) {
          source.setAttribute('srcset', source.dataset.srcset);
          delete source.dataset.srcset;
        }
      });
    }
    if (this.main.dataset.src) {
      this.main.setAttribute('src', this.main.dataset.src);
      delete this.main.dataset.src;
    }
    if (this.main.dataset.srcset) {
      this.main.setAttribute('srcset', this.main.dataset.srcset);
      delete this.main.dataset.srcset;
    }

    // if the main image is in the cache, onload function won't be called as an image was already loaded
    if (this.main.complete) {
      this.isLoaded = true;
    }
  }

  onload(e) {
    if (this.isLoaded) {
      return
    }

    storeImageloaded(this.cacheKey);

    const target = e.currentTarget;
    const img = new Image();
    img.src = target.currentSrc;

    if (img.decode) {
      // Decode the image through javascript to support our transition
      img
        .decode()
        .catch(() => {
          // ignore error, we just go forward
        })
        .then(() => {
          this.isLoaded = true;
        });
    } else {
      this.isLoaded = true;
    }
  }
}

class TwillImage {
  constructor() {
    this.reset = debounce(this._reset, 100).bind(this);

    this.start();
  }

  get images() {
    return document.querySelectorAll('[data-twill-image-wrapper]')
  }

  start() {
    this.images.forEach((image) => {
      if (!image.wrapper) {
        image.wrapper = new Wrapper(image);
      }
      const intersectionObserver = createIntersectionObserver(() => {
        image.wrapper.reveal();
        image.wrapper.isCached && (image.wrapper.isLoaded = true);
      });
      image.wrapper.unobserve = intersectionObserver(image);
    });
  }

  _reset() {
    this.images.forEach((image) => {
      image.wrapper && image.wrapper.unobserve && image.wrapper.unobserve();
      image.wrapper.unobserve = () => {};
      this.start();
    });
  }
}

exports.TwillImage = TwillImage;
