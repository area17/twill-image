# Changelog

All notable changes to this project will be documented in this file.

## 1.0.0-rc3 (2021-11-08)

### Added

- Minor optimizations when webp_support is not enabled [`c3e06fe`](https://github.com/area17/twill-image/commit/c3e06feed527f889c53f6c56d312de311000432c)

### Fixed

- Fix problem with uppercase extension [`6be42f4`](https://github.com/area17/twill-image/commit/6be42f402718d17e1333eabdccf83c192123c198)

## 1.0.0-rc2 (2021-10-05)

### Fixed

- Fix wrong variable name in placehoder template [`f17f279`](https://github.com/area17/twill-image/commit/f17f27996d0a994d0bd14610884d8a3113c1a9a7)

## 1.0.0-rc1 (2021-10-01)

### Added

- Complete refactor of the API
- Fluent image model
- Creation of an agnostic MediaSource service
- Isolate frontend view logic in a view model
- Refactor service provider
- Allow image profiles to be re-used on any image role
- Review profile structure
- Make image role with single crop easy to render without profile
- Add sizes attribute dynamic generation with ImageColumns service

[`fc8b4a0`](https://github.com/area17/twill-image/commit/fc8b4a0), [`d724edf`](https://github.com/area17/twill-image/commit/d724edf), [`7497ad2`](https://github.com/area17/twill-image/commit/7497ad2), [`eec9cd8`](https://github.com/area17/twill-image/commit/eec9cd8), [`1f12e5e`](https://github.com/area17/twill-image/commit/1f12e5e), [`29a6ca1`](https://github.com/area17/twill-image/commit/29a6ca1), [`cb31ca0`](https://github.com/area17/twill-image/commit/cb31ca0), [`9ebc315`](https://github.com/area17/twill-image/commit/9ebc315), [`77c6bcf`](https://github.com/area17/twill-image/commit/77c6bcf), [`3bf71fd`](https://github.com/area17/twill-image/commit/3bf71fd), [`6ba6ab5`](https://github.com/area17/twill-image/commit/6ba6ab5), [`f11d66c`](https://github.com/area17/twill-image/commit/f11d66c), [`44c6391`](https://github.com/area17/twill-image/commit/44c6391), [`56049eb`](https://github.com/area17/twill-image/commit/56049eb), [`85f9070`](https://github.com/area17/twill-image/commit/85f9070), [`3d1d353`](https://github.com/area17/twill-image/commit/3d1d353), [`233ff70`](https://github.com/area17/twill-image/commit/233ff70), [`844e504`](https://github.com/area17/twill-image/commit/844e504), [`d65b5ee`](https://github.com/area17/twill-image/commit/d65b5ee), [`9fb378b`](https://github.com/area17/twill-image/commit/9fb378b), [`57ea081`](https://github.com/area17/twill-image/commit/57ea081), [`203200a`](https://github.com/area17/twill-image/commit/203200a), [`95a0421`](https://github.com/area17/twill-image/commit/95a0421), [`a4c04dd`](https://github.com/area17/twill-image/commit/a4c04dd), [`4fe4a64`](https://github.com/area17/twill-image/commit/4fe4a64), [`03e3d7f`](https://github.com/area17/twill-image/commit/03e3d7f), [`658891b`](https://github.com/area17/twill-image/commit/658891b), [`4cb2433`](https://github.com/area17/twill-image/commit/4cb2433)

- Re-write of the documentation

[`92c5b79`](https://github.com/area17/twill-image/commit/92c5b79), [`8287598`](https://github.com/area17/twill-image/commit/8287598), [`78746cb`](https://github.com/area17/twill-image/commit/78746cb), [`5771dc1`](https://github.com/area17/twill-image/commit/5771dc1), [`f891985`](https://github.com/area17/twill-image/commit/f891985), [`ba45f43`](https://github.com/area17/twill-image/commit/ba45f43), [`3c0d313`](https://github.com/area17/twill-image/commit/3c0d313), [`078a05a`](https://github.com/area17/twill-image/commit/078a05a), [`1d59227`](https://github.com/area17/twill-image/commit/1d59227), [`10a27cf`](https://github.com/area17/twill-image/commit/10a27cf), [`4bbfb04`](https://github.com/area17/twill-image/commit/4bbfb04), [`751a56d`](https://github.com/area17/twill-image/commit/751a56d), [`f418e47`](https://github.com/area17/twill-image/commit/f418e47), [`e33bb7b`](https://github.com/area17/twill-image/commit/e33bb7b)

### Fixed

- Fixed wrong opacity set on `noscript` tag [`4d79ee9`](https://github.com/area17/twill-image/commit/4d79ee9d755346470df89f3c5cd3f692c189ab7f)
- Import ImageService facade [`d2882d0`](https://github.com/area17/twill-image/commit/d2882d0)
- Webp first on main source [`5a78913`](https://github.com/area17/twill-image/commit/5a78913)
- List webp srcset first [`7ae2e30`](https://github.com/area17/twill-image/commit/7ae2e30)
- update path-parse dependency [`80ffd88`](https://github.com/area17/twill-image/commit/80ffd88)

### Improved

- Add error message when role isn't found [`445b7de`](https://github.com/area17/twill-image/commit/445b7de)

### Chores

- Add and apply Prettier and PHP CS Fixer [`91f7f96`](https://github.com/area17/twill-image/commit/91f7f96)

## 0.2.8 (2021-09-15)

### Fixed

- Use media file extension to set image format and MIME type

## 0.2.7 (2021-07-15)

### Fixed

- Fix wrong srcset returned when media obj provided

## 0.2.6 (2021-06-07)

### Fixed

- Prevent escaping placeholder image attributes

## 0.2.5 (2021-05-26)

### Fixed

- Fix broken alt tag

## 0.2.3 (2021-05-10)

### Changed

- Updated PHP version dependency to `>=7.2.5`
