// Cache if we've seen an image before so we don't both with
// lazy-loading
const imageCache = {};

const inImageCache = src => imageCache[src] || false;

const activateCacheForImage = src => {
  imageCache[src] = true;
};

let io;
let listeners;

const getIO = () => {
  if (
    typeof io === "undefined" &&
    typeof window !== "undefined" &&
    window.IntersectionObserver
  ) {
    io = new window.IntersectionObserver(
      entries => {
        for (let i = 0; i < entries.length; i += 1) {
          for (let j = 0; j < listeners.length; j += 1) {
            if (listeners[j][0] === entries[i].target) {
              // Edge doesn't currently support isIntersecting,
              // so also test for an intersectionRatio > 0
              if (
                entries[i].isIntersecting ||
                entries[i].intersectionRatio > 0
              ) {
                io.unobserve(listeners[j][0]);
                listeners[j][1](listeners[j][0]);
              }
            }
          }
        }
      },
      { root: null, rootMargin: "600px" }
    );
  }
  return io;
};

const listenToIntersections = (el, cb) => {
  getIO().observe(el);
  listeners.push([el, cb]);
};

const revealImage = ref => {
  const imgEl = ref.querySelectorAll("picture img")[0];
  const srcEl = ref.querySelectorAll("picture source")[0];
  const placeholderEl = ref.querySelectorAll(
    ".croustille-image-placeholder"
  )[0];

  const resolve = () => {
    srcEl.srcset = srcEl.dataset.srcset;
    imgEl.src = imgEl.dataset.src;
    placeholderEl.style.opacity = 0;
    imgEl.style.opacity = 1;
  };

  const onError = () => {
    throw new Error("Could not load/decode image.");
  };

  if (inImageCache(imgEl.dataset.src)) {
    resolve();
    return;
  }
  activateCacheForImage(imgEl.dataset.src);

  const img = new Image();

  if (!("decode" in img)) {
    img.onload = resolve;
    img.onError = onError;
  }

  img.sizes = srcEl.sizes;
  img.srcset = srcEl.dataset.srcset;
  img.src = imgEl.dataset.src;

  if ("decode" in img) {
    img
      .decode()
      .then(resolve)
      .catch(onError);
  }
};

class CroustilleImage {
  init() {
    this.observe();
    console.log("observe triggered");
  }

  // peut-être appeller d'un autre module
  observe() {
    const images = document.querySelectorAll(".croustille-image-wrapper");

    listeners = [];

    for (let i = 0; i < images.length; i += 1) {
      listenToIntersections(images[i], () => revealImage(images[i]));
    }
  }

  destroy() {
    if (io) {
      for (let j = 0; j < listeners.length; j += 1) {
        io.unobserve(listeners[j][0]);
        delete listeners[j];
      }
    }

    // this.observe();
  }
}

export default CroustilleImage;
