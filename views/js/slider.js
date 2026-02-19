(function () {
  const mobileQuery = window.matchMedia('(max-width: 767px)');

  function setVideoPoster(video) {
    if (!video) {
      return;
    }

    const targetSrc = mobileQuery.matches ? video.dataset.mobileSrc : video.dataset.desktopSrc;
    const targetPoster = mobileQuery.matches ? video.dataset.mobilePoster : video.dataset.desktopPoster;

    if (targetPoster) {
      video.setAttribute('poster', targetPoster);
    } else {
      video.removeAttribute('poster');
    }

    video.dataset.currentPoster = targetPoster || '';

    return targetSrc;
  }

  function setVideoSource(video) {
    if (!video) {
      return;
    }

    const targetSrc = setVideoPoster(video);

    if (!targetSrc || video.dataset.currentSrc === targetSrc) {
      return;
    }

    // na wszelki wypadek wyłącz pętlę, żeby 'ended' w ogóle się wywołał
    video.removeAttribute('loop');

    video.pause();
    video.src = targetSrc;
    video.load();
    video.dataset.currentSrc = targetSrc;
  }

  function clearVideoSource(video) {
    if (!video || !video.dataset.currentSrc) {
      return;
    }

    video.pause();
    video.removeAttribute('src');
    video.load();
    video.dataset.currentSrc = '';
  }

  function playVideo(video) {
    if (!video) {
      return;
    }

    video.muted = true;
    video.setAttribute('playsinline', 'true');
    const playPromise = video.play();
    if (playPromise && typeof playPromise.catch === 'function') {
      playPromise.catch(function () {});
    }
  }

  function pauseVideos(container) {
    container.querySelectorAll('video').forEach(function (video) {
      video.pause();
      // wyczyść poprzednie nasłuchiwanie końca filmu
      video.onended = null;
    });
  }

  function debounce(fn, wait) {
    let timeout;
    return function () {
      clearTimeout(timeout);
      timeout = setTimeout(fn, wait);
    };
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.besmartvideoslider').forEach(function (container) {
      const swiperElement = container.querySelector('.js-besmartvideoslider-swiper');
      if (!swiperElement || typeof Swiper === 'undefined') {
        return;
      }

      let swiper;
      let isVisible = true;

      const prepareActiveVideo = function () {
        if (!swiper || swiper.destroyed || !isVisible) {
          return;
        }

        pauseVideos(swiperElement);

        const activeVideo = swiperElement.querySelector('.swiper-slide-active video');

        swiperElement.querySelectorAll('video').forEach(function (video) {
          if (video !== activeVideo) {
            video.onended = null;
            clearVideoSource(video);
          }
        });

        if (!activeVideo) {
          return;
        }

        setVideoSource(activeVideo);

        activeVideo.onended = function () {
          if (swiper && !swiper.destroyed) {
            swiper.slideNext();
          }
        };

        playVideo(activeVideo);
      };

      const stopAllVideos = function () {
        pauseVideos(swiperElement);
        swiperElement.querySelectorAll('video').forEach(function (video) {
          clearVideoSource(video);
        });
      };

      const updatePosters = function () {
        swiperElement.querySelectorAll('video').forEach(function (video) {
          setVideoPoster(video);
        });
      };

      swiper = new Swiper(swiperElement, {
        loop: true,
        pagination: {
          el: container.querySelector('.swiper-pagination'),
          clickable: true,
        },
        navigation: {
          nextEl: container.querySelector('.swiper-button-next'),
          prevEl: container.querySelector('.swiper-button-prev'),
        },
        on: {
          init: function () {
            updatePosters();
            prepareActiveVideo();
          },
          slideChangeTransitionEnd: function () {
            prepareActiveVideo();
          }
        },
      });

      const refreshWithDebounce = debounce(function () {
        updatePosters();
        prepareActiveVideo();
      }, 100);

      if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(function (entries) {
          entries.forEach(function (entry) {
            if (entry.target !== container) {
              return;
            }

            isVisible = entry.isIntersecting;
            if (isVisible) {
              prepareActiveVideo();
            } else {
              stopAllVideos();
            }
          });
        }, {
          threshold: 0.2,
        });

        observer.observe(container);
      }

      document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
          stopAllVideos();
          return;
        }

        prepareActiveVideo();
      });

      mobileQuery.addEventListener('change', refreshWithDebounce);
      window.addEventListener('resize', refreshWithDebounce);
    });
  });
})();
