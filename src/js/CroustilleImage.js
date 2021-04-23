import { createIntersectionObserver } from './intersectionObserver'
import debounce from './debounce'
import Wrapper from './Wrapper'

class CroustilleImage {
  constructor() {
    this.reset = debounce(this._reset, 100).bind(this)

    this.start()
  }

  get images() {
    return document.querySelectorAll('[data-croustille-image-wrapper]')
  }

  start() {
    this.images.forEach((image) => {
      if (!image.wrapper) {
        image.wrapper = new Wrapper(image)
      }
      const intersectionObserver = createIntersectionObserver(() => {
        image.wrapper.reveal()
        image.wrapper.isCached && (image.wrapper.isLoaded = true)
      })
      image.wrapper.unobserve = intersectionObserver(image)
    })
  }

  _reset() {
    this.images.forEach((image) => {
      image.wrapper && image.wrapper.unobserve && image.wrapper.unobserve()
      image.wrapper.unobserve = () => {}
      this.start()
    })
  }
}

export default CroustilleImage
