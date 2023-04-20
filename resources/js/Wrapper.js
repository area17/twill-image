const imageCache = new Set()

const storeImageloaded = (cacheKey) => {
  if (cacheKey) {
    imageCache.add(cacheKey)
  }
}

const hasImageLoaded = (cacheKey) => {
  return imageCache.has(cacheKey)
}

const hasNativeLazyLoadSupport = () =>
  typeof HTMLImageElement !== 'undefined' &&
  'loading' in HTMLImageElement.prototype

class Wrapper {
  _unobserve() {}

  constructor(el) {
    this.el = el
    this.main = el.querySelector('[data-main-image]')
    this.placeholder = el.querySelector('[data-placeholder-image]')
    this.isLoading = hasNativeLazyLoadSupport()
    this.isLoaded = false
    this.load = this.load.bind(this)
    this.reveal = this.reveal.bind(this)
    this.cacheKey = JSON.stringify(this.main.src || this.main.dataset.src)
    this.revealed = false

    this.main.dispatchEvent(new CustomEvent('twill-image:start'));

    this.main.onload = (e) => {
      this.load(e.currentTarget)

      this.main.dispatchEvent(new CustomEvent('twill-image:loaded'));
    }

    if (this.isLoading) {
      this.reveal()
    }

    if (this.isLoading || this.main.complete) {
      this.load(this.main)
    }
  }

  set isLoaded(state) {
    this._isLoaded = state

    if (state) {
      this.main.style.transition = 'opacity 500ms linear'
      this.main.style.opacity = 1
    } else {
      this.main.style.transition = ''
      this.main.style.opacity = 0
    }

    state && this.unobserve()
  }

  get isLoaded() {
    return this._isLoaded
  }

  set unobserve(callback) {
    this._unobserve = callback
  }

  get unobserve() {
    return this._unobserve
  }

  get isCached() {
    return hasImageLoaded(this.cacheKey)
  }

  reveal() {
    const sources = this.main.parentElement.querySelectorAll('source')
    if (sources) {
      sources.forEach((source) => {
        if (source.dataset.src) {
          source.setAttribute('src', source.dataset.src)
          delete source.dataset.src
        }
        if (source.dataset.srcset) {
          source.setAttribute('srcset', source.dataset.srcset)
          delete source.dataset.srcset
        }
      })
    }
    if (this.main.dataset.src) {
      this.main.setAttribute('src', this.main.dataset.src)
      delete this.main.dataset.src
    }
    if (this.main.dataset.srcset) {
      this.main.setAttribute('srcset', this.main.dataset.srcset)
      delete this.main.dataset.srcset
    }

    this.revealed = true

    this.main.dispatchEvent(new CustomEvent('twill-image:revealed'));
  }

  load(target) {
    if (this.isLoaded) {
      return
    }

    storeImageloaded(this.cacheKey)

    const img = new Image()
    img.src = target.currentSrc

    if (img.decode) {
      // Decode the image through javascript to support our transition
      img
        .decode()
        .catch(() => {
          // ignore error, we just go forward
        })
        .then(() => {
          this.isLoaded = true
        })
    } else {
      this.isLoaded = true
    }
  }
}

export default Wrapper
